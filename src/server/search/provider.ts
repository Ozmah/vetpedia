import type { SearchRequest, SearchResponse } from "../../lib/search/types";

export type SearchProvider = {
	search(request: SearchRequest): Promise<SearchResponse>;
};
