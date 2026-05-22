import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/api/search")({
	server: {
		handlers: {
			GET: async ({ request }) => {
				const url = new URL(request.url);
				const query = url.searchParams.get("q") ?? "";
				const limit = Number(url.searchParams.get("limit") ?? 20);

				try {
					const { searchVetpediaDocuments } = await import(
						"../../server/search/search"
					);
					const response = await searchVetpediaDocuments({
						query,
						limit: Number.isFinite(limit) ? limit : 20,
					});

					return Response.json(response, {
						headers: {
							"Cache-Control": "no-store",
						},
					});
				} catch (error) {
					console.error("Vetpedia search failed", error);

					return Response.json(
						{ message: "Search failed" },
						{
							status: 500,
							headers: {
								"Cache-Control": "no-store",
							},
						},
					);
				}
			},
		},
	},
});
