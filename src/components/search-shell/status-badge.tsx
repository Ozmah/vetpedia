import {
	AlertTriangleIcon,
	CheckCircle2Icon,
	CircleDotIcon,
} from "lucide-react";
import { cn } from "../../lib/cn";
import type { SearchStatus } from "./types";

const STATUS_LABEL: Record<SearchStatus, string> = {
	vet_approved: "Validado por veterinario",
	soft_approved: "Revisión humana",
	parsed: "Parsed",
	needs_review: "Requiere revisión",
};

export function StatusBadge({ status }: { status: SearchStatus }) {
	const Icon =
		status === "needs_review"
			? AlertTriangleIcon
			: status === "parsed"
				? CircleDotIcon
				: CheckCircle2Icon;

	return (
		<span
			className={cn(
				"inline-flex min-h-8 items-center gap-2 rounded-[0.65rem] border px-3 font-medium font-mono text-[0.68rem]",
				(status === "vet_approved" || status === "soft_approved") &&
					"border-success/35 bg-success/8 text-success",
				status === "parsed" &&
					"border-accent/40 bg-accent/10 text-muted-foreground",
				status === "needs_review" &&
					"border-warning/35 bg-warning/8 text-warning",
			)}
		>
			<Icon aria-hidden="true" className="size-3.5" />
			{STATUS_LABEL[status]}
		</span>
	);
}
