import { createFileRoute } from "@tanstack/react-router";
import { SearchShell } from "../components/search-shell/search-shell";

type HomeSearch = {
	q: string;
};

export const Route = createFileRoute("/")({
	validateSearch: (search): HomeSearch => ({
		q: typeof search.q === "string" ? search.q.slice(0, 120) : "",
	}),
	component: Home,
});

function Home() {
	const search = Route.useSearch();

	return <SearchShell initialQuery={search.q} />;
}
