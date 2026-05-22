# From PDF to NDJSON

This document describes the one-time extraction process used to create the initial Vetpedia dataset from PDF sources.

This is historical documentation. It is not the normal runtime data-entry flow for the application.

## Source files

The starting point was a local working directory containing veterinary pharmacology books in PDF format and text files extracted from those PDFs with ABBYY OCR.

The useful inputs were the ABBYY-generated `.txt` files:

```txt
vademecum-bsava-pe.txt
vademecum-farmacologico-para-perros-y-gatos-ian-ramsey.txt
```

Older generated Markdown files and previous pipeline outputs were intentionally discarded to avoid contaminating the process with incorrect assumptions.

## Initial assessment

The first review showed that the documents were pharmacological formularies organized around drug monographs.

The correct unit of extraction was therefore not a blind text chunk, but a semantic unit:

```txt
1 unit ~= 1 drug monograph
```

Each monograph could contain sections such as:

- formulations / presentation;
- action;
- use / indications;
- safety and handling;
- contraindications;
- adverse reactions;
- interactions;
- dosage;
- references.

The English source had clearer structure. The Spanish OCR source contained substantial OCR damage, including missing titles, malformed headings, broken words, page noise, and section drift.

## Data quality policy

The process was designed around conservative extraction.

Rules:

- do not invent drug names;
- do not silently trust damaged OCR;
- preserve raw text;
- preserve source file and line spans;
- mark suspicious units as `needs_review`;
- treat `parsed` as automatic extraction, not clinical validation;
- keep English material out of the Spanish search index until translated/reviewed.

Review states used by the generated dataset:

```txt
raw
parsed
needs_review
soft_approved
vet_approved
rejected
```

Important semantic rule:

```txt
parsed != clinically approved
```

## Pipeline design

The generated pipeline read only the ABBYY `.txt` files.

It produced per-run artifacts under:

```txt
data/runs/<run-id>/
```

Artifacts:

```txt
manifest.json
units.ndjson
search-documents.ndjson
review-queue.ndjson
agent-inputs.ndjson
parser-report.md
```

## Source segmentation

The extraction code configured useful body ranges for each source.

The English source was segmented by detecting monograph titles followed by category/formulation patterns.

The Spanish OCR source was segmented primarily by detecting `Presentacion:` / `Presentación:` lines, then looking backward for a plausible title candidate. When no reliable title existed, the unit was given a fallback title such as:

```txt
Monografía OCR sin título línea 780
```

Those fallback units were marked as `needs_review`.

Cross-reference lines were also extracted as units, for example:

```txt
Aceite mineral vease Parafina
Acetaminophen see Paracetamol
```

## Unit format

Each unit preserved:

- schema version;
- stable ID;
- kind (`drug_monograph` or `cross_reference`);
- title;
- trusted/untrusted title flag;
- source language;
- content language;
- source file;
- starting line;
- ending line;
- approximate page when detected;
- raw text;
- normalized text;
- canonical Spanish text when available;
- status;
- quality issues;
- drug metadata;
- sections;
- run ID.

## Search documents

Search documents were derived from units.

The goal was to create smaller indexable records while preserving the relationship to the parent unit.

Examples of search document fields:

```txt
title
aliases
presentations
action
use
indications
safetyHandling
contraindications
adverseReactions
interactions
dosage
references
body
```

Only Spanish content entered `search-documents.ndjson`.

English units were not indexed as canonical public-search content. They were sent to `agent-inputs.ndjson` for future translation and review.

## Review queue

`review-queue.ndjson` was generated from units with quality issues.

Common issue codes:

```txt
translation_required
untrusted_title
missing_dosage_section
needs_human_review
```

Each review item included:

- unit ID;
- title;
- priority;
- reasons;
- source file;
- line range;
- page guess when available;
- preview text.

## Agent inputs

`agent-inputs.ndjson` was prepared for controlled review by LLM agents.

Agents were intended to receive small, bounded units instead of being asked to freely read long source files.

Agent rules:

- output JSON only;
- write clinical content in Spanish;
- do not invent unsupported data;
- preserve evidence quotes and approximate line references;
- never assign `vet_approved`;
- keep doubtful OCR as `needs_review`.

Agents were not intended to write directly to the canonical dataset. They would produce proposals to be validated and merged later.

## Final run produced during this process

The final clean run produced:

```txt
Units: 848
Search documents: 3231
Needs review: 718
Parsed: 130
English units: 481
Spanish units: 367
Warnings: 0
```

The high number of `needs_review` units was expected because the Spanish OCR source had many missing or unreliable titles.

## Architectural outcome

The extraction confirmed that the correct long-term data model for Vetpedia is:

```txt
unit = drug monograph or cross-reference
section = clinical subdivision inside a unit
search_document = indexable projection of a unit or section
review_queue = derived work queue, not source of truth
```

The runtime application will use SQLite as the operational source of truth and Typesense as a rebuildable search index.

The NDJSON artifacts from this process are portable historical artifacts and can be used to seed the initial database, but NDJSON is not intended to be the daily operational database.
