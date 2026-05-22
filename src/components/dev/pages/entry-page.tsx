"use client";

import { useNavigate } from "@tanstack/react-router";
import { useMemo, useState } from "react";
import type {
	DictionarySectionInput,
	DictionarySpecies,
	DictionaryStatus,
	DictionaryUnitDetail,
	DictionaryUnitInput,
} from "../../../lib/dictionary/types";
import { Button } from "../../ui/button";
import { Select } from "../../ui/dropdown";
import { TextArea, TextInput } from "../../ui/text-field";
import { DevShell } from "../dev-shell";

type EntryPageProps = {
	initialCount: number;
	initialUnit: DictionaryUnitDetail | null;
	unitId?: string;
};

const SECTION_FIELDS: Array<Pick<DictionarySectionInput, "key" | "title">> = [
	{ key: "presentations", title: "Presentaciones" },
	{ key: "action", title: "Acción" },
	{ key: "use", title: "Uso" },
	{ key: "indications", title: "Indicaciones" },
	{ key: "dosage", title: "Dosis" },
	{ key: "contraindications", title: "Contraindicaciones" },
	{ key: "adverseReactions", title: "Reacciones adversas" },
	{ key: "interactions", title: "Interacciones" },
	{ key: "safetyHandling", title: "Seguridad y manejo" },
	{ key: "references", title: "Referencias" },
];

const SPECIES_OPTIONS: Array<{ label: string; value: DictionarySpecies }> = [
	{ label: "Perro", value: "perro" },
	{ label: "Gato", value: "gato" },
	{ label: "Otro", value: "otro" },
	{ label: "Desconocido", value: "desconocido" },
];

const KIND_OPTIONS = [
	{
		description: "Monografía principal de un medicamento.",
		label: "Fármaco",
		value: "drug_monograph",
	},
	{
		description: "Entrada que apunta a otro registro canónico.",
		label: "Referencia cruzada",
		value: "cross_reference",
	},
];

const STATUS_OPTIONS: Array<{
	description: string;
	label: string;
	value: DictionaryStatus;
}> = [
	{ description: "Captura incompleta.", label: "Borrador", value: "raw" },
	{
		description: "Necesita corrección antes de usarse.",
		label: "Requiere revisión",
		value: "needs_review",
	},
	{
		description: "Revisado manualmente, listo para búsqueda interna.",
		label: "Revisión humana",
		value: "soft_approved",
	},
	{
		description: "Validado clínicamente por veterinario.",
		label: "Validado veterinario",
		value: "vet_approved",
	},
];

