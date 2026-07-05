<?php

declare(strict_types=1);

namespace App\EntryTemplates;

use App\Enums\EntryType;
use LogicException;

final readonly class EntryTypeSectionTemplates
{
    /**
     * @return list<EntrySectionTemplate>
     */
    public function for(EntryType $type): array
    {
        return $this->all()[$type->value];
    }

    /**
     * @return array<string, list<EntrySectionTemplate>>
     */
    public function all(): array
    {
        return [
            EntryType::Drug->value => $this->templates([
                ['description', 'Descripción', 'Resumen farmacológico y contexto clínico general.'],
                ['indications', 'Indicaciones', 'Usos clínicos apropiados y situaciones donde aplica.'],
                ['dosage', 'Dosis', 'Dosis, rangos, frecuencia, vía y consideraciones por especie.'],
                ['contraindications', 'Contraindicaciones', 'Casos donde no debe usarse o requiere evitarse.'],
                ['adverse_effects', 'Efectos adversos', 'Reacciones adversas esperadas, relevantes o graves.'],
                ['interactions', 'Interacciones', 'Interacciones medicamentosas o clínicas importantes.'],
                ['clinical_notes', 'Notas clínicas', 'Observaciones prácticas, advertencias de uso y contexto adicional.'],
            ]),
            EntryType::Procedure->value => $this->templates([
                ['purpose', 'Objetivo', 'Qué busca lograr el procedimiento y en qué contexto se realiza.'],
                ['indications', 'Indicaciones', 'Situaciones clínicas donde el procedimiento está indicado.'],
                ['contraindications', 'Contraindicaciones', 'Situaciones donde debe evitarse o requiere evaluación adicional.'],
                ['materials', 'Material requerido', 'Equipo, insumos y preparación material necesaria.'],
                ['preparation', 'Preparación', 'Preparación del paciente, personal, área o equipo.'],
                ['steps', 'Pasos', 'Secuencia ordenada para realizar el procedimiento.'],
                ['precautions', 'Precauciones', 'Puntos de seguridad y riesgos a prevenir durante la ejecución.'],
                ['complications', 'Complicaciones', 'Complicaciones posibles y señales de alerta.'],
            ]),
            EntryType::Maneuver->value => $this->templates([
                ['when_to_use', 'Cuándo usar', 'Situaciones donde la maniobra es apropiada.'],
                ['when_not_to_use', 'Cuándo no usar', 'Situaciones donde la maniobra debe evitarse.'],
                ['steps', 'Pasos', 'Ejecución concreta y ordenada de la maniobra.'],
                ['precautions', 'Precauciones', 'Puntos de seguridad para paciente y operador.'],
                ['common_mistakes', 'Errores comunes', 'Errores frecuentes que reducen eficacia o aumentan riesgo.'],
                ['clinical_notes', 'Notas clínicas', 'Notas prácticas, contexto y observaciones clínicas útiles.'],
            ]),
            EntryType::Protocol->value => $this->templates([
                ['objective', 'Objetivo', 'Meta clínica del protocolo.'],
                ['use_criteria', 'Criterios de uso', 'Condiciones que justifican activar el protocolo.'],
                ['initial_actions', 'Acciones iniciales', 'Primeras acciones prioritarias.'],
                ['treatment_steps', 'Pasos de tratamiento', 'Secuencia de tratamiento o intervención.'],
                ['monitoring', 'Monitoreo', 'Variables, signos o parámetros a vigilar.'],
                ['escalation_criteria', 'Criterios de escalamiento', 'Cuándo aumentar intervención, referir o pedir ayuda.'],
                ['stop_criteria', 'Criterios de suspensión', 'Cuándo detener, cambiar o cerrar el protocolo.'],
            ]),
            EntryType::Toxicity->value => $this->templates([
                ['toxic_agent', 'Agente tóxico', 'Sustancia, producto o exposición tóxica involucrada.'],
                ['toxic_threshold', 'Dosis tóxica / umbral', 'Dosis, cantidad o umbral asociado a toxicidad.'],
                ['clinical_signs', 'Signos clínicos', 'Signos esperados, progresión y datos de alarma.'],
                ['initial_actions', 'Acciones iniciales', 'Primeras medidas ante sospecha o confirmación de exposición.'],
                ['treatment', 'Tratamiento', 'Tratamiento recomendado y medidas de soporte.'],
                ['antidote', 'Antídoto', 'Antídoto específico si existe y consideraciones de uso.'],
                ['prognosis', 'Pronóstico', 'Pronóstico, factores de riesgo y evolución esperada.'],
            ]),
            EntryType::Formula->value => $this->templates([
                ['use_case', 'Uso', 'Para qué sirve el cálculo o conversión.'],
                ['variables', 'Variables', 'Variables requeridas, unidades y significado.'],
                ['formula', 'Fórmula', 'Expresión matemática o regla de cálculo.'],
                ['example', 'Ejemplo', 'Ejemplo práctico con valores y resultado.'],
                ['warnings', 'Advertencias', 'Limitaciones, supuestos y riesgos de interpretación.'],
                ['clinical_notes', 'Notas clínicas', 'Notas prácticas o contexto clínico para aplicar la fórmula.'],
            ]),
        ];
    }

    /**
     * @param  list<array{0: string, 1: string, 2: string}>  $definitions
     * @return list<EntrySectionTemplate>
     */
    private function templates(array $definitions): array
    {
        $templates = array_map(
            fn (array $definition, int $index): EntrySectionTemplate => new EntrySectionTemplate(
                key: $definition[0],
                title: $definition[1],
                sortOrder: ($index + 1) * 10,
                guidance: $definition[2],
            ),
            $definitions,
            array_keys($definitions),
        );

        $this->ensureUniqueKeys($templates);

        return $templates;
    }

    /**
     * @param  list<EntrySectionTemplate>  $templates
     */
    private function ensureUniqueKeys(array $templates): void
    {
        $keys = array_map(fn (EntrySectionTemplate $template): string => $template->key, $templates);

        throw_if(count($keys) !== count(array_unique($keys)), LogicException::class, 'Entry section template keys must be unique within an entry type.');
    }
}
