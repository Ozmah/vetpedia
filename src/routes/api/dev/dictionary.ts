import { createFileRoute } from "@tanstack/react-router";
import type { DictionaryUnitInput } from "../../../lib/dictionary/types";

export const Route = createFileRoute("/api/dev/dictionary")({
	server: {
		handlers: {
			GET: async ({ request }) => {
				if (import.meta.env.PROD) return notFoundResponse();

				const url = new URL(request.url);
				const unitId = url.searchParams.get("unitId");
				const { getDictionaryOverview, getDictionaryUnit } = await import(
					"../../../server/db/queries/dictionary"
				);

				if (unitId) {
					const unit = await getDictionaryUnit(unitId);

					return unit ? Response.json(unit) : notFoundResponse();
				}

				return Response.json(await getDictionaryOverview(), {
					headers: { "Cache-Control": "no-store" },
				});
			},
			POST: async ({ request }) => {
				if (import.meta.env.PROD) return notFoundResponse();

				try {
					const input = normalizeInput(
						(await request.json()) as DictionaryUnitInput,
					);
					const { saveDictionaryUnit, getDictionaryOverview } = await import(
						"../../../server/db/queries/dictionary"
					);
					const unit = await saveDictionaryUnit(input);
					const overview = await getDictionaryOverview();

					return Response.json({ count: overview.count, unit });
				} catch (error) {
					const message =
						error instanceof Error ? error.message : "No se pudo guardar.";

					return Response.json({ message }, { status: 400 });
				}
			},
		},
	},
});

function notFoundResponse() {
	return Response.json({ message: "Not found" }, { status: 404 });
}

function normalizeInput(input: DictionaryUnitInput): DictionaryUnitInput {
	return {
		aliases: Array.isArray(input.aliases) ? input.aliases : [],
		id: typeof input.id === "string" && input.id ? input.id : undefined,
		kind:
			input.kind === "cross_reference" ? "cross_reference" : "drug_monograph",
		sections: Array.isArray(input.sections) ? input.sections : [],
		species: Array.isArray(input.species) ? input.species : [],
		status: input.status || "soft_approved",
		title: input.title || "",
		titleEs: input.titleEs || "",
	};
}
