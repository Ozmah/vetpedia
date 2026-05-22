# Vetpedia

Vetpedia is an application for managing veterinary pharmacology data as a searchable, reviewable, and indexable data source.

The technical goal of the project is to manage veterinary pharmacology data with traceability, review states, and fast search. It is not a PDF repository and it is not a substitute for veterinary judgment.

## License

MIT.

## Technical scope

Vetpedia is designed as a data management system for building and maintaining a veterinary data source.

The system should support:

- preserving source traceability;
- storing units in SQLite;
- generating search-optimized documents;
- indexing those documents in Typesense;
- reviewing, correcting, and approving information progressively;
- separating public search data from privileged review data.

## Conceptual model

The main unit in the system is a pharmacological monograph.

```txt
1 unit ~= 1 drug / monograph
```

Example units:

- Acepromazine
- Meloxicam
- Cephalexin
- Acetazolamide

There are also cross-reference units:

```txt
Mineral oil -> Paraffin
Acetaminophen -> Paracetamol
```

Each unit can contain clinical sections:

- formulations;
- action;
- use;
- indications;
- safety and handling;
- contraindications;
- adverse reactions;
- interactions;
- dosage;
- references.

Sections generate smaller search documents, while still pointing back to their original unit.

```txt
unit
  -> sections
  -> search_documents
```

## Review states

Data is not considered clinically validated just because it was extracted.

Defined states:

```txt
raw            raw or imported data that has not been processed
parsed         automatically extracted and structurally coherent
needs_review   requires human review
soft_approved  human-reviewed, not veterinary-validated
vet_approved   validated by a veterinarian
rejected        should not be used
```

## Sources, traceability, and auditability

Every unit should preserve source information:

- source file;
- starting line;
- ending line;
- approximate page when available;
- raw text;
- normalized text;
- quality issues;
- review state.

This makes it possible to audit where each data point came from and correct errors without losing context.

## Data architecture

SQLite is the operational source of truth.

Typesense is a rebuildable search index.

```txt
admin-only data entry / curation
  -> SQLite
  -> search_documents
  -> Typesense
  -> search/review UI
```

If the Typesense index is lost, it should be rebuildable from SQLite.

## Database

The operational database is SQLite.

In production, you should mount a persistent volume for the database.

Expected variable:

```txt
DATABASE_PATH=/data/vetpedia.sqlite
```

Agreed data access tooling:

```txt
drizzle-orm + better-sqlite3 + drizzle-kit
```

## Search

Typesense is used as the search engine.

The browser must never communicate with a Typesense administrative key directly.

Expected MVP flow:

```txt
browser
  -> TanStack Start server-side route/API
  -> Typesense
```

Expected variables:

```txt
TYPESENSE_HOST=
TYPESENSE_PORT=
TYPESENSE_PROTOCOL=
TYPESENSE_ADMIN_API_KEY=
TYPESENSE_COLLECTION=vetpedia_search_documents
```

The index should prioritize results by review state:

```txt
vet_approved > soft_approved > parsed > needs_review
```

## Public and privileged access

Public search should act as a discovery and limited consultation mechanism.

Privileged access should be reserved for:

- complete units;
- raw text;
- problematic OCR;
- review tools;
- corrections;
- exports;
- index administration.

This keeps the public experience separate from the internal data-curation workflow.

## Agreed stack

```txt
React
TanStack Start
TanStack Query
SQLite
Drizzle ORM
Typesense
Tailwind CSS v4
Coss UI / Base UI for specific components
Biome
Vitest
```

## Expected file structure

```txt
src/
  routes/
    __root.tsx
    index.tsx

  components/
    brand/
      logo.tsx
      wordmark.tsx

    search-shell/
      search-shell.tsx
      search-input.tsx
      search-filter-bar.tsx
      search-result-list.tsx
      search-preview-panel.tsx
      types.ts

    ui/
      badge.tsx
      button.tsx
      card.tsx
      input.tsx

  server/
    db/
      client.ts
      schema.ts
      migrate.ts
      migrations/
      queries/
        review.ts
        search-documents.ts
        units.ts

    search/
      collections.ts
      index-documents.ts
      search.ts
      types.ts
      typesense-client.ts

  lib/
    cn.ts
    env.ts

  styles.css
```

Organization rules:

- `src/routes` composes pages.
- `src/server` contains server-only code.
- `src/server` must not be imported from client-side components.
- large components live in their own folders.
- large components should be split before becoming monolithic.
- avoid oversized files.

## Visual identity notes

The interface uses Tailwind CSS v4 with CSS tokens in OKLCH.

Base palette:

```txt
Almond Roca   #F0E8E0  oklch(0.935 0.014 67.7)
Swiss Coffee  #D5C3AD  oklch(0.826 0.036 72.8)
Coastal Plain #9FA694  oklch(0.714 0.027 124.4)
Hinterland    #616C51  oklch(0.514 0.043 125.9)
Golden Gun    #DDDD00  oklch(0.869 0.189 109.8)
Joyful Ruby   #503136  oklch(0.352 0.046 9.1)
```

Expected usage:

- `Hinterland`: primary action and selection.
- `Joyful Ruby`: strong text, brand emphasis, controlled critical states.
- `Almond Roca`: light base background.
- `Swiss Coffee`: warm borders and surfaces.
- `Coastal Plain`: neutral states and secondary accents.
- `Golden Gun`: search highlight only; not a primary color.

Fonts:

```txt
Headings: Zen Kaku Gothic Antique
Body:     Zen Kaku Gothic New
```

Logo:

```txt
public/vetpedia-logo.svg
```

## Scripts

```bash
bun run dev
bun run build
bun run check
bun run check-types
bun run glados
bun run test
```

`glados` runs the full validation chain:

```bash
bun run check && bun run build && bun run check-types
```

## Clinical note

Vetpedia organizes veterinary pharmacology information. Information may come from OCR, translation, human review, or veterinary validation.

The system must clearly expose the state of each piece of data. The application does not replace diagnosis, prescription, or professional veterinary judgment.
