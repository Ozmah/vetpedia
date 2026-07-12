import { CheckCircleIcon } from '@phosphor-icons/react/CheckCircle';
import { MagnifyingGlassIcon } from '@phosphor-icons/react/MagnifyingGlass';
import { WarningCircleIcon } from '@phosphor-icons/react/WarningCircle';
import type { ReactNode } from 'react';
import { useState } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Icon } from '@/components/ui/icon';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import { Skeleton } from '@/components/ui/skeleton';
import { Spinner } from '@/components/ui/spinner';
import { LabSection, Specimen } from './scaffold';

type FeedbackState = 'loading' | 'empty' | 'error' | 'success';

const feedbackStates: FeedbackState[] = [
    'loading',
    'empty',
    'error',
    'success',
];

const feedbackStateLabels: Record<FeedbackState, string> = {
    loading: 'Cargando',
    empty: 'Vacío',
    error: 'Error',
    success: 'Éxito',
};

const entries = [
    {
        id: 'VP-00421',
        title: 'Infección por parvovirus canino',
        type: 'Enfermedad',
        status: 'documented',
        species: 'Canis lupus familiaris',
        updated: 'Hace 12 minutos',
    },
    {
        id: 'VP-00422',
        title: 'Ctenocephalides felis felis',
        type: 'Parásito',
        status: 'draft',
        species: 'Felis catus · Canis lupus familiaris',
        updated: 'Ayer',
    },
    {
        id: 'VP-00423',
        title: 'Enteropatía inflamatoria crónica con complicaciones por pérdida de proteínas',
        type: 'Enfermedad',
        status: 'archived',
        species: 'Canis lupus familiaris',
        updated: '30 de mayo de 2026',
    },
] as const;

export function FeedbackSection() {
    const [state, setState] = useState<FeedbackState>('loading');

    return (
        <LabSection id="feedback" title="Estados de resultados">
            <Specimen title="Resultados del catálogo">
                <div className="flex flex-col gap-5">
                    <div
                        role="group"
                        className="flex flex-wrap items-center gap-2 border border-dashed border-border bg-muted/30 p-3"
                        aria-label="Estado de prueba de los resultados"
                    >
                        <span className="mr-1 text-sm font-medium">
                            Estado de prueba:
                        </span>
                        {feedbackStates.map((feedbackState) => (
                            <Button
                                key={feedbackState}
                                type="button"
                                size="sm"
                                variant={
                                    state === feedbackState
                                        ? 'secondary'
                                        : 'outline'
                                }
                                aria-pressed={state === feedbackState}
                                onClick={() => setState(feedbackState)}
                            >
                                {feedbackStateLabels[feedbackState]}
                            </Button>
                        ))}
                    </div>

                    <div className="min-h-52 border border-border bg-muted/20 p-4">
                        {state === 'loading' && <LoadingState />}
                        {state === 'empty' && <EmptyState />}
                        {state === 'error' && <ErrorState />}
                        {state === 'success' && <SuccessState />}
                    </div>
                </div>
            </Specimen>
        </LabSection>
    );
}

function LoadingState() {
    return (
        <div
            className="flex flex-col gap-4"
            aria-label="Cargando entradas del catálogo"
        >
            <div className="flex items-center gap-2 text-muted-foreground">
                <Spinner className="shrink-0" aria-label="Cargando" />
                <p className="text-base sm:text-sm">
                    Cargando entradas del catálogo…
                </p>
            </div>
            {[0, 1, 2].map((item) => (
                <div
                    key={item}
                    className="grid grid-cols-[3rem_minmax(0,1fr)] gap-3"
                >
                    <Skeleton className="size-12 rounded-none" />
                    <div className="flex min-w-0 flex-col gap-2">
                        <Skeleton className="h-4 w-2/3 rounded-none" />
                        <Skeleton className="h-3 w-1/3 rounded-none" />
                    </div>
                </div>
            ))}
        </div>
    );
}

