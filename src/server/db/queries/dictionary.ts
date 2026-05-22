import { count, desc, eq } from "drizzle-orm";
import type {
	DictionaryOverview,
	DictionarySectionInput,
	DictionaryUnitDetail,
	DictionaryUnitInput,
	DictionaryUnitSummary,
} from "../../../lib/dictionary/types";
import { db, ensureDatabasePragmas } from "../client";
import {
	type SearchDocument,
	searchDocuments,
	unitAliases,
	unitSections,
	unitSpecies,
	units,
} from "../schema";

const MANUAL_SOURCE = "manual-entry";
const SEARCHABLE_SECTION_KEYS = new Set([
	"presentations",
	"action",
	"use",
	"indications",
	"safetyHandling",
	"contraindications",
	"adverseReactions",
	"interactions",
	"dosage",
	"references",
]);

export async function getDictionaryOverview(): Promise<DictionaryOverview> {
	await ensureDatabasePragmas();
	const [{ value }] = await db.select({ value: count() }).from(units);
	const unitRows = await db
		.select()
		.from(units)
		.orderBy(desc(units.updatedAt), units.title)
		.limit(250);

	return {
		count: value,
		units: await Promise.all(unitRows.map(toUnitSummary)),
	};
}

export async function getDictionaryUnit(
	unitId: string,
): Promise<DictionaryUnitDetail | null> {
	await ensureDatabasePragmas();
	const [unit] = await db.select().from(units).where(eq(units.id, unitId));

	if (!unit) return null;

	const summary = await toUnitSummary(unit);
	const sections = await db
		.select()
		.from(unitSections)
		.where(eq(unitSections.unitId, unitId))
		.orderBy(unitSections.position);

	return {
		...summary,
		sections: sections.map((section) => ({
			key: section.key,
			title: section.title,
			text: section.text,
		})),
	};
}

export async function saveDictionaryUnit(input: DictionaryUnitInput) {
	await ensureDatabasePragmas();
	const now = new Date().toISOString();
	const title = input.title.trim();

	if (!title) {
		throw new Error("El título es obligatorio.");
	}

	const unitId =
		input.id || `manual:${slugify(title)}:${crypto.randomUUID().slice(0, 8)}`;
	const slug = slugify(input.titleEs || title);
	const aliases = unique(input.aliases);
	const species = unique(input.species).length
		? unique(input.species)
		: ["desconocido" as const];
	const sections = cleanSections(input.sections);
	const normalizedText = buildNormalizedText(title, aliases, sections);
	const needsHumanReview =
		input.status === "raw" ||
		input.status === "needs_review" ||
		input.status === "parsed";

	await db.transaction(async (tx) => {
		await tx
			.insert(units)
			.values({
				id: unitId,
				kind: input.kind,
				title,
				titleEs: emptyToNull(input.titleEs),
				titleTrusted: true,
				slug,
				sourceLanguage: "es",
				contentLanguage: "es",
				sourceFile: MANUAL_SOURCE,
				lineStart: 0,
				lineEnd: 0,
				pageGuess: null,
				rawText: normalizedText,
				normalizedText,
				canonicalTextEs: normalizedText,
				status: input.status,
				confidence: 1,
				needsHumanReview,
				crossReferenceTarget: null,
				createdFromRun: "manual-entry",
				updatedAt: now,
			})
			.onConflictDoUpdate({
				target: units.id,
				set: {
					kind: input.kind,
					title,
					titleEs: emptyToNull(input.titleEs),
					slug,
					rawText: normalizedText,
					normalizedText,
					canonicalTextEs: normalizedText,
					status: input.status,
					needsHumanReview,
					updatedAt: now,
				},
			});

		await tx.delete(searchDocuments).where(eq(searchDocuments.unitId, unitId));
		await tx.delete(unitSections).where(eq(unitSections.unitId, unitId));
		await tx.delete(unitAliases).where(eq(unitAliases.unitId, unitId));
		await tx.delete(unitSpecies).where(eq(unitSpecies.unitId, unitId));

		if (sections.length) {
			await tx.insert(unitSections).values(
				sections.map((section, index) => ({
					id: `${unitId}:section:${section.key}`,
					unitId,
					key: section.key,
					title: section.title,
					text: section.text,
					language: "es" as const,
					lineStart: 0,
					lineEnd: 0,
					pageGuess: null,
					position: index,
				})),
			);
		}

		if (aliases.length) {
			await tx
				.insert(unitAliases)
				.values(aliases.map((alias) => ({ unitId, alias })));
		}

		await tx
			.insert(unitSpecies)
			.values(species.map((item) => ({ unitId, species: item })));

		const searchRows = buildSearchDocuments({
			aliases,
			needsHumanReview,
			normalizedText,
			sections,
			slug,
			status: input.status,
			title,
			unitId,
		});

		if (searchRows.length) {
			await tx.insert(searchDocuments).values(searchRows);
		}
	});

	return getDictionaryUnit(unitId);
}

