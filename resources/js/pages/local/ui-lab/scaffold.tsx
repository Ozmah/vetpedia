import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type LabSectionProps = {
    id: string;
    title: string;
    children: ReactNode;
};

export function LabSection({ id, title, children }: LabSectionProps) {
    const headingId = `${id}-heading`;

    return (
        <section
            id={id}
            aria-labelledby={headingId}
            className="scroll-mt-20 border-b border-border px-4 py-8 last:border-b-0 sm:px-6 lg:px-8"
        >
            <header>
                <h2
                    id={headingId}
                    className="text-2xl font-semibold tracking-tight text-balance"
                >
                    {title}
                </h2>
            </header>

            <div className="@container mt-6">{children}</div>
        </section>
    );
}

type SpecimenProps = {
    title: string;
    test?: string;
    className?: string;
    contentClassName?: string;
    children: ReactNode;
};

export function Specimen({
    title,
    test,
    className,
    contentClassName,
    children,
}: SpecimenProps) {
    return (
        <article
            className={cn(
                'min-w-0 border border-border bg-background',
                className,
            )}
        >
            <header className="border-b border-border bg-muted/30 px-4 py-3">
                <h3 className="font-medium text-foreground">{title}</h3>
                {test && (
                    <p className="mt-1 font-mono text-xs text-muted-foreground">
                        Prueba: {test}
                    </p>
                )}
            </header>
            <div className={cn('min-w-0 p-4 sm:p-5', contentClassName)}>
                {children}
            </div>
        </article>
    );
}
