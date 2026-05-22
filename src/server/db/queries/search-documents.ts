import { and, eq, ne } from "drizzle-orm";
import { db, ensureDatabasePragmas } from "../client";
import { searchDocuments } from "../schema";

export async function getSearchDocumentsForIndexing(limit = 1000) {
	const normalizedLimit = Math.min(Math.max(limit, 1), 5000);
	await ensureDatabasePragmas();

	return db
		.select()
		.from(searchDocuments)
		.where(
			and(
				eq(searchDocuments.language, "es"),
				ne(searchDocuments.status, "rejected"),
			),
		)
		.limit(normalizedLimit);
}

export async function markSearchDocumentIndexed(
	searchDocumentId: string,
	indexedAt = new Date(),
) {
	await ensureDatabasePragmas();

	return db
		.update(searchDocuments)
		.set({
			indexedAt: indexedAt.toISOString(),
			updatedAt: new Date().toISOString(),
		})
		.where(eq(searchDocuments.id, searchDocumentId));
}
