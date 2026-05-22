import "@tanstack/react-start/server-only";

import type { SearchRequest } from "../../lib/search/types";
import { sqliteSearchProvider } from "./sqlite-search";

const searchProvider = sqliteSearchProvider;

export async function searchVetpediaDocuments(request: SearchRequest) {
	return searchProvider.search(request);
}
