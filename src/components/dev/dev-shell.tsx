import { Link } from "@tanstack/react-router";
import type { ReactNode } from "react";
import { VetpediaLogo } from "../brand/logo";

type DevShellProps = {
	children: ReactNode;
	description: string;
	title: string;
};

export function DevShell({ children, description, title }: DevShellProps) {
	return (
		<main className="min-h-dvh px-4 py-6 sm:px-6 lg:px-8">
			<div className="mx-auto grid w-full max-w-5xl gap-6">
				<header className="grid gap-5 rounded-[1.4rem] border border-border bg-card/52 p-5 shadow-[0_16px_60px_var(--shadow-soft)] sm:p-6">
					<div className="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
						<VetpediaLogo />
						<nav className="flex flex-wrap gap-2">
							<Link
								className="inline-flex min-h-10 items-center rounded-[0.8rem] border border-border bg-background/45 px-3 font-semibold text-sm transition-colors hover:border-primary/50 hover:bg-card"
								to="/dev/entry"
							>
								Captura
							</Link>
							<Link
								className="inline-flex min-h-10 items-center rounded-[0.8rem] border border-border bg-background/45 px-3 font-semibold text-sm transition-colors hover:border-primary/50 hover:bg-card"
								to="/dev/database"
							>
								Base de datos
							</Link>
						</nav>
					</div>
					<div>
						<p className="font-mono text-[0.72rem] text-primary uppercase tracking-[0.14em]">
							Dev only
						</p>
						<h1 className="mt-2 font-heading font-semibold text-3xl tracking-[-0.04em] sm:text-4xl">
							{title}
						</h1>
						<p className="mt-3 max-w-2xl text-muted-foreground leading-7">
							{description}
						</p>
					</div>
				</header>

				{children}
			</div>
		</main>
	);
}
