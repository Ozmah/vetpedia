import { sql } from "drizzle-orm";
import {
	index,
	integer,
	real,
	sqliteTable,
	text,
	uniqueIndex,
} from "drizzle-orm/sqlite-core";

export const reviewStatuses = [
	"raw",
	"parsed",
	"needs_review",
	"soft_approved",
	"vet_approved",
	"rejected",
] as const;

export const unitKinds = ["drug_monograph", "cross_reference"] as const;
export const languages = ["es", "en", "mixed"] as const;

export const sectionKeys = [
	"preamble",
	"commercialNamesCategory",
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
	"unknown",
] as const;

export const searchFields = [
	"title",
	"aliases",
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
	"body",
] as const;

export const issueSeverities = ["info", "warning", "error"] as const;
export const speciesValues = ["perro", "gato", "otro", "desconocido"] as const;

const createdAt = text("created_at").notNull().default(sql`CURRENT_TIMESTAMP`);

const updatedAt = text("updated_at").notNull().default(sql`CURRENT_TIMESTAMP`);

export const units = sqliteTable(
	"units",
	{
		id: text("id").primaryKey(),
		kind: text("kind", { enum: unitKinds }).notNull(),
		title: text("title").notNull(),
		titleEs: text("title_es"),
		titleTrusted: integer("title_trusted", { mode: "boolean" })
			.notNull()
			.default(false),
		slug: text("slug").notNull(),
		sourceLanguage: text("source_language", { enum: languages }).notNull(),
		contentLanguage: text("content_language", { enum: languages }).notNull(),
		sourceFile: text("source_file").notNull(),
		lineStart: integer("line_start").notNull(),
		lineEnd: integer("line_end").notNull(),
		pageGuess: integer("page_guess"),
		rawText: text("raw_text").notNull(),
		normalizedText: text("normalized_text").notNull(),
		canonicalTextEs: text("canonical_text_es"),
		status: text("status", { enum: reviewStatuses }).notNull().default("raw"),
		confidence: real("confidence").notNull().default(0),
		needsHumanReview: integer("needs_human_review", { mode: "boolean" })
			.notNull()
			.default(true),
		crossReferenceTarget: text("cross_reference_target"),
		createdFromRun: text("created_from_run"),
		createdAt,
		updatedAt,
	},
	(table) => [
		index("units_status_idx").on(table.status),
		index("units_slug_idx").on(table.slug),
		index("units_source_span_idx").on(
			table.sourceFile,
			table.lineStart,
			table.lineEnd,
		),
		index("units_review_idx").on(table.needsHumanReview, table.status),
	],
);

export const unitSections = sqliteTable(
	"unit_sections",
	{
		id: text("id").primaryKey(),
		unitId: text("unit_id")
			.notNull()
			.references(() => units.id, { onDelete: "cascade" }),
		key: text("key", { enum: sectionKeys }).notNull(),
		title: text("title").notNull(),
		text: text("text").notNull(),
		language: text("language", { enum: languages }).notNull(),
		lineStart: integer("line_start").notNull(),
		lineEnd: integer("line_end").notNull(),
		pageGuess: integer("page_guess"),
		position: integer("position").notNull(),
		createdAt,
		updatedAt,
	},
	(table) => [
		index("unit_sections_unit_idx").on(table.unitId),
		index("unit_sections_key_idx").on(table.key),
	],
);

export const unitAliases = sqliteTable(
	"unit_aliases",
	{
		id: integer("id").primaryKey({ autoIncrement: true }),
		unitId: text("unit_id")
			.notNull()
			.references(() => units.id, { onDelete: "cascade" }),
		alias: text("alias").notNull(),
		createdAt,
	},
	(table) => [
		index("unit_aliases_unit_idx").on(table.unitId),
		uniqueIndex("unit_aliases_unit_alias_unique").on(table.unitId, table.alias),
	],
);

export const unitSpecies = sqliteTable(
	"unit_species",
	{
		unitId: text("unit_id")
			.notNull()
			.references(() => units.id, { onDelete: "cascade" }),
		species: text("species", { enum: speciesValues }).notNull(),
		createdAt,
	},
	(table) => [
		uniqueIndex("unit_species_unit_species_unique").on(
			table.unitId,
			table.species,
		),
		index("unit_species_species_idx").on(table.species),
	],
);

export const unitIssues = sqliteTable(
	"unit_issues",
	{
		id: integer("id").primaryKey({ autoIncrement: true }),
		unitId: text("unit_id")
			.notNull()
			.references(() => units.id, { onDelete: "cascade" }),
		code: text("code").notNull(),
		message: text("message").notNull(),
		severity: text("severity", { enum: issueSeverities })
			.notNull()
			.default("warning"),
		field: text("field"),
		createdAt,
	},
	(table) => [
		index("unit_issues_unit_idx").on(table.unitId),
		index("unit_issues_code_idx").on(table.code),
	],
);

export const searchDocuments = sqliteTable(
	"search_documents",
	{
		id: text("id").primaryKey(),
		unitId: text("unit_id")
			.notNull()
			.references(() => units.id, { onDelete: "cascade" }),
		title: text("title").notNull(),
		slug: text("slug").notNull(),
		field: text("field", { enum: searchFields }).notNull(),
		text: text("text").notNull(),
		language: text("language", { enum: languages }).notNull(),
		status: text("status", { enum: reviewStatuses }).notNull(),
		sourceFile: text("source_file").notNull(),
		lineStart: integer("line_start").notNull(),
		lineEnd: integer("line_end").notNull(),
		pageGuess: integer("page_guess"),
		rankWeight: real("rank_weight").notNull().default(1),
		needsHumanReview: integer("needs_human_review", { mode: "boolean" })
			.notNull()
			.default(false),
		indexedAt: text("indexed_at"),
		createdAt,
		updatedAt,
	},
	(table) => [
		index("search_documents_unit_idx").on(table.unitId),
		index("search_documents_status_idx").on(table.status),
		index("search_documents_field_idx").on(table.field),
		index("search_documents_indexing_idx").on(
			table.language,
			table.status,
			table.rankWeight,
		),
	],
);

export const reviewEvents = sqliteTable(
	"review_events",
	{
		id: integer("id").primaryKey({ autoIncrement: true }),
		unitId: text("unit_id")
			.notNull()
			.references(() => units.id, { onDelete: "cascade" }),
		fromStatus: text("from_status", { enum: reviewStatuses }),
		toStatus: text("to_status", { enum: reviewStatuses }).notNull(),
		actor: text("actor").notNull(),
		note: text("note"),
		patchJson: text("patch_json"),
		createdAt,
	},
	(table) => [index("review_events_unit_idx").on(table.unitId)],
);

export type Unit = typeof units.$inferSelect;
export type UnitSection = typeof unitSections.$inferSelect;
export type SearchDocument = typeof searchDocuments.$inferSelect;
