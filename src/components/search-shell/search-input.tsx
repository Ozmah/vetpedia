import { SearchIcon, XIcon } from "lucide-react";

type SearchInputProps = {
	value: string;
	onChange: (value: string) => void;
};

export function SearchInput({ value, onChange }: SearchInputProps) {
	return (
		<div className="relative mx-auto w-full max-w-2xl">
			<label className="sr-only" htmlFor="vetpedia-search">
				Buscar en Vetpedia
			</label>
			<SearchIcon
				aria-hidden="true"
				className="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-muted-foreground sm:left-5"
			/>
			<input
				className="min-h-14 w-full rounded-[1rem] border border-input bg-card/70 pr-13 pl-12 font-sans text-[16px] text-foreground shadow-[0_10px_34px_var(--shadow-soft)] outline-none transition-[border-color,box-shadow,background-color] duration-150 placeholder:text-muted-foreground/70 focus:border-primary focus:bg-card focus:shadow-[0_0_0_4px_var(--primary-wash),0_10px_34px_var(--shadow-soft)] sm:min-h-16 sm:rounded-[1.15rem] sm:pr-14 sm:pl-14 sm:text-lg"
				id="vetpedia-search"
				onChange={(event) => onChange(event.target.value)}
				placeholder="Buscar fármaco, especie o indicación"
				type="search"
				value={value}
			/>
			{value ? (
				<button
					aria-label="Limpiar búsqueda"
					className="absolute top-1/2 right-2.5 grid size-10 -translate-y-1/2 place-items-center rounded-[0.8rem] text-muted-foreground transition-colors duration-150 hover:bg-muted hover:text-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-ring focus-visible:outline-offset-2 sm:right-3"
					onClick={() => onChange("")}
					type="button"
				>
					<XIcon aria-hidden="true" className="size-4" />
				</button>
			) : null}
		</div>
	);
}
