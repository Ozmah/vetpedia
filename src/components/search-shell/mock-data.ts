import {
	FileTextIcon,
	FlaskConicalIcon,
	Repeat2Icon,
	SyringeIcon,
} from "lucide-react";
import type { SearchResult } from "./types";

export const MOCK_RESULTS: SearchResult[] = [
	{
		id: "meloxicam-dosage",
		title: "Meloxicam",
		category: "FÁRMACO",
		section: "Dosis y administración",
		excerpt:
			"Dosis recomendadas de meloxicam en gatos y perros. Vías de administración, duración del tratamiento y ajustes en pacientes especiales.",
		source: "Fuente: monografía farmacológica veterinaria",
		sourceMeta: "ramsey-es-abbyy-ocr · lines 761–779 · page 35",
		unitId: "ramsey-es-abbyy-ocr:meloxicam-dosage:761:77be16d7519b",
		year: "2026",
		status: "vet_approved",
		tags: ["Gatos", "Perros"],
		icon: FileTextIcon,
		type: "drug",
		detail: {
			title: "Meloxicam",
			description: "Antiinflamatorio · Analgésico · Antipirético",
			badges: ["Gatos", "Perros", "AINE"],
			dosage: [
				{
					species: "Gatos",
					items: [
						"Dosis inicial: 0,1 mg/kg el primer día.",
						"Dosis de mantenimiento: 0,05 mg/kg cada 24 horas.",
						"Usar la menor dosis eficaz durante el menor tiempo posible.",
					],
				},
				{
					species: "Perros",
					items: [
						"Dosis inicial: 0,2 mg/kg el primer día.",
						"Dosis de mantenimiento: 0,1 mg/kg cada 24 horas.",
						"En dolor agudo, puede repetirse la dosis inicial una sola vez a las 24 h.",
					],
				},
			],
			notes: [
				"No usar en gatos deshidratados o con enfermedad renal, hepática o cardíaca.",
				"Evitar el uso concomitante con otros AINEs o corticosteroides.",
			],
			warning:
				"La información mostrada es un ejemplo visual. La app debe mostrar el estado real de revisión de cada unidad.",
		},
	},
	{
		id: "oxicams-class",
		title: "AINEs: Oxicams",
		category: "CLASE TERAPÉUTICA",
		section: "Antiinflamatorios no esteroideos",
		excerpt:
			"Propiedades farmacológicas de los oxicams, incluyendo meloxicam. Indicaciones, seguridad y efectos adversos.",
		source: "Fuente: índice terapéutico",
		sourceMeta: "vetpedia-curated · field therapeutic_class · parsed",
		unitId: "vetpedia-curated:aines-oxicams:class:2026",
		year: "2026",
		status: "parsed",
		tags: ["Gatos", "Perros", "Equinos"],
		icon: FlaskConicalIcon,
		type: "class",
		detail: {
			title: "AINEs: Oxicams",
			description:
				"Clase terapéutica relacionada con antiinflamatorios no esteroideos.",
			badges: ["Clase", "AINE", "Parsed"],
			dosage: [],
			notes: [
				"Las clases terapéuticas ayudan a agrupar medicamentos relacionados.",
				"El detalle clínico debe resolverse contra la monografía específica.",
			],
		},
	},
	{
		id: "meloxicam-corticosteroids",
		title: "Meloxicam + Corticosteroides",
		category: "INTERACCIÓN",
		section: "Interacciones",
		excerpt:
			"El uso concomitante de meloxicam con corticosteroides puede aumentar el riesgo de ulceración gastrointestinal y nefrotoxicidad.",
		source: "Fuente: sección de interacciones",
		sourceMeta: "review-queue · field interactions · needs_review",
		unitId: "review-queue:meloxicam-corticosteroids:interaction",
		year: "2026",
		status: "needs_review",
		tags: ["Gatos", "Perros"],
		icon: Repeat2Icon,
		type: "interaction",
		detail: {
			title: "Meloxicam + Corticosteroides",
			description:
				"Interacción potencialmente relevante para seguridad gastrointestinal y renal.",
			badges: ["Interacción", "Requiere revisión"],
			dosage: [],
			notes: [
				"Evitar combinaciones sin criterio veterinario y evaluación del paciente.",
			],
			warning:
				"Esta unidad requiere revisión antes de presentarse como información confiable.",
		},
	},
	{
		id: "meloxicam-routes",
		title: "Meloxicam: vías y consideraciones",
		category: "DOSIS Y ADMINISTRACIÓN",
		section: "Dosis en animales de compañía",
		excerpt:
			"Farmacocinética de meloxicam en perros y gatos. Biodisponibilidad, formulaciones y administración.",
		source: "Fuente: sección de dosis",
		sourceMeta: "soft-approved · field dosage · companion_animals",
		unitId: "soft-approved:meloxicam-routes:dosage",
		year: "2026",
		status: "soft_approved",
		tags: ["Gatos", "Perros"],
		icon: SyringeIcon,
		type: "dosage",
		detail: {
			title: "Meloxicam: vías y consideraciones",
			description:
				"Resumen de vías de administración y consideraciones de formulación.",
			badges: ["Dosis", "Soft approved"],
			dosage: [
				{
					species: "Animales de compañía",
					items: [
						"Confirmar especie, peso y formulación antes de dosificar.",
						"Evitar extrapolar dosis entre presentaciones sin validación.",
					],
				},
			],
			notes: ["La app debe enlazar siempre a fuente y estado de revisión."],
		},
	},
];
