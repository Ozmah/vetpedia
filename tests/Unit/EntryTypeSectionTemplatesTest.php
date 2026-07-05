<?php

declare(strict_types=1);

use App\EntryTemplates\EntrySectionTemplate;
use App\EntryTemplates\EntryTypeSectionTemplates;
use App\Enums\EntryType;

it('defines templates for every entry type', function (): void {
    $templates = resolve(EntryTypeSectionTemplates::class)->all();

    expect(array_keys($templates))->toBe(array_map(
        fn (EntryType $type): string => $type->value,
        EntryType::cases(),
    ));
});

it('matches the canonical v1 section plan', function (EntryType $type, array $expected): void {
    $templates = resolve(EntryTypeSectionTemplates::class)->for($type);

    expect(sectionPairs($templates))->toBe($expected);
})->with([
    'drug' => [EntryType::Drug, [
        'description' => 'Descripción',
        'indications' => 'Indicaciones',
        'dosage' => 'Dosis',
        'contraindications' => 'Contraindicaciones',
        'adverse_effects' => 'Efectos adversos',
        'interactions' => 'Interacciones',
        'clinical_notes' => 'Notas clínicas',
    ]],
    'procedure' => [EntryType::Procedure, [
        'purpose' => 'Objetivo',
        'indications' => 'Indicaciones',
        'contraindications' => 'Contraindicaciones',
        'materials' => 'Material requerido',
        'preparation' => 'Preparación',
        'steps' => 'Pasos',
        'precautions' => 'Precauciones',
        'complications' => 'Complicaciones',
    ]],
    'maneuver' => [EntryType::Maneuver, [
        'when_to_use' => 'Cuándo usar',
        'when_not_to_use' => 'Cuándo no usar',
        'steps' => 'Pasos',
        'precautions' => 'Precauciones',
        'common_mistakes' => 'Errores comunes',
        'clinical_notes' => 'Notas clínicas',
    ]],
    'protocol' => [EntryType::Protocol, [
        'objective' => 'Objetivo',
        'use_criteria' => 'Criterios de uso',
        'initial_actions' => 'Acciones iniciales',
        'treatment_steps' => 'Pasos de tratamiento',
        'monitoring' => 'Monitoreo',
        'escalation_criteria' => 'Criterios de escalamiento',
        'stop_criteria' => 'Criterios de suspensión',
    ]],
    'toxicity' => [EntryType::Toxicity, [
        'toxic_agent' => 'Agente tóxico',
        'toxic_threshold' => 'Dosis tóxica / umbral',
        'clinical_signs' => 'Signos clínicos',
        'initial_actions' => 'Acciones iniciales',
        'treatment' => 'Tratamiento',
        'antidote' => 'Antídoto',
        'prognosis' => 'Pronóstico',
    ]],
    'formula' => [EntryType::Formula, [
        'use_case' => 'Uso',
        'variables' => 'Variables',
        'formula' => 'Fórmula',
        'example' => 'Ejemplo',
        'warnings' => 'Advertencias',
        'clinical_notes' => 'Notas clínicas',
    ]],
]);

it('prevents duplicate keys within each type', function (): void {
    $allTemplates = resolve(EntryTypeSectionTemplates::class)->all();

    foreach ($allTemplates as $templates) {
        $keys = array_map(fn (EntrySectionTemplate $template): string => $template->key, $templates);

        expect($keys)->toHaveCount(count(array_unique($keys)));
    }
});

it('uses predictable unique sort orders per type', function (): void {
    $allTemplates = resolve(EntryTypeSectionTemplates::class)->all();

    foreach ($allTemplates as $templates) {
        $sortOrders = array_map(fn (EntrySectionTemplate $template): int => $template->sortOrder, $templates);

        expect($sortOrders)->toBe(range(10, count($templates) * 10, 10));
    }
});

it('serializes section templates for forms and guides', function (): void {
    $template = new EntrySectionTemplate(
        key: 'dosage',
        title: 'Dosis',
        sortOrder: 30,
        guidance: 'Dosis, rangos, frecuencia, vía y consideraciones por especie.',
    );

    expect($template->toArray())->toBe([
        'key' => 'dosage',
        'title' => 'Dosis',
        'sort_order' => 30,
        'guidance' => 'Dosis, rangos, frecuencia, vía y consideraciones por especie.',
    ]);
});

/**
 * @param  list<EntrySectionTemplate>  $templates
 * @return array<string, string>
 */
function sectionPairs(array $templates): array
{
    $pairs = [];

    foreach ($templates as $template) {
        $pairs[$template->key] = $template->title;
    }

    return $pairs;
}
