import { ArrowSquareOutIcon } from '@phosphor-icons/react/ArrowSquareOut';
import { BookOpenTextIcon } from '@phosphor-icons/react/BookOpenText';
import { PawPrintIcon } from '@phosphor-icons/react/PawPrint';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { LabSection } from './scaffold';

const aliases = [
    'Enteritis parvoviral canina',
    'Infección por CPV',
    'Gastroenteritis hemorrágica canina asociada con CPV-2',
];

const sources = [
    {
        title: 'Enfermedades infecciosas caninas y felinas',
        detail: 'Segunda edición · Capítulo 14',
    },
    {
        title: 'Guías clínicas para animales de compañía',
        detail: 'Edición 2026 · Capítulo 8',
    },
];

export function DomainSection() {
    return (
        <LabSection id="domain" title="Detalle de entrada">
            <div className="grid gap-4 @5xl:grid-cols-[minmax(0,3fr)_minmax(18rem,2fr)]">
                <Card className="shadow-none">
                    <CardHeader className="gap-4">
                        <div className="flex items-start gap-3">
                            <div className="flex size-10 shrink-0 items-center justify-center bg-primary text-primary-foreground">
                                <PawPrintIcon
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            </div>
                            <div className="min-w-0 flex-1">
                                <div className="flex flex-wrap items-center gap-2">
                                    <Badge>Enfermedad</Badge>
                                    <Badge variant="secondary">borrador</Badge>
                                    <Badge variant="outline">VP-00421</Badge>
                                </div>
                                <CardTitle className="mt-3 text-2xl tracking-tight text-balance">
                                    Infección por parvovirus canino
                                </CardTitle>
                                <CardDescription className="mt-1 text-base sm:text-sm">
                                    Protoparvovirus carnivoran1 · Canidae
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-6">
                        <Separator />

                        <section aria-labelledby="lab-overview-heading">
                            <h3
                                id="lab-overview-heading"
                                className="font-medium"
                            >
                                Resumen
                            </h3>
                            <p className="mt-2 max-w-[72ch] text-base text-pretty text-muted-foreground sm:text-sm">
                                Enfermedad viral altamente contagiosa que afecta
                                a perros domésticos, caracterizada por
                                enfermedad gastrointestinal aguda, leucopenia,
                                deshidratación y complicaciones sistémicas
                                potencialmente mortales.
                            </p>
                        </section>

                        <section aria-labelledby="lab-signs-heading">
                            <h3 id="lab-signs-heading" className="font-medium">
                                Signos clínicos frecuentes
                            </h3>
                            <ul
                                className="mt-3 grid gap-2 text-base text-muted-foreground sm:grid-cols-2 sm:text-sm"
                                role="list"
                            >
                                <li className="border-l-2 border-border pl-3">
                                    Vómito agudo y anorexia
                                </li>
                                <li className="border-l-2 border-border pl-3">
                                    Diarrea hemorrágica
                                </li>
                                <li className="border-l-2 border-border pl-3">
                                    Letargo y deshidratación
                                </li>
                                <li className="border-l-2 border-border pl-3">
                                    Leucopenia en hematología
                                </li>
                            </ul>
                        </section>
                    </CardContent>
                </Card>

                <div className="flex min-w-0 flex-col gap-4">
                    <Card className="shadow-none">
                        <CardHeader>
                            <CardTitle>Alias</CardTitle>
                            <CardDescription>
                                Nombres asociados que pueden utilizarse en
                                búsquedas.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ul
                                className="flex flex-col divide-y divide-border"
                                role="list"
                            >
                                {aliases.map((alias) => (
                                    <li
                                        key={alias}
                                        className="py-3 first:pt-0 last:pb-0"
                                    >
                                        <p className="text-base text-pretty sm:text-sm">
                                            {alias}
                                        </p>
                                    </li>
                                ))}
                            </ul>
                        </CardContent>
                    </Card>

                    <Card className="shadow-none">
                        <CardHeader>
                            <div className="flex items-center gap-2">
                                <BookOpenTextIcon
                                    className="size-4 shrink-0 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <CardTitle>Fuentes</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            {sources.map((source) => (
                                <div key={source.title} className="min-w-0">
                                    <p className="font-medium text-pretty">
                                        {source.title}
                                    </p>
                                    <p className="mt-1 text-base text-pretty text-muted-foreground sm:text-sm">
                                        {source.detail}
                                    </p>
                                </div>
                            ))}
                            <Button type="button" variant="outline" size="sm">
                                <ArrowSquareOutIcon data-icon="inline-start" />
                                Revisar lista de fuentes
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </LabSection>
    );
}
