import { FloppyDiskIcon } from '@phosphor-icons/react/FloppyDisk';
import { PawPrintIcon } from '@phosphor-icons/react/PawPrint';
import { PlusIcon } from '@phosphor-icons/react/Plus';
import { TrashIcon } from '@phosphor-icons/react/Trash';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { LabSection, Specimen } from './scaffold';

const swatches = [
    {
        name: 'Fondo',
        token: '--background',
        className: 'bg-background text-foreground',
    },
    {
        name: 'Primer plano',
        token: '--foreground',
        className: 'bg-foreground text-background',
    },
    {
        name: 'Primario',
        token: '--primary',
        className: 'bg-primary text-primary-foreground',
    },
    {
        name: 'Secundario',
        token: '--secondary',
        className: 'bg-secondary text-secondary-foreground',
    },
    {
        name: 'Atenuado',
        token: '--muted',
        className: 'bg-muted text-muted-foreground',
    },
    {
        name: 'Acento',
        token: '--accent',
        className: 'bg-accent text-accent-foreground',
    },
    {
        name: 'Destructivo',
        token: '--destructive',
        className: 'bg-destructive text-white',
    },
    {
        name: 'Tarjeta',
        token: '--card',
        className: 'bg-card text-card-foreground',
    },
] as const;

export function FoundationsSection() {
    return (
        <LabSection id="foundations" title="Fundamentos">
            <div className="grid gap-4 @4xl:grid-cols-[3fr_2fr]">
                <Specimen
                    title="Colores semánticos"
                    test="cambiar entre los temas claro, oscuro y del sistema."
                    contentClassName="grid grid-cols-2 gap-3 @xl:grid-cols-4"
                >
                    {swatches.map((swatch) => (
                        <div
                            key={swatch.token}
                            className={`flex min-h-24 flex-col justify-between border border-border p-3 ${swatch.className}`}
                        >
                            <p className="font-medium">{swatch.name}</p>
                            <code>{swatch.token}</code>
                        </div>
                    ))}
                </Specimen>

                <Specimen title="Tipografía">
                    <div className="flex flex-col gap-5">
                        <div className="flex items-start gap-3">
                            <div className="flex size-10 shrink-0 items-center justify-center bg-primary text-primary-foreground">
                                <PawPrintIcon
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            </div>
                            <div className="min-w-0">
                                <p className="font-mono text-xs tracking-wide text-muted-foreground uppercase">
                                    Entrada de especie · VP-00421
                                </p>
                                <h3 className="mt-1 text-2xl font-semibold tracking-tight text-balance">
                                    Ctenocephalides felis felis
                                </h3>
                                <p className="mt-1 text-base text-muted-foreground sm:text-sm">
                                    Pulga del gato · Pulicidae
                                </p>
                            </div>
                        </div>

                        <Separator />

                        <p className="max-w-[64ch] text-base text-pretty sm:text-sm">
                            Insecto ectoparásito de importancia veterinaria en
                            animales de compañía. Los adultos viven sobre el
                            hospedador y se alimentan de sangre.
                        </p>
                    </div>
                </Specimen>
            </div>
        </LabSection>
    );
}

export function PrimitivesSection() {
    return (
        <LabSection id="primitives" title="Acciones de entrada">
            <Specimen title="Barra de acciones">
                <div className="flex flex-wrap items-center gap-2">
                    <Button type="button">
                        <FloppyDiskIcon data-icon="inline-start" />
                        Guardar borrador
                    </Button>
                    <Button type="button" variant="secondary">
                        <PlusIcon data-icon="inline-start" />
                        Agregar fuente
                    </Button>
                    <Button type="button" variant="outline">
                        Vista previa
                    </Button>
                    <Button type="button" variant="ghost">
                        Cancelar
                    </Button>
                    <Button type="button" variant="destructive">
                        <TrashIcon data-icon="inline-start" />
                        Eliminar
                    </Button>
                </div>

                <Separator className="my-5" />

                <div className="flex flex-wrap items-center gap-2">
                    <Badge>documentada</Badge>
                    <Badge variant="secondary">borrador</Badge>
                    <Badge variant="outline">3 fuentes</Badge>
                    <Badge variant="destructive">archivada</Badge>
                </div>
            </Specimen>
        </LabSection>
    );
}
