import { DotsThreeIcon } from '@phosphor-icons/react/DotsThree';
import { EyeIcon } from '@phosphor-icons/react/Eye';
import { FunnelIcon } from '@phosphor-icons/react/Funnel';
import { TrashIcon } from '@phosphor-icons/react/Trash';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Label } from '@/components/ui/label';
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { LabSection, Specimen } from './scaffold';

export function OverlaysSection() {
    return (
        <LabSection id="overlays" title="Menús y diálogos">
            <Specimen
                title="Acciones de la entrada"
                test="cerrar cada capa con Escape y verificar la restauración del foco."
            >
                <div className="flex flex-wrap items-center gap-2">
                    <Tooltip>
                        <TooltipTrigger
                            render={
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    aria-label="Vista previa de la entrada"
                                />
                            }
                        >
                            <EyeIcon />
                        </TooltipTrigger>
                        <TooltipContent>
                            Vista previa de la entrada
                        </TooltipContent>
                    </Tooltip>

                    <DropdownMenu>
                        <DropdownMenuTrigger
                            render={<Button type="button" variant="outline" />}
                        >
                            <DotsThreeIcon data-icon="inline-start" />
                            Más acciones
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" className="min-w-64">
                            <DropdownMenuGroup>
                                <DropdownMenuLabel>Entrada</DropdownMenuLabel>
                                <DropdownMenuItem>
                                    Editar entrada
                                </DropdownMenuItem>
                                <DropdownMenuItem>
                                    Marcar como documentada
                                </DropdownMenuItem>
                            </DropdownMenuGroup>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem>
                                Archivar entrada
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <Sheet>
                        <SheetTrigger
                            render={
                                <Button type="button" variant="secondary" />
                            }
                        >
                            <FunnelIcon data-icon="inline-start" />
                            Filtros
                        </SheetTrigger>
                        <SheetContent side="right" closeLabel="Cerrar">
                            <SheetHeader>
                                <SheetTitle>Filtros del catálogo</SheetTitle>
                                <SheetDescription>
                                    Selecciona los estados que deseas mostrar en
                                    la cola editorial.
                                </SheetDescription>
                            </SheetHeader>
                            <div className="flex flex-1 flex-col gap-5 overflow-y-auto px-4 py-2">
                                <FilterOption
                                    id="lab-filter-draft"
                                    label="Entradas en borrador"
                                    description="Incluir registros pendientes de documentación."
                                    defaultChecked
                                />
                                <FilterOption
                                    id="lab-filter-documented"
                                    label="Entradas documentadas"
                                    description="Incluir registros revisados y publicados."
                                    defaultChecked
                                />
                                <FilterOption
                                    id="lab-filter-archived"
                                    label="Entradas archivadas"
                                    description="Incluir registros retirados de la búsqueda activa."
                                />
                            </div>
                            <SheetFooter>
                                <SheetClose render={<Button type="button" />}>
                                    Aplicar filtros
                                </SheetClose>
                                <SheetClose
                                    render={
                                        <Button type="button" variant="ghost" />
                                    }
                                >
                                    Cancelar
                                </SheetClose>
                            </SheetFooter>
                        </SheetContent>
                    </Sheet>

                    <Dialog>
                        <DialogTrigger
                            render={
                                <Button type="button" variant="destructive" />
                            }
                        >
                            <TrashIcon data-icon="inline-start" />
                            Eliminar entrada
                        </DialogTrigger>
                        <DialogContent closeLabel="Cerrar">
                            <DialogHeader>
                                <DialogTitle>
                                    ¿Eliminar esta entrada?
                                </DialogTitle>
                                <DialogDescription>
                                    Se eliminará “Infección por parvovirus
                                    canino”. Esta acción no se puede deshacer.
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter>
                                <DialogClose
                                    render={
                                        <Button
                                            type="button"
                                            variant="outline"
                                        />
                                    }
                                >
                                    Cancelar
                                </DialogClose>
                                <DialogClose
                                    render={
                                        <Button
                                            type="button"
                                            variant="destructive"
                                        />
                                    }
                                >
                                    Eliminar entrada
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
            </Specimen>
        </LabSection>
    );
}

function FilterOption({
    id,
    label,
    description,
    defaultChecked = false,
}: {
    id: string;
    label: string;
    description: string;
    defaultChecked?: boolean;
}) {
    return (
        <div className="flex items-start gap-3">
            <Checkbox id={id} name={id} defaultChecked={defaultChecked} />
            <div className="min-w-0">
                <Label htmlFor={id} className="cursor-pointer">
                    {label}
                </Label>
                <p className="mt-1 text-base text-muted-foreground sm:text-sm">
                    {description}
                </p>
            </div>
        </div>
    );
}
