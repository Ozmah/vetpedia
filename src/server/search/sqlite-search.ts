import "@tanstack/react-start/server-only";

import { and, desc, eq, ne, or, sql } from "drizzle-orm";
import type { SQLiteColumn } from "drizzle-orm/sqlite-core";
import type {
	SearchRequest,
	SearchResponse,
	SearchResult,
	SearchSection,
} from "../../lib/search/types";
import { db, ensureDatabasePragmas } from "../db/client";
import {
	searchDocuments,
	type Unit,
	type UnitSection,
	unitAliases,
	unitSections,
	unitSpecies,
	units,
} from "../db/schema";
import type { SearchProvider } from "./provider";

const DEFAULT_LIMIT = 20;
const MAX_LIMIT = 50;
const MAX_QUERY_LENGTH = 120;
const MAX_TERMS = 6;

export const sqliteSearchProvider: SearchProvider = {
	search(request) {
		return searchSqlite(request);
	},
};

async function searchSqlite(request: SearchRequest): Promise<SearchResponse> {
	const query = normalizeQuery(request.query);
	const limit = normalizeLimit(request.limit);
	const terms = tokenize(query);
	const fetchLimit = Math.min(limit * 8, 200);
	await ensureDatabasePragmas();
	const rows = await db
		.select({ document: searchDocuments, unit: units })
		.from(searchDocuments)
		.innerJoin(units, eq(searchDocuments.unitId, units.id))
		.where(buildWhereClause(terms))
		.orderBy(desc(searchDocuments.rankWeight), searchDocuments.title)
		.limit(fetchLimit);
	const dedupedRows = dedupeRowsByUnit(rows);

	const visibleRows = dedupedRows.slice(0, limit);

	return {
		query,
		resultCount: visibleRows.length,
		hasMore: dedupedRows.length > limit || rows.length === fetchLimit,
		results: await Promise.all(
			visibleRows.map(({ document, unit }) => toSearchResult(document, unit)),
		),
	};
}

function dedupeRowsByUnit(
	rows: Array<{
		document: typeof searchDocuments.$inferSelect;
		unit: Unit;
	}>,
) {
	const seen = new Set<string>();
	const deduped: typeof rows = [];

	for (const row of rows) {
		if (seen.has(row.unit.id)) continue;

		seen.add(row.unit.id);
		deduped.push(row);
	}

	return deduped;
}

function buildWhereClause(terms: string[]) {
	const statusGuard = ne(searchDocuments.status, "rejected");

	if (terms.length === 0) {
		return statusGuard;
	}

	return and(
		statusGuard,
		...terms.map((term) => {
			const pattern = `%${escapeLikeTerm(term)}%`;

			return or(
				likeEscaped(searchDocuments.title, pattern),
				likeEscaped(searchDocuments.slug, pattern),
				likeEscaped(searchDocuments.text, pattern),
			);
		}),
	);
}

function likeEscaped(column: SQLiteColumn, pattern: string) {
	return sql`${column} LIKE ${pattern} ESCAPE '\\'`;
}

