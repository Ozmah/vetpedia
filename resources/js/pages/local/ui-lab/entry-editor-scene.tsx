import { FloppyDiskIcon } from '@phosphor-icons/react/FloppyDisk';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { LabSection } from './scaffold';

export function EntryEditorSection() {
    const [simulateLoading, setSimulateLoading] = useState(false);

    return (
        <LabSection id="forms" title="Formulario de entrada">
            <div className="flex flex-col gap-4">
                <div
                    role="group"
                    aria-label="Estado de prueba del formulario"
                    className="flex flex-wrap items-center gap-2 border border-dashed border-border bg-muted/30 p-3"
                >
                    <span className="mr-1 text-sm font-medium">
                        Estado de prueba:
                    </span>
                    <Button
                        type="button"
                        size="sm"
                        variant={!simulateLoading ? 'secondary' : 'outline'}
                        aria-pressed={!simulateLoading}
                        onClick={() => setSimulateLoading(false)}
                    >
                        Normal
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        variant={simulateLoading ? 'secondary' : 'outline'}
                        aria-pressed={simulateLoading}
                        onClick={() => setSimulateLoading(true)}
                    >
                        Guardando
                    </Button>
                </div>

                <Card className="gap-0 py-0 shadow-none">
                    <CardHeader className="gap-3 border-b border-border py-5">
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div className="min-w-0">
                                <CardTitle className="text-lg">
                                    Editar entrada
                                </CardTitle>
                            </div>
                            <Badge variant="secondary" className="shrink-0">
                                borrador
                            </Badge>
                        </div>
                    </CardHeader>

                    <CardContent className="py-6">
                        <div className="grid gap-6 @4xl:grid-cols-2">
                            <div className="flex min-w-0 flex-col gap-2">
                                <Label htmlFor="lab-common-name">
                                    Nombre común
                                </Label>
                                <Input
                                    id="lab-common-name"
                                    name="common_name"
                                    defaultValue="Infección por parvovirus canino"
                                    autoComplete="off"
                                    spellCheck={false}
                                />
                            </div>

                            <div className="flex min-w-0 flex-col gap-2">
                                <Label htmlFor="lab-scientific-name">
                                    Nombre científico
                                </Label>
                                <Input
                                    id="lab-scientific-name"
                                    name="scientific_name"
                                    defaultValue="Protoparvovirus carnivoran1"
                                    autoComplete="off"
                                    spellCheck={false}
                                />
                            </div>

                            <div className="flex min-w-0 flex-col gap-2">
                                <Label htmlFor="lab-slug">
                                    Identificador URL
                                </Label>
                                <Input
                                    id="lab-slug"
                                    name="slug"
                                    defaultValue="canine-parvovirus-infection"
                                    aria-invalid="true"
                                    aria-describedby="lab-slug-error"
                                    autoComplete="off"
                                    spellCheck={false}
                                />
                                <p
                                    id="lab-slug-error"
                                    className="text-base text-destructive sm:text-sm"
                                >
                                    Este identificador URL ya está asignado a
                                    otra entrada.
                                </p>
                            </div>

                            <div className="flex min-w-0 flex-col gap-2">
                                <Label htmlFor="lab-catalog-id">
                                    ID de catálogo
                                </Label>
                                <Input
                                    id="lab-catalog-id"
                                    name="catalog_id"
                                    defaultValue="VP-000421"
                                    disabled
                                />
                                <p className="text-base text-muted-foreground sm:text-sm">
                                    No puede modificarse después de crear la
                                    entrada.
                                </p>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter className="flex-col-reverse gap-2 border-t border-border py-4 sm:flex-row sm:justify-end">
                        <Button type="button" variant="ghost">
                            Cancelar
                        </Button>
                        <Button type="button" disabled={simulateLoading}>
                            {simulateLoading ? (
                                <>
                                    <Spinner
                                        data-icon="inline-start"
                                        aria-label="Guardando"
                                    />
                                    Guardando borrador…
                                </>
                            ) : (
                                <>
                                    <FloppyDiskIcon data-icon="inline-start" />
                                    Guardar borrador
                                </>
                            )}
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </LabSection>
    );
}