function EmptyState() {
    return (
        <div className="relative isolate flex min-h-44 flex-col items-center justify-center gap-3 overflow-hidden px-4 text-center">
            <PlaceholderPattern
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 -z-10 size-full [mask-image:radial-gradient(ellipse_at_center,black,transparent_70%)] stroke-border/60"
            />
            <div className="flex size-10 items-center justify-center border border-border bg-background">
                <Icon iconNode={MagnifyingGlassIcon} className="size-5" />
            </div>
            <div>
                <h3 className="font-medium">No hay entradas coincidentes</h3>
                <p className="mt-1 max-w-[48ch] text-base text-pretty text-muted-foreground sm:text-sm">
                    Intenta quitar el filtro de especie o buscar mediante un
                    alias.
                </p>
            </div>
            <Button type="button" variant="outline" size="sm">
                Limpiar filtros
            </Button>
        </div>
    );
}

function ErrorState() {
    return (
        <div className="flex min-h-44 items-center justify-center">
            <Alert variant="destructive" className="max-w-xl">
                <WarningCircleIcon aria-hidden="true" />
                <AlertTitle>No fue posible cargar el catálogo</AlertTitle>
                <AlertDescription>
                    El catálogo no está disponible en este momento. Inténtalo
                    nuevamente.
                    <Button type="button" variant="outline" size="sm">
                        Reintentar
                    </Button>
                </AlertDescription>
            </Alert>
        </div>
    );
}

function SuccessState() {
    return (
        <div className="flex min-h-44 items-center justify-center">
            <Alert role="status" className="max-w-xl">
                <CheckCircleIcon aria-hidden="true" />
                <AlertTitle>Catálogo sincronizado</AlertTitle>
                <AlertDescription>
                    Tres entradas están listas para revisión editorial.
                </AlertDescription>
            </Alert>
        </div>
    );
}

export function CatalogSection() {
    return (
        <LabSection id="data-display" title="Tabla de entradas">
            <Card className="gap-0 py-0 shadow-none">
                <CardHeader className="gap-2 border-b border-border py-5">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <CardTitle>Cola editorial</CardTitle>
                        <Badge
                            variant="outline"
                            className="shrink-0 tabular-nums"
                        >
                            3 entradas
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent className="p-0">
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-3xl text-left">
                            <thead className="bg-muted/40">
                                <tr>
                                    <TableHead>Entrada</TableHead>
                                    <TableHead>Tipo</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Especies</TableHead>
                                    <TableHead>Actualización</TableHead>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border">
                                {entries.map((entry) => (
                                    <tr
                                        key={entry.id}
                                        className="hover:bg-muted/30"
                                    >
                                        <TableCell>
                                            <div className="max-w-sm">
                                                <p className="font-medium">
                                                    {entry.title}
                                                </p>
                                                <p className="mt-1 font-mono text-xs text-muted-foreground">
                                                    {entry.id}
                                                </p>
                                            </div>
                                        </TableCell>
                                        <TableCell>{entry.type}</TableCell>
                                        <TableCell>
                                            <EntryStatus
                                                status={entry.status}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <p className="max-w-xs text-pretty">
                                                {entry.species}
                                            </p>
                                        </TableCell>
                                        <TableCell>
                                            <p className="whitespace-nowrap text-muted-foreground">
                                                {entry.updated}
                                            </p>
                                        </TableCell>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </LabSection>
    );
}

function EntryStatus({
    status,
}: {
    status: (typeof entries)[number]['status'];
}) {
    if (status === 'documented') {
        return <Badge>documentada</Badge>;
    }

    if (status === 'archived') {
        return <Badge variant="destructive">archivada</Badge>;
    }

    return <Badge variant="secondary">borrador</Badge>;
}

function TableHead({ children }: { children: ReactNode }) {
    return (
        <th className="px-4 py-3 text-xs font-medium tracking-wide text-muted-foreground uppercase">
            {children}
        </th>
    );
}

function TableCell({ children }: { children: ReactNode }) {
    return <td className="px-4 py-3 align-top text-sm">{children}</td>;
}
