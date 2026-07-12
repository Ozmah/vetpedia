import { Head, usePage } from '@inertiajs/react';
import AppearanceToggleTab from '@/components/appearance-tabs';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { CatalogSection, FeedbackSection } from './ui-lab/catalog-scene';
import { DomainSection } from './ui-lab/domain-scene';
import { EntryEditorSection } from './ui-lab/entry-editor-scene';
import {
    FoundationsSection,
    PrimitivesSection,
} from './ui-lab/foundations-scene';
import { NavigationSection } from './ui-lab/navigation-scene';
import { OverlaysSection } from './ui-lab/overlays-scene';

export default function LocalUi() {
    const { localTools } = usePage().props;

    if (localTools.ui === null) {
        return null;
    }

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Laboratorio UI',
            href: localTools.ui,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Laboratorio UI" />

            <div className="@container flex min-w-0 flex-1 flex-col bg-background antialiased">
                <div className="flex justify-end border-b border-border px-4 py-3 sm:px-6 lg:px-8">
                    <AppearanceToggleTab aria-label="Tema de revisión" />
                </div>

                <FoundationsSection />
                <PrimitivesSection />
                <EntryEditorSection />
                <FeedbackSection />
                <CatalogSection />
                <NavigationSection />
                <OverlaysSection />
                <DomainSection />
            </div>
        </AppLayout>
    );
}
