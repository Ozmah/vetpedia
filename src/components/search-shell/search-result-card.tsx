import {
	CheckIcon,
	ChevronRightIcon,
	FileTextIcon,
	FlaskConicalIcon,
	Repeat2Icon,
	SyringeIcon,
} from "lucide-react";
import { cn } from "../../lib/cn";
import { SearchDetail } from "./search-detail";
import { StatusBadge } from "./status-badge";
import type { SearchResult, SearchSection } from "./types";

type SearchResultCardProps = {
	result: SearchResult;
	isSelected: boolean;
	onSelect: () => void;
	queryTerms: string[];
};

export function SearchResultCard({
	result,
	isSelected,
	onSelect,
	queryTerms,
}: SearchResultCardProps) {
	const Icon = iconByType[result.type];

	return (
		<li>
			<button
				aria-expanded={isSelected}
				className={cn(
					"group relative grid w-full grid-cols-[3.25rem_1fr_auto] gap-4 overflow-hidden rounded-[1.25rem] border bg-card/52 p-4 text-left shadow-[0_10px_45px_var(--shadow-soft)] transition-[border-color,background-color,box-shadow] duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-ring focus-visible:outline-offset-2 sm:p-5",
					isSelected
						? "border-primary bg-[linear-gradient(135deg,var(--surface-raised),oklch(0.975_0.009_70/0.68))] shadow-[0_0_0_1px_var(--primary),0_16px_60px_var(--shadow-soft)]"
						: "border-border hover:border-primary/50 hover:bg-card/78",
				)}
				onClick={onSelect}
				type="button"
			>
				<div className="grid size-12 place-items-center rounded-[var(--radius)] border border-border bg-background/55 text-primary shadow-[inset_0_1px_0_oklch(1_0_0/0.35)]">
					<Icon aria-hidden="true" className="size-6" />
				</div>

				<div className="min-w-0">
					<p className="font-bold text-primary text-xs tracking-[0.12em]">
						{result.category}
					</p>
					<h3 className="mt-1 font-heading font-semibold text-2xl text-foreground tracking-[-0.035em]">
						{result.title}
					</h3>
					<p className="mt-1 font-medium text-primary text-sm">
						Sección: {result.section}
					</p>
					<p className="mt-2 text-sm leading-6">
						{highlightTerms(result.excerpt, queryTerms)}
					</p>
					<div className="mt-3 flex flex-wrap gap-2">
						<StatusBadge status={result.status} />
						{result.tags.map((tag) => (
							<span
								className="inline-flex min-h-8 items-center rounded-[0.65rem] border border-border bg-background/40 px-3 font-medium font-mono text-[0.68rem]"
								key={tag}
							>
								{tag}
							</span>
						))}
					</div>
				</div>

				{isSelected ? (
					<span className="absolute top-4 right-4 hidden size-7 place-items-center rounded-[0.65rem] border border-primary/35 bg-primary text-primary-foreground shadow-[0_8px_24px_var(--shadow-soft)] sm:grid">
						<CheckIcon aria-hidden="true" className="size-4" />
					</span>
				) : null}

				<ChevronRightIcon
					aria-hidden="true"
					className={cn(
						"mt-16 size-5 text-muted-foreground transition-transform duration-200 group-hover:text-primary",
						isSelected && "rotate-90 lg:rotate-0",
					)}
				/>
			</button>

			{isSelected ? (
				<div className="mt-4 lg:hidden">
					<SearchDetail result={result} />
				</div>
			) : null}
		</li>
	);
}

const iconByType: Record<SearchSection, typeof FileTextIcon> = {
	class: FlaskConicalIcon,
	dosage: SyringeIcon,
	drug: FileTextIcon,
	interaction: Repeat2Icon,
};

function highlightTerms(text: string, terms: string[]) {
	const safeTerms = terms.map(escapeRegExp).filter(Boolean);

	if (safeTerms.length === 0) return text;

	const pattern = new RegExp(`(${safeTerms.join("|")})`, "gi");
	const parts = text.split(pattern);
	const seen = new Map<string, number>();

	return parts.map((part) => {
		const isMatch = terms.some(
			(term) => term.toLowerCase() === part.toLowerCase(),
		);
		const count = seen.get(part) ?? 0;
		seen.set(part, count + 1);
		const key = `${part}-${count}`;

		return isMatch ? (
			<mark className="rounded-sm bg-highlight px-1 text-foreground" key={key}>
				{part}
			</mark>
		) : (
			<span key={key}>{part}</span>
		);
	});
}

function escapeRegExp(value: string) {
	return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}
