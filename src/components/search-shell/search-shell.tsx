"use client";

import { BookmarkIcon, HelpCircleIcon } from "lucide-react";
import { useState } from "react";
import { VetpediaLogo } from "../brand/logo";
import { MOCK_RESULTS } from "./mock-data";
import { SearchDetail } from "./search-detail";
import { SearchFilters } from "./search-filters";
import { SearchInput } from "./search-input";
import { SearchResultCard } from "./search-result-card";

export function SearchShell() {
	const [query, setQuery] = useState("meloxicam gatos dosis");
	const [selectedId, setSelectedId] = useState(MOCK_RESULTS[0]?.id ?? "");
	const selectedResult =
		MOCK_RESULTS.find((result) => result.id === selectedId) ?? MOCK_RESULTS[0];

	return (
		<main className="min-h-dvh px-4 py-6 sm:px-6 lg:px-8">
			<div className="mx-auto flex w-full max-w-[116rem] flex-col gap-8">
				<header className="grid gap-6">
					<div className="flex items-start justify-center lg:relative">
						<VetpediaLogo className="justify-center" />
						<nav className="absolute top-6 right-8 hidden items-center gap-5 text-sm lg:flex">
							<a
								className="inline-flex items-center gap-2 transition-colors hover:text-primary"
								href="/"
							>
								<HelpCircleIcon aria-hidden="true" className="size-5" />
								Ayuda
							</a>
							<a
								className="inline-flex items-center gap-2 transition-colors hover:text-primary"
								href="/"
							>
								<BookmarkIcon aria-hidden="true" className="size-5" />
								Guardados
							</a>
						</nav>
					</div>
					<SearchInput onChange={setQuery} value={query} />
					<SearchFilters />
				</header>

				<section className="grid gap-4">
					<div className="grid gap-4 lg:grid-cols-[minmax(0,0.92fr)_minmax(34rem,1.08fr)]">
						<div className="flex items-center justify-between gap-4 text-muted-foreground text-sm">
							<p>28 resultados encontrados</p>
							<button
								className="hidden min-h-10 rounded-[var(--radius)] px-3 transition-colors hover:bg-muted hover:text-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-ring focus-visible:outline-offset-2 sm:inline-flex sm:items-center"
								type="button"
							>
								Ordenar por: Relevancia
							</button>
						</div>
					</div>

					<div className="grid gap-6 lg:grid-cols-[minmax(0,0.92fr)_minmax(34rem,1.08fr)] lg:items-start">
						<ul className="grid gap-4">
							{MOCK_RESULTS.map((result) => (
								<SearchResultCard
									isSelected={result.id === selectedId}
									key={result.id}
									onSelect={() => setSelectedId(result.id)}
									result={result}
								/>
							))}
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