export function EntryPage({
	initialCount,
	initialUnit,
	unitId,
}: EntryPageProps) {
	const navigate = useNavigate();
	const [form, setForm] = useState(() =>
		initialUnit ? detailToForm(initialUnit) : emptyForm(),
	);
	const [count, setCount] = useState(initialCount);
	const [isLoading, setIsLoading] = useState(false);
	const [message, setMessage] = useState<{
		tone: "error" | "success";
		text: string;
	} | null>(null);
	const mode = form.id ? "Editar registro" : "Nuevo registro";
	const aliasText = useMemo(() => form.aliases.join(", "), [form.aliases]);

	async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
		event.preventDefault();
		setIsLoading(true);
		setMessage(null);

		try {
			const result = await fetchJson<{
				count: number;
				unit: DictionaryUnitDetail;
			}>("/api/dev/dictionary", {
				body: JSON.stringify(form),
				headers: { "Content-Type": "application/json" },
				method: "POST",
			});

			setCount(result.count);
			setForm(detailToForm(result.unit));
			setMessage({ tone: "success", text: "Registro guardado correctamente." });

			if (result.unit.id !== unitId) {
				navigate({
					to: "/dev/entry",
					search: { unitId: result.unit.id },
					replace: true,
				});
			}
		} catch (error) {
			setMessage({ tone: "error", text: errorMessage(error) });
		} finally {
			setIsLoading(false);
		}
	}

	return (
		<DevShell
			description="Forma local para capturar monografías curadas manualmente. Esta página no shippea para producción."
			title="Captura de diccionario"
		>
			<section className="grid gap-4 rounded-[1.4rem] border border-border bg-card/58 p-5 shadow-[0_16px_60px_var(--shadow-soft)] sm:p-6">
				<div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
					<div>
						<p className="font-mono text-[0.72rem] text-muted-foreground uppercase tracking-[0.14em]">
							{mode}
						</p>
						<h2 className="mt-1 font-heading font-semibold text-2xl tracking-[-0.035em]">
							Diccionario: {count} registros
						</h2>
					</div>
					<Button
						onClick={() => {
							setForm(emptyForm());
							setMessage(null);
							navigate({ to: "/dev/entry", search: {}, replace: true });
						}}
						variant="secondary"
					>
						Nuevo
					</Button>
				</div>

				{message ? <Notice tone={message.tone}>{message.text}</Notice> : null}

				<form className="grid gap-5" onSubmit={handleSubmit}>
					<div className="grid gap-4 md:grid-cols-2">
						<TextInput
							label="Título"
							onChange={(event) =>
								setForm({ ...form, title: event.target.value })
							}
							placeholder="Meloxicam"
							required
							value={form.title}
						/>
						<TextInput
							description="Nombre mostrado si necesitas separar título original y display."
							label="Título en español / display"
							onChange={(event) =>
								setForm({ ...form, titleEs: event.target.value })
							}
							placeholder="Opcional"
							value={form.titleEs ?? ""}
						/>
						<Select
							label="Tipo"
							onValueChange={(value) =>
								setForm({ ...form, kind: value as DictionaryUnitInput["kind"] })
							}
							options={KIND_OPTIONS}
							value={form.kind}
						/>
						<Select
							label="Estado"
							onValueChange={(value) =>
								setForm({ ...form, status: value as DictionaryStatus })
							}
							options={STATUS_OPTIONS}
							value={form.status}
						/>
					</div>

					<TextInput
						description="Separados por coma."
						label="Alias / nombres comerciales"
						onChange={(event) =>
							setForm({ ...form, aliases: splitCsv(event.target.value) })
						}
						placeholder="Metacam, Loxicom"
						value={aliasText}
					/>

					<div className="grid gap-2">
						<p className="font-mono font-semibold text-[0.72rem] text-primary uppercase leading-tight tracking-[0.14em]">
							Especies
						</p>
						<div className="flex flex-wrap gap-2">
							{SPECIES_OPTIONS.map((option) => (
								<label
									className="group relative inline-flex min-h-10 cursor-pointer items-center gap-2 rounded-[0.8rem] border border-border bg-background/45 px-3 font-medium text-sm transition-[background-color,border-color,color] duration-150 hover:border-primary/50"
									key={option.value}
								>
									<input
										checked={form.species.includes(option.value)}
										className="peer sr-only"
										onChange={(event) =>
											setForm({
												...form,
												species: event.target.checked
													? [...form.species, option.value]
													: form.species.filter(
															(item) => item !== option.value,
														),
											})
										}
										type="checkbox"
									/>
									<span className="size-3 rounded-[0.28rem] border border-border bg-card peer-checked:border-primary peer-checked:bg-primary" />
									<span className="peer-checked:text-primary">
										{option.label}
									</span>
								</label>
							))}
						</div>
					</div>

					<div className="grid gap-4">
						{SECTION_FIELDS.map((section) => (
							<TextArea
								key={section.key}
								label={section.title}
								onChange={(event) =>
									updateSection(section.key, event.target.value, setForm)
								}
								placeholder={`Agregar ${section.title.toLowerCase()}...`}
								value={
									form.sections.find((item) => item.key === section.key)
										?.text ?? ""
								}
							/>
						))}
					</div>

					<div className="flex flex-col gap-3 sm:flex-row sm:justify-end">
						<Button disabled={isLoading} type="submit">
							{isLoading ? "Guardando…" : "Guardar registro"}
						</Button>
					</div>
				</form>
			</section>
		</DevShell>
	);
}

function Notice({
	children,
	tone,
}: {
	children: string;
	tone: "error" | "success";
}) {
	return (
		<p
			className={
				tone === "success"
					? "rounded-[0.85rem] border border-success/35 bg-success/8 p-3 text-sm text-success"
					: "rounded-[0.85rem] border border-destructive/35 bg-destructive/8 p-3 text-destructive text-sm"
			}
		>
			{children}
		</p>
	);
}

function emptyForm(): DictionaryUnitInput {
	return {
		aliases: [],
		kind: "drug_monograph",
		sections: SECTION_FIELDS.map((section) => ({ ...section, text: "" })),
		species: ["perro", "gato"],
		status: "soft_approved",
		title: "",
		titleEs: "",
	};
}

function detailToForm(unit: DictionaryUnitDetail): DictionaryUnitInput {
	return {
		aliases: unit.aliases,
		id: unit.id,
		kind: unit.kind,
		sections: SECTION_FIELDS.map((section) => ({
			...section,
			text: unit.sections.find((item) => item.key === section.key)?.text ?? "",
		})),
		species: unit.species,
		status: unit.status,
		title: unit.title,
		titleEs: unit.titleEs ?? "",
	};
}

function updateSection(
	key: DictionarySectionInput["key"],
	text: string,
	setForm: React.Dispatch<React.SetStateAction<DictionaryUnitInput>>,
) {
	setForm((current) => ({
		...current,
		sections: current.sections.map((section) =>
			section.key === key ? { ...section, text } : section,
		),
	}));
}

function splitCsv(value: string) {
	return value
		.split(",")
		.map((item) => item.trim())
		.filter(Boolean);
}

async function fetchJson<T>(url: string, init?: RequestInit): Promise<T> {
	const response = await fetch(url, init);

	if (!response.ok) {
		const error = (await response.json().catch(() => null)) as {
			message?: string;
		} | null;

		throw new Error(error?.message ?? "Request failed");
	}

	return response.json() as Promise<T>;
}

function errorMessage(error: unknown) {
	return error instanceof Error
		? error.message
		: "Ocurrió un error inesperado.";
}
