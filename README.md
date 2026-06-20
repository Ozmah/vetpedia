# Vetpedia

Vetpedia is a Laravel/Inertia application for maintaining and searching structured veterinary knowledge.

The public search surface must only expose veterinary-approved content. Draft or documented-but-unapproved content may exist internally, but must not appear in normal public search.

## Stack

- Laravel 13 / PHP 8.5
- Inertia Laravel 3 / React 19 / TypeScript
- Tailwind CSS v4
- Fortify
- Wayfinder
- SQLite as source of truth
- Typesense as rebuildable search index
- FrankenPHP in Docker
- Varlock + Infisical for environment and secrets

## Prerequisites

- Linux or WSL2 is the primary local workflow.
- Docker for the active Ubuntu distro.
- Bun installed.
- Access to the Vetpedia Infisical project if using the recommended setup.


Verify Docker is visible:

```bash
docker version
```

For Windows users, if Docker is not available, enable it in Docker Desktop:

## Environment setup

Vetpedia uses `.env.schema` as the committed environment contract. Local secrets live in `.env`, which must remain uncommitted.

### Recommended: Varlock + Infisical

Create or request an Infisical Machine Identity with read access to the Vetpedia project and the `local` environment.

Local `.env` should include the Infisical bootstrap values:

```env
INFISICAL_ENV=local
INFISICAL_CLIENT_ID=...
INFISICAL_CLIENT_SECRET=varlock("local:...")
INFISICAL_PROJECT_ID=varlock("local:...")
APP_NAME=Vetpedia
APP_ENV=local
DB_FILE_PATH=/app-data/vetpedia.sqlite
```

Use Varlock to encrypt local sensitive values:

```bash
bunx varlock encrypt --file .env
```

Validate environment resolution:

```bash
bun run env:check
```

Scan for accidentally committed plaintext secrets:

```bash
bun run secrets:scan
```

### Required Infisical secrets

Root path `/`:

- `APP_KEY`
- production-only values such as `APP_URL`, `DB_DATABASE`, and `TYPESENSE_API_KEY` when needed

Path `/seed-users`:

- `VETPEDIA_SUPERADMIN_NAME`
- `VETPEDIA_SUPERADMIN_EMAIL`
- `VETPEDIA_SUPERADMIN_PASSWORD`
- `VETPEDIA_ADMIN_NAME`
- `VETPEDIA_ADMIN_EMAIL`
- `VETPEDIA_ADMIN_PASSWORD`
- `VETPEDIA_DUMMY_USER_PASSWORD`

When reading Infisical secrets from a path, use the explicit default instance form:

```env
VETPEDIA_SUPERADMIN_NAME=infisical(_default, "VETPEDIA_SUPERADMIN_NAME", "/your-infisical-folder")
```

Do not use project names, emails, or repo words as passwords. Secret scanners match resolved sensitive values against repository files, so passwords must be random and unique.

### Manual local secrets

If you do not want to use Infisical locally, use direct local secrets only in `.env` and do not commit them. You will need to remove or bypass Infisical resolvers in your local schema/override workflow.

## Running locally

Start Docker through Varlock so Docker Compose receives decrypted values:

```bash
varlock run -- docker compose up -d --force-recreate
```

Use `--build` when rebuilding images:

```bash
varlock run -- docker compose up -d --build --force-recreate
```

Do not start this project with plain `docker compose up` when `.env` contains `varlock("local:...")` values. Compose would pass the literal encrypted resolver strings into the container, causing Infisical authentication failures.

Local ports:

```txt
8000  Laravel / FrankenPHP
5173  Vite HMR
8108  Typesense
```

## Common commands

```bash
bun run env:check      # validate resolved env
bun run secrets:scan   # scan for plaintext secret leaks
bun check              # autofix/format/lint through Docker when needed
bun run shodan         # application tests
bun run glados         # full pre-push gate
```

Useful direct container checks:

```bash
docker compose exec app printenv INFISICAL_CLIENT_ID
docker compose exec app bunx varlock load
docker compose exec -T app composer test:app
docker compose exec -T -u "$(id -u):$(id -g)" app composer test:types
```

## Seed users

Public registration is closed. Initial users are created by the database seeder.

Seeded real users:

- configured superadmin from `VETPEDIA_SUPERADMIN_*`
- configured admin from `VETPEDIA_ADMIN_*`

Local/development dummy users:

- 2 dummy admins
- 10 dummy users

Dummy users are not seeded in production.

Seeders are idempotent and use email as the stable identity. Re-running the seeder updates existing seeded users instead of creating duplicates.

## Domain concepts

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

Production public search only returns `vet_approved` entries.

### Roles

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

## Architecture notes

Controllers should stay thin:

```txt
Routes → Controllers → Form Requests / Policies → Actions → Models
```

Business mutations should live in Action classes with a `handle()` method.

Multi-model mutations should use transactions.

Authorization-sensitive mutations must go through dedicated actions and policies/gates.

Audit logging is planned for important actions such as entry changes, approvals, user changes, login events, search index rebuilds, and backup events.

## Search

Typesense powers search-as-you-type. SQLite remains the source of truth.

The Typesense admin API key must not be exposed to the browser. Search should be queried through Laravel server-side endpoints.

## Troubleshooting

### Docker is not available in WSL

Enable Docker Desktop WSL Integration for the active Ubuntu distro, then reopen the WSL terminal.

### Infisical returns 403

The Machine Identity lacks access to the project, environment, or secret path. Grant read access to the correct Infisical project, `INFISICAL_ENV`, `/`, and `/seed-users` as needed.

### Infisical invalid credentials inside Docker

Start Compose through Varlock:

```bash
varlock run -- docker compose up -d --force-recreate
```

This ensures Compose receives decrypted Infisical credentials instead of literal `varlock("local:...")` strings.

### Full browser tests fail because Playwright is missing

The app test command runs Unit and Feature tests. If you run the complete suite including Browser tests, install Playwright first.

## License

MIT.
