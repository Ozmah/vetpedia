import { Badge } from '@/components/ui/badge';
import {
    Breadcrumb,
    BreadcrumbEllipsis,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
} from '@/components/ui/navigation-menu';
import { LabSection, Specimen } from './scaffold';

export function NavigationSection() {
    return (
        <LabSection id="navigation" title="Navegación de entrada">
            <Specimen
                title="Encabezado de entrada"
                test="recorrer el menú con Tab, flechas, Enter y Escape."
                contentClassName="p-0"
            >
                <div className="flex flex-col">
                    <div className="border-b border-border px-4 py-3 sm:px-5">
                        <Breadcrumb>
                            <BreadcrumbList>
                                <BreadcrumbItem>
                                    <BreadcrumbLink href="#navigation">
                                        Vetpedia
                                    </BreadcrumbLink>
                                </BreadcrumbItem>
                                <BreadcrumbSeparator />
                                <BreadcrumbItem>
                                    <BreadcrumbEllipsis label="Más" />
                                </BreadcrumbItem>
                                <BreadcrumbSeparator />
                                <BreadcrumbItem>
                                    <BreadcrumbLink href="#data-display">
                                        Catálogo de enfermedades
                                    </BreadcrumbLink>
                                </BreadcrumbItem>
                                <BreadcrumbSeparator />
                                <BreadcrumbItem>
                                    <BreadcrumbPage>
                                        Infección por parvovirus canino
                                    </BreadcrumbPage>
                                </BreadcrumbItem>
                            </BreadcrumbList>
                        </Breadcrumb>
                    </div>

                    <div className="flex flex-col gap-3 px-4 py-4 sm:px-5 lg:flex-row lg:items-center lg:justify-between">
                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                                <h3 className="truncate text-lg font-semibold">
                                    Infección por parvovirus canino
                                </h3>
                                <Badge variant="secondary">borrador</Badge>
                            </div>
                            <p className="mt-1 text-base text-muted-foreground sm:text-sm">
                                Entrada VP-00421 · editada hace 12 minutos.
                            </p>
                        </div>

                        <div className="max-w-full overflow-x-auto">
                            <NavigationMenu>
                                <NavigationMenuList>
                                    <NavigationMenuItem>
                                        <NavigationMenuLink
                                            href="#navigation"
                                            active
                                        >
                                            Resumen
                                        </NavigationMenuLink>
                                    </NavigationMenuItem>
                                    <NavigationMenuItem>
                                        <NavigationMenuLink href="#forms">
                                            Editor
                                        </NavigationMenuLink>
                                    </NavigationMenuItem>
                                    <NavigationMenuItem>
                                        <NavigationMenuTrigger>
                                            Catálogos
                                        </NavigationMenuTrigger>
                                        <NavigationMenuContent className="w-72 p-2">
                                            <div className="grid gap-1">
                                                <NavigationMenuLink
                                                    href="#data-display"
                                                    closeOnClick
                                                >
                                                    <div className="font-medium">
                                                        Catálogo de enfermedades
                                                    </div>
                                                    <p className="text-muted-foreground">
                                                        Afecciones, síndromes y
                                                        presentaciones clínicas.
                                                    </p>
                                                </NavigationMenuLink>
                                                <NavigationMenuLink
                                                    href="#domain"
                                                    closeOnClick
                                                >
                                                    <div className="font-medium">
                                                        Taxonomía de especies
                                                    </div>
                                                    <p className="text-muted-foreground">
                                                        Nombres aceptados, alias
                                                        y clasificaciones.
                                                    </p>
                                                </NavigationMenuLink>
                                            </div>
                                        </NavigationMenuContent>
                                    </NavigationMenuItem>
                                </NavigationMenuList>
                            </NavigationMenu>
                        </div>
                    </div>
                </div>
            </Specimen>
        </LabSection>
    );
}
