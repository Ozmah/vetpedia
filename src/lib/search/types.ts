export type SearchStatus =
	| "raw"
	| "parsed"
	| "needs_review"
	| "soft_approved"
	| "vet_approved"
	| "rejected";

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

export type SearchRequest = {
	query: string;
	limit?: number;
};

export type SearchResponse = {
	query: string;
	resultCount: number;
	hasMore: boolean;
	results: SearchResult[];
};
