import { eq } from "drizzle-orm";
import { db, ensureDatabasePragmas } from "../client";
import { reviewEvents, type reviewStatuses, type Unit, units } from "../schema";

type ReviewStatus = (typeof reviewStatuses)[number];

type UpdateUnitStatusInput = {
	unitId: string;
	toStatus: ReviewStatus;
	actor: string;
	note?: string;
	patchJson?: unknown;
};

export async function updateUnitStatus(input: UpdateUnitStatusInput) {
	await ensureDatabasePragmas();
	const [current] = await db
		.select()
		.from(units)
		.where(eq(units.id, input.unitId));

	if (!current) {
		throw new Error(`Unit not found: ${input.unitId}`);
	}

	const now = new Date().toISOString();
	const needsHumanReview =
		input.toStatus === "needs_review" || current.needsHumanReview;

	return db.transaction(async (tx) => {
		await tx
			.update(units)
			.set({
				status: input.toStatus,
				needsHumanReview,
				updatedAt: now,
			})
			.where(eq(units.id, input.unitId));

		await tx.insert(reviewEvents).values({
			unitId: input.unitId,
			fromStatus: current.status,
			toStatus: input.toStatus,
			actor: input.actor,
			note: input.note,
			patchJson: input.patchJson ? JSON.stringify(input.patchJson) : undefined,
		});

		const [updated] = await tx
			.select()
			.from(units)
			.where(eq(units.id, input.unitId));

		return updated as Unit;
	});
}
