# Vetpedia

Vetpedia is a Laravel/Inertia application for maintaining and searching structured veterinary knowledge.

## Purpose

Vetpedia stores veterinary knowledge as reviewed, structured records called **entries**.

The public search surface must only expose veterinary-approved content. Draft or documented-but-unapproved content can exist internally, but must not appear in normal public search.

## Core concepts

### Entry types

Initial entry types:

- `drug`
- `procedure`
- `maneuver`
- `protocol`
- `toxicity`
- `formula`

`condition` / disease encyclopedia-style entries are intentionally out of scope for v1.

### Entry statuses

Initial statuses:

- `draft` — incomplete or without sources
- `documented` — has at least one source, but is not public
- `vet_approved` — approved for public search
- `archived` — retired from normal use

Public production search only returns `vet_approved` entries.

Development may allow searching unapproved entries through:

```txt
VETPEDIA_ALLOW_UNAPPROVED_SEARCH=true
```

This flag must default to `false` and must not create production exposure.

## Architecture decisions

### Application stack

- Laravel 13
- PHP 8.5
- Inertia Laravel 3
- React 19
- TypeScript
- Tailwind CSS v4
- Fortify
- Wayfinder
- SQLite
- Typesense
- FrankenPHP
- Cloudflare R2 later for backups

### Runtime

The target runtime is FrankenPHP in Docker.

This avoids depending on the host PHP version and allows the app to run with PHP 8.5 even when the local WSL PHP version is older.

Planned local ports:

```txt
8000  Laravel / FrankenPHP
5173  Vite HMR
8108  Typesense
```

### Database

SQLite is the source of truth.

Typesense is a rebuildable search index, not the source of truth.

Planned SQLite optimization package:

```txt
nunomaduro/laravel-optimize-database
```

### Search

Typesense will power search-as-you-type.

Search will be queried through Laravel server-side endpoints. The Typesense admin API key must not be exposed to the browser.

### Actions

The application will follow the starter's action-oriented architecture.

Controllers should stay thin:

```txt
Routes → Controllers → Form Requests / Policies → Actions → Models
```

Business mutations should live in Action classes with a `handle()` method.

Multi-model mutations should use transactions.

Mutating domain actions should explicitly write audit events.

### Audit logging

Audit logging is non-negotiable.

The app will record important actions such as:

- entry creation and updates
- status changes
- approvals
- archiving
- source changes
- species/catalog changes
- user changes
- login and failed-login events
- search index rebuilds
- backup events

The logging design will also explore Laravel-compatible wide events / evlog-style structured events.

## Roles

Initial roles:

- `superadmin`
- `admin`
- `user`

Rules:

- `superadmin` can do everything and cannot be deleted or demoted.
- `admin` can manage entries, sources, species, catalogs, and normal users.
- `admin` can approve entries.
- `user` can create and edit entries that are not `vet_approved`.
- `user` cannot create sources, species, catalogs, or users.
- only `admin` / `superadmin` can move an entry to `vet_approved`.

Public registration is closed.

Initial seeded users:

- Gabriel: `superadmin`
- Carlos: `admin`

Dummy users will be seeded for permission testing.

## Sources

Entries may have multiple sources.

Sources apply to the whole entry by default. Section-level sources are optional for more granular citation.

Source status is intentionally omitted in v1 to reduce friction.

Admins can create sources. Users can select existing sources but cannot create new ones.

## Internal guide

The system will include an internal guide for contributors.

The guide should explain:

- what an entry is
- entry statuses
- sources
- entry types
- section templates
- examples
- common mistakes
- how to report missing sections or types

This guide is part of the product, not external marketing copy.

## Public contribution flow

`/contribute` is a later experimental feature.

It is for external contributors without system accounts.

The page will:

- require no login
- write nothing to the database
- generate a portable JSON file
- instruct the contributor to send that file to a real Vetpedia user for review

Clinical contributions are not published automatically.

## Email and 2FA

Email will use Resend.

2FA enforcement is a later phase.

Final rule: every real system user must have 2FA enabled. There should be no normal user-facing flow to disable 2FA.

The first implementation phase keeps starter authentication working without blocking domain work.

## Backups

Backups to Cloudflare R2 are a later operational phase.

SQLite backups must be consistent. The active SQLite file should not be copied directly while in use.

Planned command:

```txt
php artisan vetpedia:backup
```

## Tooling

Planned / expected tooling:

- Laravel Boost
- Laravel PAO (`laravel/pao`)
- Varlock
- Laravel Moat
- Pest
- Larastan / PHPStan
- Rector
- Pint
- Roave Security Advisories
- Composer audit

## Development status

Current first Linear epic:

```txt
VET-1 Bootstrap Laravel + FrankenPHP runtime
```

Current active first child issue:

```txt
VET-2 Add minimal FrankenPHP Caddyfile
```

## License

MIT.
