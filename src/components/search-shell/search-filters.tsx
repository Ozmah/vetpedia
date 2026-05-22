import { ChevronDownIcon } from "lucide-react";
import { cn } from "../../lib/cn";

const FILTERS = [
	{ label: "Todos", active: true },
	{ label: "Fármaco" },
	{ label: "Clase terapéutica" },
	{ label: "Interacción" },
	{ label: "Dosis y administración" },
	{ label: "Monografía" },
];

const STATUS_FILTERS = [
	{ label: "Validados", tone: "success" },
	{ label: "Requieren revisión", tone: "warning" },
	{ label: "Advertencias", tone: "danger" },
];

export function SearchFilters() {
	return (
		<div className="flex w-full flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
			<div className="scrollbar-none -mx-2 flex gap-3 overflow-x-auto px-2 pb-1">
				{FILTERS.map((filter) => (
					<button
						className={cn(
							"flex min-h-11 shrink-0 items-center gap-2 rounded-[var(--radius)] border px-5 font-medium font-mono text-[0.72rem] transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-ring focus-visible:outline-offset-2",
							filter.active
								? "border-primary bg-primary text-primary-foreground"
								: "border-border bg-card/60 text-foreground hover:border-primary/60 hover:bg-card",
						)}
						key={filter.label}
						type="button"
					>
						{filter.label}
						{filter.active ? (
							<ChevronDownIcon aria-hidden="true" className="size-4" />
						) : null}
					</button>
				))}
			</div>

			<div className="scrollbar-none -mx-2 flex gap-3 overflow-x-auto px-2 pb-1 lg:mx-0 lg:px-0">
				{STATUS_FILTERS.map((filter) => (
					<button
						className={cn(
							"min-h-11 shrink-0 rounded-[var(--radius)] border bg-card/45 px-5 font-medium font-mono text-[0.72rem] transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-ring focus-visible:outline-offset-2",
							filter.tone === "success" &&
								"border-success/45 text-success hover:bg-success/10",
							filter.tone === "warning" &&
								"border-warning/45 text-warning hover:bg-warning/10",
							filter.tone === "danger" &&
								"border-destructive/45 text-destructive hover:bg-destructive/10",
						)}
						key={filter.label}
						type="button"
					>
						{filter.label}
					</button>
				))}
				<button
					className="min-h-11 shrink-0 rounded-[var(--radius)] px-4 font-medium font-mono text-[0.72rem] text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-ring focus-visible:outline-offset-2"
					type="button"
				>
					Borrar filtros
				</button>
			</div>
		</div>
	);
}
