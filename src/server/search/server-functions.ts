import { createServerFn } from "@tanstack/react-start";

export const searchVetpedia = createServerFn({ method: "GET" })
	.inputValidator((data: { query?: string; limit?: number }) => ({
		query: typeof data.query === "string" ? data.query : "",
		limit: typeof data.limit === "number" ? data.limit : undefined,
	}))
	.handler(async ({ data }) => {
		const { searchVetpediaDocuments } = await import("./search");

		return searchVetpediaDocuments(data);
	});
