import { createFileRoute } from "@tanstack/react-router";
import { SearchShell } from "../components/search-shell/search-shell";
import { searchVetpedia } from "../server/search/server-functions";

type HomeSearch = {
	q?: string;
};

export const Route = createFileRoute("/")({
	validateSearch: (search): HomeSearch => {
		const query =
			typeof search.q === "string" ? search.q.trim().slice(0, 120) : "";

		return query ? { q: query } : {};
	},
	loaderDeps: ({ search }) => ({ q: search.q ?? "" }),
	loader: ({ deps }) => searchVetpedia({ data: { query: deps.q, limit: 20 } }),
	component: Home,
});

function Home() {
	const search = Route.useSearch();
	const searchResponse = Route.useLoaderData();

	return (
		<SearchShell
			initialQuery={search.q ?? ""}
			searchResponse={searchResponse}
		/>
	);
}
