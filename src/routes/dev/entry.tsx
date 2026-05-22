import { createFileRoute, notFound } from "@tanstack/react-router";
import { lazy, Suspense } from "react";

type EntrySearch = {
	unitId?: string;
};

const EntryPage = import.meta.env.DEV
	? lazy(() =>
			import("../../components/dev/pages/entry-page").then((module) => ({
				default: module.EntryPage,
			})),
		)
	: null;

export const Route = createFileRoute("/dev/entry")({
	beforeLoad: () => {
		if (import.meta.env.PROD) throw notFound();
	},
	validateSearch: (search): EntrySearch => ({
		unitId: typeof search.unitId === "string" ? search.unitId : undefined,
	}),
	loaderDeps: ({ search }) => ({ unitId: search.unitId }),
	loader: async ({ deps }) => {
		if (import.meta.env.PROD) throw notFound();

		const { getDictionaryOverviewForDev, getDictionaryUnitForDev } =
			await import("../../server/dictionary/server-functions");
		const [overview, unit] = await Promise.all([
			getDictionaryOverviewForDev(),
			deps.unitId
				? getDictionaryUnitForDev({ data: { unitId: deps.unitId } })
				: Promise.resolve(null),
		]);

		return { count: overview.count, unit };
	},
	component: EntryRoute,
});

function EntryRoute() {
	const { unitId } = Route.useSearch();
	const data = Route.useLoaderData();

	if (!EntryPage) throw notFound();

	return (
		<Suspense fallback={null}>
			<EntryPage
				initialCount={data.count}
				initialUnit={data.unit}
				key={unitId ?? "new"}
				unitId={unitId}
			/>
		</Suspense>
	);
}
