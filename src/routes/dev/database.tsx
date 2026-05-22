import { createFileRoute, notFound } from "@tanstack/react-router";
import { lazy, Suspense } from "react";

const DatabasePage = import.meta.env.DEV
	? lazy(() =>
			import("../../components/dev/pages/database-page").then((module) => ({
				default: module.DatabasePage,
			})),
		)
	: null;

export const Route = createFileRoute("/dev/database")({
	beforeLoad: () => {
		if (import.meta.env.PROD) throw notFound();
	},
	loader: async () => {
		if (import.meta.env.PROD) throw notFound();

		const { getDictionaryOverviewForDev } = await import(
			"../../server/dictionary/server-functions"
		);

		return getDictionaryOverviewForDev();
	},
	component: DatabaseRoute,
});

function DatabaseRoute() {
	const overview = Route.useLoaderData();

	if (!DatabasePage) throw notFound();

	return (
		<Suspense fallback={null}>
			<DatabasePage overview={overview} />
		</Suspense>
	);
}
