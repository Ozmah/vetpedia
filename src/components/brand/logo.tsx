import { cn } from "../../lib/cn";

type VetpediaLogoProps = {
	className?: string;
	markClassName?: string;
};

export function VetpediaLogo({ className, markClassName }: VetpediaLogoProps) {
	return (
		<div className={cn("flex items-center gap-3", className)}>
			<VetpediaMark className={cn("size-12 text-primary", markClassName)} />
			<div className="leading-none">
				<p className="font-heading font-semibold text-4xl text-foreground tracking-[-0.045em]">
					Vetpedia
				</p>
				<p className="mt-1.5 text-muted-foreground text-sm tracking-wide">
					Farmacología veterinaria
				</p>
			</div>
		</div>
	);
}

function VetpediaMark({ className }: { className?: string }) {
	return (
		<svg
			aria-hidden="true"
			className={className}
			viewBox="0 0 150 150"
			xmlns="http://www.w3.org/2000/svg"
		>
			<path
				className="fill-current"
				d="m75.3 78.2c-6.1-6.3-5.9-14.7-8.2-22.2-1.3-4.3-3.1-8.2-5.2-12-1.9 2.6-4.2 7.7-7.4 8-3.6 0.2-7-4.7-8.3-9-0.8-3-1.3-7.5-0.7-10.6 0.7 3.5 2.8 16.1 8.4 17 1.6 0.2 2.5-0.9 3.3-2 1.8-2.3 6.3-8.2 7.1-11.1 1-3.4-4.2-3.6-7-4.4-1.6-0.4-7.2-3.7-11.6-3.7-1.8 0-4.1 2.5-5.3 3.5-5.9 0.8-17.2 4.9-18 11.1-0.7 4.9-5.5 5.8-13.6 9.3-1.7 0.6-5.3 2-6 3-1.1 1.3-0.8 2.5 1.1 5.5 0.3 0.5 0.7 1.8 1.1 2.4 3.4 6.6 10.5 7.4 22.5 5.9 3.7-0.4 5.8 0.2 7.2 2.1 3.4 4.1 2.2 11.6-1.8 18.4-3.3 5.8-6.4 11.2-6.4 18.3 0.1 5.7 2.2 10.5 5 14.1h3.8c0.3-6.1 4.1-15.7 15.6-22.8 8.7-5.8 20.7-9.4 24.3-19l0.1-1.8z"
			/>
			<path
				className="fill-current"
				d="m147 56.8c-6.7-2.9-14.8-4.7-17.6-6.2-4.2-1.9-4.9-5-6.3-7.2-3-3.9-11-5.9-19.3-5.8-5.1 0.1-10.7 1.8-14.2 5.4-4.5 4.4-8.5 12.2-9.7 22.1-0.6 4.9-0.8 10.3-3.5 16-6.3 12.3-18.7 13.5-28.8 22.5-3.5 3.1-8.8 9.3-9.7 18.2h77.3c2.2-3.1 4.4-7.4 4.5-13.5 0.2-9.3-3.7-12.7-5.1-21.8-0.6-3.9-1.5-12 1.8-15.1 3.2-3 8.2-1.9 11.7-1.7 5.4 0.4 13.5 0.2 17-5.3 1.8-0.5 3.2-4.4 3-4.4 0.7-1.4-0.1-2.6-1.1-3.2zm-45.8 26c-7.1 0.2-10-4.2-12.7-9.2-2.1-3.9-6-5.6-6.4-10.7-0.5-6.4 5-14 9.4-17.8-2.4 2.9-6.2 9.4-7 14.5-0.9 6.8 3.5 8 6.5 14 3.7 7.4 10.4 8.8 13.4 6.4 3.2-2.4 4.2-9.1 0.7-16.4-2-4.4-5.5-9.7-3.5-16.7 0.1-1-0.4 3.7 2 8.8 2.5 5.3 5.4 9.4 5.4 15.8 0.1 5.6-2.4 11.1-7.8 11.3z"
			/>
		</svg>
	);
}