async function toSearchResult(
	document: typeof searchDocuments.$inferSelect,
	unit: Unit,
): Promise<SearchResult> {
	const [sections, aliases, species] = await Promise.all([
		db
			.select()
			.from(unitSections)
			.where(eq(unitSections.unitId, unit.id))
			.orderBy(unitSections.position),
		db
			.select({ alias: unitAliases.alias })
			.from(unitAliases)
			.where(eq(unitAliases.unitId, unit.id))
			.limit(4),
		db
			.select({ species: unitSpecies.species })
			.from(unitSpecies)
			.where(eq(unitSpecies.unitId, unit.id)),
	]);
	const section = sectionByKey(sections);
	const dosageSection = section.get("dosage");
	const noteSections = [
		section.get("indications"),
		section.get("contraindications"),
		section.get("interactions"),
	].filter((value): value is UnitSection => Boolean(value));
	const speciesTags = species.map(({ species }) => capitalize(species));
	const aliasTags = aliases.map(({ alias }) => alias).filter(Boolean);
	const badges = unique([...speciesTags, ...aliasTags]).slice(0, 6);

	return {
		id: document.id,
		title: unit.titleEs || document.title,
		category:
			unit.kind === "cross_reference" ? "REFERENCIA CRUZADA" : "FÁRMACO",
		section: sectionLabel(document.field),
		excerpt: excerpt(document.text, 320),
		source: `Fuente: ${unit.sourceFile}`,
		sourceMeta: `${unit.sourceFile} · lines ${document.lineStart}–${document.lineEnd}${document.pageGuess ? ` · page ${document.pageGuess}` : ""}`,
		unitId: unit.id,
		year: "2026",
		status: unit.status,
		tags: speciesTags.length ? speciesTags : ["Especie no detectada"],
		type: resultType(document.field),
		detail: {
			title: unit.titleEs || unit.title,
			description: descriptionFromSections(sections),
			badges,
			dosage: dosageSection
				? [
						{
							species: speciesTags.join(", ") || "Monografía",
							items: splitClinicalLines(dosageSection.text),
						},
					]
				: [],
			notes: noteSections.map(
				(item) => `${item.title}: ${excerpt(item.text, 180)}`,
			),
			warning: warningForStatus(unit.status),
		},
	};
}

function normalizeQuery(query: string) {
	return query.trim().replace(/\s+/g, " ").slice(0, MAX_QUERY_LENGTH);
}

function normalizeLimit(limit = DEFAULT_LIMIT) {
	return Math.min(Math.max(Math.trunc(limit), 1), MAX_LIMIT);
}

function tokenize(query: string) {
	return query
		.toLowerCase()
		.split(" ")
		.map((term) => term.trim())
		.filter((term) => term.length >= 2)
		.slice(0, MAX_TERMS);
}

function escapeLikeTerm(term: string) {
	return term.replace(/[\\%_]/g, "\\$&");
}

function sectionByKey(sections: UnitSection[]) {
	return new Map(sections.map((section) => [section.key, section]));
}

function sectionLabel(field: string) {
	const labels: Record<string, string> = {
		action: "Acción",
		adverseReactions: "Reacciones adversas",
		aliases: "Sinónimos y nombres comerciales",
		body: "Monografía",
		contraindications: "Contraindicaciones",
		dosage: "Dosis y administración",
		indications: "Indicaciones",
		interactions: "Interacciones",
		presentations: "Presentaciones",
		references: "Referencias",
		safetyHandling: "Seguridad y manejo",
		title: "Título",
		use: "Uso",
	};

	return labels[field] ?? "Monografía";
}

function resultType(field: string): SearchSection {
	if (field === "dosage") return "dosage";
	if (field === "interactions") return "interaction";
	return "drug";
}

function descriptionFromSections(sections: UnitSection[]) {
	const preferred = sections.find((section) =>
		["action", "use", "indications"].includes(section.key),
	);

	return preferred
		? excerpt(preferred.text, 180)
		: "Monografía farmacológica veterinaria.";
}

function splitClinicalLines(text: string) {
	const lines = text
		.split(/[.;\n]/)
		.map((line) => line.trim())
		.filter(Boolean)
		.slice(0, 5);

	return lines.length ? lines : [excerpt(text, 220)];
}

function warningForStatus(status: Unit["status"]) {
	if (status === "needs_review") {
		return "Esta unidad requiere revisión humana. Úsala solo como señal de búsqueda, no como referencia clínica validada.";
	}

	if (status === "parsed") {
		return "Esta unidad fue extraída automáticamente y todavía no tiene aprobación veterinaria.";
	}

	return undefined;
}

function excerpt(text: string, maxLength: number) {
	const normalized = text.trim().replace(/\s+/g, " ");

	if (normalized.length <= maxLength) return normalized;

	return `${normalized.slice(0, maxLength - 1).trim()}…`;
}

function capitalize(value: string) {
	return value.charAt(0).toUpperCase() + value.slice(1);
}

function unique(values: string[]) {
	return [...new Set(values.map((value) => value.trim()).filter(Boolean))];
}
