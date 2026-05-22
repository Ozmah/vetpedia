"use client";

import { useNavigate } from "@tanstack/react-router";
import { DatabaseIcon, Loader2Icon } from "lucide-react";
import { useEffect, useMemo, useState, useTransition } from "react";
import { VetpediaLogo } from "../brand/logo";
import { SearchDetail } from "./search-detail";
import { SearchInput } from "./search-input";
import { SearchResultCard } from "./search-result-card";
import type { SearchResponse } from "./types";

type SearchShellProps = {
	initialQuery: string;
};

const EMPTY_RESPONSE: SearchResponse = {
	query: "",
	resultCount: 0,
	hasMore: false,
	results: [],
};

export function SearchShell({ initialQuery }: SearchShellProps) {
	const navigate = useNavigate();
	const [isPending, startTransition] = useTransition();
	const [query, setQuery] = useState(initialQuery);
	const [searchResponse, setSearchResponse] =
		useState<SearchResponse>(EMPTY_RESPONSE);
	const [isSearching, setIsSearching] = useState(false);
	const [searchError, setSearchError] = useState<string | null>(null);
	const [selectedId, setSelectedId] = useState("");
	const results = searchResponse.results;
	const selectedResult = useMemo(
		() => results.find((result) => result.id === selectedId) ?? results[0],
		[results, selectedId],
	);
	const queryTerms = useMemo(
		() => query.split(" ").filter((term) => term.trim().length >= 2),
		[query],
	);

	useEffect(() => {
		setQuery(initialQuery);
	}, [initialQuery]);

	useEffect(() => {
		setSelectedId(results[0]?.id ?? "");
	}, [results]);

	useEffect(() => {
		const controller = new AbortController();
		const normalizedQuery = query.trim().replace(/\s+/g, " ");

		if (!normalizedQuery) {
			setSearchResponse(EMPTY_RESPONSE);
			setSearchError(null);
			setIsSearching(false);

			return () => controller.abort();
		}

		const timeoutId = window.setTimeout(async () => {
			setIsSearching(true);
			setSearchError(null);

			try {
				const params = new URLSearchParams({
					q: normalizedQuery,
					limit: "20",
				});
				const response = await fetch(`/api/search?${params.toString()}`, {
					headers: { Accept: "application/json" },
					signal: controller.signal,
				});

				if (!response.ok) {
					throw new Error("Search request failed");
				}

				setSearchResponse((await response.json()) as SearchResponse);
			} catch (error) {
				if (controller.signal.aborted) return;

				console.error(error);
				setSearchError("No se pudo ejecutar la búsqueda.");
			} finally {
				if (!controller.signal.aborted) {
					setIsSearching(false);
				}
			}
		}, 180);

		return () => {
			controller.abort();
			window.clearTimeout(timeoutId);
		};
	}, [query]);

	useEffect(() => {
		const normalizedQuery = query.trim().replace(/\s+/g, " ");

		if (normalizedQuery === initialQuery) return;

		const timeoutId = window.setTimeout(() => {
			startTransition(() => {
				navigate({
					to: "/",
					search: { q: normalizedQuery },
					replace: true,
				});
			});
		}, 250);

		return () => window.clearTimeout(timeoutId);
	}, [initialQuery, navigate, query]);

	return (
		<main className="min-h-dvh overflow-x-hidden px-4 py-5 sm:px-6 sm:py-8 lg:px-8">
			<div className="mx-auto flex w-full max-w-5xl flex-col gap-6 sm:gap-8">
				<header className="grid gap-5 sm:gap-6">
					<div className="flex items-start justify-center">
						<VetpediaLogo className="justify-center" />
					</div>
					<SearchInput onChange={setQuery} value={query} />
				</header>

				<section className="grid gap-4" aria-live="polite">
					<SearchStatus
						isLoading={isPending || isSearching}
						response={searchResponse}
					/>

					<div className="grid gap-6 lg:grid-cols-[minmax(0,0.92fr)_minmax(34rem,1.08fr)] lg:items-start">
						<ul className="grid gap-4">
							{searchError ? <ErrorState message={searchError} /> : null}
							{results.map((result) => (
								<SearchResultCard
									isSelected={result.id === selectedId}
									key={result.id}
									onSelect={() => setSelectedId(result.id)}
									queryTerms={queryTerms}
									result={result}
								/>
							))}
							{!searchError && !isSearching && results.length === 0 ? (
								<EmptyState hasQuery={Boolean(query.trim())} />
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

function ErrorState({ message }: { message: string }) {
	return (
		<li className="rounded-[1.25rem] border border-destructive/35 bg-destructive/8 p-6 text-destructive text-sm">
			<p className="font-semibold">Error de búsqueda</p>
			<p className="mt-2">{message}</p>
		</li>
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
