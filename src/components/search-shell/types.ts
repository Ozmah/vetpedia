import type { ComponentType } from "react";

export type SearchStatus =
	| "vet_approved"
	| "soft_approved"
	| "parsed"
	| "needs_review";

export type SearchSection = "drug" | "class" | "interaction" | "dosage";

export type SearchResult = {
	id: string;
	title: string;
	category: string;
	section: string;
	excerpt: string;
	source: string;
	sourceMeta: string;
	unitId: string;
	year: string;
	status: SearchStatus;
	tags: string[];
	icon: ComponentType<{ className?: string }>;
	type: SearchSection;
	detail: {
		title: string;
		description: string;
		badges: string[];
		dosage: Array<{
			species: string;
			items: string[];
		}>;
		notes: string[];
		warning?: string;
	};
};
