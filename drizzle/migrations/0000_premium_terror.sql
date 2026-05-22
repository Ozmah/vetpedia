CREATE TABLE `review_events` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`unit_id` text NOT NULL,
	`from_status` text,
	`to_status` text NOT NULL,
	`actor` text NOT NULL,
	`note` text,
	`patch_json` text,
	`created_at` text DEFAULT CURRENT_TIMESTAMP NOT NULL,
	FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON UPDATE no action ON DELETE cascade
);
--> statement-breakpoint
CREATE INDEX `review_events_unit_idx` ON `review_events` (`unit_id`);--> statement-breakpoint
CREATE TABLE `search_documents` (
	`id` text PRIMARY KEY NOT NULL,
	`unit_id` text NOT NULL,
	`title` text NOT NULL,
	`slug` text NOT NULL,
	`field` text NOT NULL,
	`text` text NOT NULL,
	`language` text NOT NULL,
	`status` text NOT NULL,
	`source_file` text NOT NULL,
	`line_start` integer NOT NULL,
	`line_end` integer NOT NULL,
	`page_guess` integer,
	`rank_weight` real DEFAULT 1 NOT NULL,
	`needs_human_review` integer DEFAULT false NOT NULL,
	`indexed_at` text,
	`created_at` text DEFAULT CURRENT_TIMESTAMP NOT NULL,
	`updated_at` text DEFAULT CURRENT_TIMESTAMP NOT NULL,
	FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON UPDATE no action ON DELETE cascade
);
--> statement-breakpoint
CREATE INDEX `search_documents_unit_idx` ON `search_documents` (`unit_id`);--> statement-breakpoint
CREATE INDEX `search_documents_status_idx` ON `search_documents` (`status`);--> statement-breakpoint
CREATE INDEX `search_documents_field_idx` ON `search_documents` (`field`);--> statement-breakpoint
CREATE INDEX `search_documents_indexing_idx` ON `search_documents` (`language`,`status`,`rank_weight`);--> statement-breakpoint
CREATE TABLE `unit_aliases` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`unit_id` text NOT NULL,
	`alias` text NOT NULL,
	`created_at` text DEFAULT CURRENT_TIMESTAMP NOT NULL,
	FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON UPDATE no action ON DELETE cascade
);
--> statement-breakpoint
CREATE INDEX `unit_aliases_unit_idx` ON `unit_aliases` (`unit_id`);--> statement-breakpoint
CREATE UNIQUE INDEX `unit_aliases_unit_alias_unique` ON `unit_aliases` (`unit_id`,`alias`);--> statement-breakpoint
CREATE TABLE `unit_issues` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`unit_id` text NOT NULL,
	`code` text NOT NULL,
	`message` text NOT NULL,
	`severity` text DEFAULT 'warning' NOT NULL,
	`field` text,
	`created_at` text DEFAULT CURRENT_TIMESTAMP NOT NULL,
	FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON UPDATE no action ON DELETE cascade
);
--> statement-breakpoint
CREATE INDEX `unit_issues_unit_idx` ON `unit_issues` (`unit_id`);--> statement-breakpoint
CREATE INDEX `unit_issues_code_idx` ON `unit_issues` (`code`);--> statement-breakpoint
CREATE TABLE `unit_sections` (
	`id` text PRIMARY KEY NOT NULL,
	`unit_id` text NOT NULL,
	`key` text NOT NULL,
	`title` text NOT NULL,
	`text` text NOT NULL,
	`language` text NOT NULL,
	`line_start` integer NOT NULL,
	`line_end` integer NOT NULL,
	`page_guess` integer,
	`position` integer NOT NULL,
	`created_at` text DEFAULT CURRENT_TIMESTAMP NOT NULL,
	`updated_at` text DEFAULT CURRENT_TIMESTAMP NOT NULL,
	FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON UPDATE no action ON DELETE cascade
);
--> statement-breakpoint
CREATE INDEX `unit_sections_unit_idx` ON `unit_sections` (`unit_id`);--> statement-breakpoint
CREATE INDEX `unit_sections_key_idx` ON `unit_sections` (`key`);--> statement-breakpoint
CREATE TABLE `unit_species` (
	`unit_id` text NOT NULL,
	`species` text NOT NULL,
	`created_at` text DEFAULT CURRENT_TIMESTAMP NOT NULL,
	FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON UPDATE no action ON DELETE cascade
);
--> statement-breakpoint
CREATE UNIQUE INDEX `unit_species_unit_species_unique` ON `unit_species` (`unit_id`,`species`);--> statement-breakpoint
CREATE INDEX `unit_species_species_idx` ON `unit_species` (`species`);--> statement-breakpoint
CREATE TABLE `units` (
	`id` text PRIMARY KEY NOT NULL,
	`kind` text NOT NULL,
	`title` text NOT NULL,
	`title_es` text,
	`title_trusted` integer DEFAULT false NOT NULL,
	`slug` text NOT NULL,
	`source_language` text NOT NULL,
	`content_language` text NOT NULL,
	`source_file` text NOT NULL,
	`line_start` integer NOT NULL,
	`line_end` integer NOT NULL,
	`page_guess` integer,
	`raw_text` text NOT NULL,
	`normalized_text` text NOT NULL,
	`canonical_text_es` text,
	`status` text DEFAULT 'raw' NOT NULL,
	`confidence` real DEFAULT 0 NOT NULL,
	`needs_human_review` integer DEFAULT true NOT NULL,
	`cross_reference_target` text,
	`created_from_run` text,
	`created_at` text DEFAULT CURRENT_TIMESTAMP NOT NULL,
	`updated_at` text DEFAULT CURRENT_TIMESTAMP NOT NULL
);
--> statement-breakpoint
CREATE INDEX `units_status_idx` ON `units` (`status`);--> statement-breakpoint
CREATE INDEX `units_slug_idx` ON `units` (`slug`);--> statement-breakpoint
CREATE INDEX `units_source_span_idx` ON `units` (`source_file`,`line_start`,`line_end`);--> statement-breakpoint
CREATE INDEX `units_review_idx` ON `units` (`needs_human_review`,`status`);