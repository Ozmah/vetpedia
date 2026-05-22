import { desc, eq, like, or } from "drizzle-orm";
import { db, ensureDatabasePragmas } from "../client";
import {
	unitAliases,
	unitIssues,
	unitSections,
	unitSpecies,
	units,
} from "../schema";

export async function getUnitById(unitId: string) {
	await ensureDatabasePragmas();
	const [unit] = await db.select().from(units).where(eq(units.id, unitId));

	if (!unit) return null;

	const [sections, aliases, species, issues] = await Promise.all([
		db
			.select()
			.from(unitSections)
			.where(eq(unitSections.unitId, unitId))
			.orderBy(unitSections.position),
		db.select().from(unitAliases).where(eq(unitAliases.unitId, unitId)),
		db.select().from(unitSpecies).where(eq(unitSpecies.unitId, unitId)),
		db.select().from(unitIssues).where(eq(unitIssues.unitId, unitId)),
	]);

	return {
		unit,
		sections,
		aliases,
		species,
		issues,
	};
}

export async function findUnitsForAdmin(query: string, limit = 50) {
	const normalizedLimit = Math.min(Math.max(limit, 1), 100);
	const search = `%${query.trim()}%`;
	await ensureDatabasePragmas();

	if (!query.trim()) {
		return db
			.select()
			.from(units)
			.orderBy(desc(units.updatedAt))
			.limit(normalizedLimit);
	}

	return db
		.select()
		.from(units)
		.where(
			or(
				like(units.title, search),
				like(units.titleEs, search),
				like(units.slug, search),
			),
		)
		.orderBy(desc(units.updatedAt))
		.limit(normalizedLimit);
}

export async function getReviewQueue(limit = 100) {
	const normalizedLimit = Math.min(Math.max(limit, 1), 250);
	await ensureDatabasePragmas();

	return db
		.select()
		.from(units)
		.where(
			or(eq(units.needsHumanReview, true), eq(units.status, "needs_review")),
		)
		.orderBy(desc(units.confidence), units.title)
		.limit(normalizedLimit);
}
