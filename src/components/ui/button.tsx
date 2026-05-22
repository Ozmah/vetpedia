import type { ButtonHTMLAttributes, ReactNode } from "react";
import { cn } from "../../lib/cn";

type ButtonProps = ButtonHTMLAttributes<HTMLButtonElement> & {
	icon?: ReactNode;
	variant?: "primary" | "secondary" | "ghost" | "danger";
};

export function Button({
	className,
	icon,
	variant = "primary",
	type = "button",
	children,
	...props
}: ButtonProps) {
	return (
		<button
			className={cn(
				"group inline-flex min-h-11 cursor-pointer touch-manipulation items-center justify-center gap-3 rounded-[0.85rem] border px-4 font-semibold text-sm transition-[background-color,border-color,color,box-shadow,transform] duration-150 ease-out focus-visible:outline focus-visible:outline-2 focus-visible:outline-ring focus-visible:outline-offset-2 active:enabled:translate-y-px active:enabled:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-60 motion-reduce:transition-none motion-reduce:active:translate-y-0 motion-reduce:active:scale-100",
				variant === "primary" &&
					"border-primary bg-primary text-primary-foreground shadow-[0_10px_28px_var(--shadow-soft)] hover:bg-primary/92",
				variant === "secondary" &&
					"border border-border bg-card/70 text-foreground hover:border-primary/50 hover:bg-card",
				variant === "ghost" &&
					"border-transparent text-muted-foreground hover:bg-muted hover:text-foreground",
				variant === "danger" &&
					"border border-destructive/35 bg-destructive/8 text-destructive hover:bg-destructive/12",
				className,
			)}
			type={type}
			{...props}
		>
			<span>{children}</span>
			{icon ? (
				<span className="inline-flex text-[1.15em] leading-none transition-transform duration-150 ease-out group-disabled:translate-x-0">
					{icon}
				</span>
			) : null}
		</button>
	);
}
