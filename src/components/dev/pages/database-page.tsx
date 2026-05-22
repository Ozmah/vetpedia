"use client";

import { Link, useRouter } from "@tanstack/react-router";
import type { ReactNode } from "react";
import type {
	DictionaryOverview,
	DictionaryUnitSummary,
} from "../../../lib/dictionary/types";
import { Button } from "../../ui/button";
import { DevShell } from "../dev-shell";

export function DatabasePage({ overview }: { overview: DictionaryOverview }) {
	const router = useRouter();

	return (
		<DevShell
			description="Vista local para revisar qué existe en el diccionario y abrir cualquier registro para edición. Esta página no shippea para producción."
			title="Contenido de la base de datos"
		>
			<section className="grid gap-4 rounded-[1.4rem] border border-border bg-card/58 p-5 shadow-[0_16px_60px_var(--shadow-soft)] sm:p-6">
				<div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
					<div>
						<p className="font-mono text-[0.72rem] text-muted-foreground uppercase tracking-[0.14em]">
							Diccionario
						</p>
						<h2 className="mt-1 font-heading font-semibold text-2xl tracking-[-0.035em]">
							{overview.count} registros
						</h2>
					</div>
					<div className="flex flex-wrap gap-2">
						<Button onClick={() => router.invalidate()} variant="secondary">
							Actualizar
						</Button>
						<Link to="/dev/entry">
							<Button>Nuevo registro</Button>
						</Link>
					</div>
				</div>

				{overview.units.length === 0 ? <EmptyDatabase /> : null}

				<ul className="grid gap-3">
					{overview.units.map((unit) => (
						<DictionaryRow key={unit.id} unit={unit} />
					))}
				</ul>
			</section>
		</DevShell>
	);
}

function DictionaryRow({ unit }: { unit: DictionaryUnitSummary }) {
	return (
		<li>
			<Link
				className="block rounded-[1.1rem] border border-border bg-background/38 p-4 text-foreground no-underline transition-[background-color,border-color,box-shadow] duration-150 hover:border-primary/55 hover:bg-card hover:shadow-[0_12px_36px_var(--shadow-soft)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-ring focus-visible:outline-offset-2"
				search={{ unitId: unit.id }}
				to="/dev/entry"
			>
				<div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
					<div>
						<p className="font-mono text-[0.68rem] text-primary uppercase tracking-[0.14em]">
							{unit.kind === "drug_monograph"
								? "Fármaco"
								: "Referencia cruzada"}
						</p>
						<h3 className="mt-1 font-heading font-semibold text-2xl tracking-[-0.035em]">
							{unit.titleEs || unit.title}
						</h3>
						<p className="mt-2 break-all font-mono text-[0.7rem] text-muted-foreground">
							{unit.id}
						</p>
					</div>
					<span className="inline-flex min-h-8 shrink-0 items-center rounded-[0.65rem] border border-border bg-card/70 px-3 font-mono text-[0.68rem]">
						{statusLabel(unit.status)}
					</span>
				</div>

				<div className="mt-4 flex flex-wrap gap-2">
					{unit.species.map((species) => (
						<Chip key={species}>{species}</Chip>
					))}
					{unit.aliases.slice(0, 4).map((alias) => (
						<Chip key={alias}>{alias}</Chip>
					))}
					<Chip>{unit.sectionCount} secciones</Chip>
					<Chip>Actualizado {formatDate(unit.updatedAt)}</Chip>
				</div>
			</Link>
		</li>
	);
}

function Chip({ children }: { children: ReactNode }) {
	return (
		<span className="inline-flex min-h-8 items-center rounded-[0.65rem] border border-border bg-card/58 px-3 font-mono text-[0.68rem] text-muted-foreground">
			{children}
		</span>
	);
}

function EmptyDatabase() {
	return (
		<div className="rounded-[1rem] border border-border bg-background/38 p-5 text-sm">
			<p className="font-semibold text-foreground">Sin registros todavía</p>
			<p className="mt-2 text-muted-foreground leading-6">
				Usa la página de captura para agregar la primera monografía al
				diccionario.
			</p>
		</div>
	);
}

function statusLabel(status: DictionaryUnitSummary["status"]) {
	const labels: Record<DictionaryUnitSummary["status"], string> = {
		raw: "Borrador",
		parsed: "Parsed",
		needs_review: "Requiere revisión",
		soft_approved: "Revisión humana",
		vet_approved: "Validado veterinario",
		rejected: "Rechazado",
	};

	return labels[status];
}

function formatDate(value: string) {
	return new Intl.DateTimeFormat("es-MX", {
		dateStyle: "medium",
		timeStyle: "short",
	}).format(new Date(value));
}
