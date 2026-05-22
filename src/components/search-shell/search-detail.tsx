import { AlertTriangleIcon, BookOpenIcon, CatIcon } from "lucide-react";
import { StatusBadge } from "./status-badge";
import type { SearchResult } from "./types";

type SearchDetailProps = {
	result: SearchResult;
};

export function SearchDetail({ result }: SearchDetailProps) {
	return (
		<article className="overflow-hidden rounded-[1.4rem] border border-border bg-card/72 shadow-[0_18px_70px_var(--shadow-soft)] backdrop-blur-sm">
			<header className="border-border border-b p-5 sm:p-7">
				<p className="mb-4 font-bold text-muted-foreground text-xs tracking-[0.14em]">
					FÁRMACO
				</p>
				<div className="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
					<div>
						<h2 className="font-heading font-semibold text-4xl text-foreground tracking-[-0.045em]">
							{result.detail.title}
						</h2>
						<div className="mt-4 flex flex-wrap gap-2">
							<StatusBadge status={result.status} />
							{result.detail.badges.map((badge) => (
								<span
									className="inline-flex min-h-8 items-center rounded-[0.65rem] border border-border bg-background/40 px-3 font-medium font-mono text-[0.68rem]"
									key={badge}
								>
									{badge}
								</span>
							))}
						</div>
						<p className="mt-5 text-muted-foreground text-sm">
							{result.detail.description}
						</p>
					</div>
					<div className="hidden rounded-2xl border border-border bg-background/35 p-4 text-muted-foreground text-xs sm:block">
						Estructura molecular pendiente
					</div>
				</div>
			</header>

			<div className="grid gap-6 p-5 sm:p-7 xl:grid-cols-[1fr_22rem]">
				<section>
					<h3 className="mb-4 font-heading font-semibold text-2xl tracking-[-0.03em]">
						Dosis recomendadas
					</h3>
					<div className="space-y-6">
						{result.detail.dosage.length ? (
							result.detail.dosage.map((group) => (
								<div key={group.species}>
									<p className="mb-3 inline-flex items-center gap-2 rounded-[0.75rem] border border-border bg-muted px-3 py-2 font-mono font-semibold text-[0.72rem]">
										<CatIcon
											aria-hidden="true"
											className="size-4 text-primary"
										/>
										{group.species}
									</p>
									<ul className="space-y-2 text-sm leading-7">
										{group.items.map((item) => (
											<li className="flex gap-3" key={item}>
												<span className="mt-3 size-1.5 shrink-0 rounded-full bg-primary" />
												<span>{item}</span>
											</li>
										))}
									</ul>
								</div>
							))
						) : (
							<p className="rounded-2xl border border-border bg-muted/40 p-4 text-muted-foreground text-sm">
								Esta unidad no contiene una sección de dosis en el mock visual.
							</p>
						)}
					</div>
				</section>

				<aside className="space-y-4">
					<div className="rounded-2xl border border-border bg-background/36 p-5">
						<h3 className="mb-4 flex items-center gap-2 font-semibold text-base">
							<BookOpenIcon
								aria-hidden="true"
								className="size-5 text-primary"
							/>
							Notas relacionadas
						</h3>
						<ul className="space-y-3 text-sm leading-6">
							{result.detail.notes.map((note) => (
								<li className="flex gap-3" key={note}>
									<span className="mt-2.5 size-1.5 shrink-0 rounded-full bg-primary" />
									<span>{note}</span>
								</li>
							))}
						</ul>
					</div>

					{result.detail.warning ? (
						<div className="rounded-2xl border border-destructive/35 bg-destructive/8 p-5 text-destructive">
							<h3 className="mb-3 flex items-center gap-2 font-semibold">
								<AlertTriangleIcon aria-hidden="true" className="size-5" />
								Advertencia
							</h3>
							<p className="text-sm leading-6">{result.detail.warning}</p>
						</div>
					) : null}
				</aside>
			</div>

			<footer className="border-border border-t p-5 sm:p-7">
				<div className="rounded-2xl border border-border bg-background/38 p-4 text-sm">
					<p className="font-semibold">Fuente</p>
					<p className="mt-1 text-muted-foreground">{result.source}</p>
					<dl className="mt-4 grid gap-2 border-border border-t pt-4 font-mono text-[0.72rem] text-muted-foreground leading-5">
						<div className="grid gap-1 sm:grid-cols-[7rem_1fr]">
							<dt className="uppercase tracking-[0.12em]">sourceSpan</dt>
							<dd>{result.sourceMeta}</dd>
						</div>
						<div className="grid gap-1 sm:grid-cols-[7rem_1fr]">
							<dt className="uppercase tracking-[0.12em]">unitId</dt>
							<dd className="break-all">{result.unitId}</dd>
						</div>
					</dl>
				</div>
			</footer>
		</article>
	);
}
