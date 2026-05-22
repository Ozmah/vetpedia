import { SearchIcon, XIcon } from "lucide-react";

type SearchInputProps = {
	value: string;
	onChange: (value: string) => void;
};

export function SearchInput({ value, onChange }: SearchInputProps) {
	return (
		<div className="relative mx-auto w-full max-w-4xl">
			<label className="sr-only" htmlFor="vetpedia-search">
				Buscar en Vetpedia
			</label>
			<SearchIcon
				aria-hidden="true"
				className="pointer-events-none absolute top-1/2 left-5 size-6 -translate-y-1/2 text-muted-foreground sm:left-7"
			/>
			<input
				className="min-h-16 w-full rounded-[1.35rem] border border-input bg-surface-glass pr-16 pl-14 font-sans text-[16px] text-foreground shadow-[0_16px_60px_var(--shadow-soft)] outline-none transition-[border-color,box-shadow,background-color] duration-200 placeholder:text-muted-foreground/70 focus:border-primary focus:shadow-[0_0_0_4px_var(--primary-wash),0_16px_60px_var(--shadow-soft)] sm:min-h-20 sm:pl-20 sm:text-xl"
				id="vetpedia-search"
				onChange={(event) => onChange(event.target.value)}
				placeholder="meloxicam gatos dosis"
				type="search"
				value={value}
			/>
			{value ? (
				<button
					aria-label="Limpiar búsqueda"
					className="absolute top-1/2 right-4 grid size-11 -translate-y-1/2 place-items-center rounded-full text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-ring focus-visible:outline-offset-2 sm:right-5"
					onClick={() => onChange("")}
					type="button"
				>
					<XIcon aria-hidden="true" className="size-5" />
				</button>
			) : null}
		</div>
	);
}
