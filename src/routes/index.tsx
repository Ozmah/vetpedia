import { createFileRoute } from "@tanstack/react-router";
import { SearchShell } from "../components/search-shell/search-shell";

export const Route = createFileRoute("/")({ component: Home });

function Home() {
	return <SearchShell />;
}
