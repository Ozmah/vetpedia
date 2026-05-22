"use client";

import { useNavigate } from "@tanstack/react-router";
import { DatabaseIcon, Loader2Icon } from "lucide-react";
import { useMemo, useState, useTransition } from "react";
import { VetpediaLogo } from "../brand/logo";
import { SearchDetail } from "./search-detail";
import { SearchInput } from "./search-input";
import { SearchResultCard } from "./search-result-card";
import type { SearchResponse } from "./types";

type SearchShellProps = {
	initialQuery: string;
	searchResponse: SearchResponse;
};

export function SearchShell({
	initialQuery,
	searchResponse,
}: SearchShellProps) {
	const navigate = useNavigate();
	const [isPending, startTransition] = useTransition();
	const [selectedId, setSelectedId] = useState("");
	const results = searchResponse.results;
	const effectiveSelectedId = selectedId || results[0]?.id || "";
	const selectedResult = useMemo(
		() =>
			results.find((result) => result.id === effectiveSelectedId) ?? results[0],
		[results, effectiveSelectedId],
	);
	const queryTerms = useMemo(
		() => initialQuery.split(" ").filter((term) => term.trim().length >= 2),
		[initialQuery],
	);

	function submitSearch(value: string) {
		const normalizedQuery = value.trim().replace(/\s+/g, " ");

		startTransition(() => {
			navigate({
				to: "/",
				search: normalizedQuery ? { q: normalizedQuery } : {},
			});
		});
	}

	return (
		<main className="min-h-dvh overflow-x-hidden px-4 py-5 sm:px-6 sm:py-8 lg:px-8">
			<div className="mx-auto flex w-full max-w-5xl flex-col gap-6 sm:gap-8">
				<header className="grid gap-5 sm:gap-6">
					<div className="flex items-start justify-center">
						<VetpediaLogo className="justify-center" />
					</div>
					<SearchInput
						defaultValue={initialQuery}
						onClear={() => submitSearch("")}
						onSearch={submitSearch}
					/>
				</header>

				<section className="grid gap-4" aria-live="polite">
					<SearchStatus isLoading={isPending} response={searchResponse} />

					<div className="grid gap-6 lg:grid-cols-[minmax(0,0.92fr)_minmax(34rem,1.08fr)] lg:items-start">
						<ul className="grid gap-4">
							{results.map((result) => (
								<SearchResultCard
									isSelected={result.id === effectiveSelectedId}
									key={result.id}
									onSelect={() => setSelectedId(result.id)}
									queryTerms={queryTerms}
									result={result}
								/>
							))}
							{!isPending && results.length === 0 ? (
								<EmptyState hasQuery={Boolean(initialQuery.trim())} />
							) : null}
						</ul>

						{selectedResult ? (
							<div className="sticky top-6 hidden lg:block">
								<SearchDetail result={selectedResult} />
							</div>
						) : null}
					</div>
				</section>
			</div>
		</main>
	);
}

function resultLabel(response: SearchResponse) {
	const suffix = response.hasMore ? "+" : "";

	if (response.resultCount === 1) return "1 resultado encontrado";

	return `${response.resultCount}${suffix} resultados encontrados`;
}

function SearchStatus({
	isLoading,
	response,
}: {
	isLoading: boolean;
	response: SearchResponse;
}) {
	if (isLoading) {
		return (
			<p className="mx-auto inline-flex min-h-8 items-center gap-2 text-muted-foreground text-sm">
				<Loader2Icon aria-hidden="true" className="size-4 animate-spin" />
				Buscando…
			</p>
		);
	}

	if (response.resultCount === 0) return null;

	return (
		<p className="font-mono text-[0.72rem] text-muted-foreground uppercase tracking-[0.12em]">
			{resultLabel(response)}
		</p>
	);
}

function EmptyState({ hasQuery }: { hasQuery: boolean }) {
	return (
		<li className="mx-auto w-full max-w-2xl rounded-[1.25rem] border border-border bg-card/58 p-5 text-sm shadow-[0_12px_40px_var(--shadow-soft)] sm:p-6">
			<div className="flex gap-4">
				<div className="grid size-11 shrink-0 place-items-center rounded-[0.85rem] border border-border bg-background/55 text-primary">
					<DatabaseIcon aria-hidden="true" className="size-5" />
				</div>
				<div>
					<p className="font-semibold text-foreground">
						{hasQuery ? "Sin resultados" : "Base de datos vacía"}
					</p>
					<p className="mt-2 text-muted-foreground leading-6">
						{hasQuery
							? "No hay monografías cargadas que coincidan con tu búsqueda."
							: "Todavía no hay monografías cargadas. La búsqueda quedará lista para cuando agreguemos datos curados manualmente."}
					</p>
				</div>
			</div>
		</li>
	);
}