async function toUnitSummary(
	unit: typeof units.$inferSelect,
): Promise<DictionaryUnitSummary> {
	const [aliases, species, [{ value: sectionCount }]] = await Promise.all([
		db
			.select({ alias: unitAliases.alias })
			.from(unitAliases)
			.where(eq(unitAliases.unitId, unit.id)),
		db
			.select({ species: unitSpecies.species })
			.from(unitSpecies)
			.where(eq(unitSpecies.unitId, unit.id)),
		db
			.select({ value: count() })
			.from(unitSections)
			.where(eq(unitSections.unitId, unit.id)),
	]);

	return {
		id: unit.id,
		title: unit.title,
		titleEs: unit.titleEs,
		kind: unit.kind,
		status: unit.status,
		aliases: aliases.map((item) => item.alias),
		species: species.map((item) => item.species),
		sectionCount,
		updatedAt: unit.updatedAt,
	};
}

function buildSearchDocuments(input: {
	aliases: string[];
	needsHumanReview: boolean;
	normalizedText: string;
	sections: DictionarySectionInput[];
	slug: string;
	status: DictionaryUnitInput["status"];
	title: string;
	unitId: string;
}): Array<typeof searchDocuments.$inferInsert> {
	const base = {
		language: "es" as const,
		lineEnd: 0,
		lineStart: 0,
		needsHumanReview: input.needsHumanReview,
		pageGuess: null,
		slug: input.slug,
		sourceFile: MANUAL_SOURCE,
		status: input.status,
		title: input.title,
		unitId: input.unitId,
	} satisfies Partial<SearchDocument>;

	const rows: Array<typeof searchDocuments.$inferInsert> = [
		{
			...base,
			id: `${input.unitId}:title`,
			field: "title",
			rankWeight: 3,
			text: input.title,
		},
		{
			...base,
			id: `${input.unitId}:body`,
			field: "body",
			rankWeight: 0.8,
			text: input.normalizedText,
		},
	];

	if (input.aliases.length) {
		rows.push({
			...base,
			id: `${input.unitId}:aliases`,
			field: "aliases",
			rankWeight: 2,
			text: input.aliases.join(", "),
		});
	}

	for (const section of input.sections) {
		if (!SEARCHABLE_SECTION_KEYS.has(section.key)) continue;

		rows.push({
			...base,
			id: `${input.unitId}:${section.key}`,
			field: section.key as (typeof rows)[number]["field"],
			rankWeight: section.key === "dosage" ? 2 : 1.4,
			text: section.text,
		});
	}

	return rows;
}

function buildNormalizedText(
	title: string,
	aliases: string[],
	sections: DictionarySectionInput[],
) {
	return [
		`Título: ${title}`,
		aliases.length ? `Alias: ${aliases.join(", ")}` : "",
		...sections.map((section) => `${section.title}: ${section.text}`),
	]
		.filter(Boolean)
		.join("\n\n");
}

function cleanSections(sections: DictionarySectionInput[]) {
	const seen = new Set<string>();

	return sections
		.map((section) => ({
			key: section.key,
			title: section.title.trim(),
			text: section.text.trim(),
		}))
		.filter((section) => {
			if (!section.text || seen.has(section.key)) return false;

			seen.add(section.key);
			return true;
		});
}

function emptyToNull(value?: string) {
	const normalized = value?.trim();

	return normalized ? normalized : null;
}

function slugify(value: string) {
	return (
		value
			.normalize("NFD")
			.replace(/[\u0300-\u036f]/g, "")
			.toLowerCase()
			.replace(/[^a-z0-9]+/g, "-")
			.replace(/^-+|-+$/g, "") || "sin-titulo"
	);
}

function unique<T extends string>(values: T[]) {
	return [
		...new Set(values.map((value) => value.trim()).filter(Boolean) as T[]),
	];
}
