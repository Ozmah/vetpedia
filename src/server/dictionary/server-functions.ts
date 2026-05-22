import { createServerFn } from "@tanstack/react-start";

export const getDictionaryOverviewForDev = createServerFn({
	method: "GET",
}).handler(async () => {
	const { getDictionaryOverview } = await import("../db/queries/dictionary");

	return getDictionaryOverview();
});

export const getDictionaryUnitForDev = createServerFn({ method: "GET" })
	.inputValidator((data: { unitId?: string }) => ({
		unitId: typeof data.unitId === "string" ? data.unitId : "",
	}))
	.handler(async ({ data }) => {
		if (!data.unitId) return null;

		const { getDictionaryUnit } = await import("../db/queries/dictionary");

		return getDictionaryUnit(data.unitId);
	});
