# Model Field Mutation Matrices

This document defines the approved mutation paths for Eloquent model fields.

The goal is to make Laravel's model magic explicit: every sensitive field should have a clear owner, write path, and enforcement rule. Code must follow these matrices.

## Rules

- Do not use `$request->all()` for model mutation.
- Do not put authorization-sensitive fields in generic update flows.
- Do not rely on frontend controls for authorization.
- Do not use `forceFill()` for convenience.
- Do not mutate `deleted_at` manually; use Eloquent soft delete APIs.
- Framework-owned authentication fields should only be changed by their dedicated framework or application flow.
- Privileged mutations need a dedicated Action or an explicitly documented framework path.
- Privileged mutations need negative tests.

## User

| Field | Classification | Who may change it | Mutation path | Mass assignment | Required rules |
|---|---|---|---|---|---|
| `id` | system-managed | Laravel | `HasUuids` / model creation | no | Never manually mutate. |
| `name` | user-editable | Owner | `UpdateUser` | yes, if validated | Validate through request/action boundary. |
| `email` | auth-sensitive | Owner | `UpdateUser` | yes, if validated | Reset `email_verified_at`; send verification notification. |
| `email_verified_at` | auth-sensitive | Laravel email verification flow | Verification controller/action | no | Signed verification URL. |
| `password` | auth-sensitive | Owner or password reset flow | `UpdateUserPassword` / `CreateUserPassword` | no | Require current password or reset token; rely on hashed cast. |
| `role` | authorization-sensitive | Admin or superadmin | `UpdateUserRole` | no | Only change non-superadmin users; audit log pending. |
| `remember_token` | auth-sensitive | Laravel auth | Remember-me flow | framework only | Hidden from serialization. |
| `two_factor_secret` | auth-sensitive | Fortify | Fortify 2FA flow | framework only | Hidden from serialization. |
| `two_factor_recovery_codes` | auth-sensitive | Fortify | Fortify 2FA flow | framework only | Hidden from serialization. |
| `two_factor_confirmed_at` | auth-sensitive | Fortify | Fortify 2FA flow | framework only | Hidden from serialization. |
| `suspended_at` | authorization-sensitive | Admin management flow | Dedicated suspend/unsuspend action | no | Block login and active sessions. |
| `deleted_at` | system-managed | `DeleteUser` | SoftDeletes via `$user->delete()` | no | Prevent superadmin deletion. |
| `created_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |
| `updated_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |

## AuditEvent

| Field | Classification | Who may change it | Mutation path | Mass assignment | Required rules |
|---|---|---|---|---|---|
| `id` | system-managed | Laravel | `HasUuids` / model creation | no | Never manually mutate. |
| `actor_id` | audit/compliance | System | `LogAuditEvent` action required | yes, through action only | Nullable for anonymous/system events; reference users when available. |
| `action` | audit/compliance | System | `LogAuditEvent` action required | yes, through action only | Required stable event name such as `entry.created` or `auth.failed_login`. |
| `subject_type` | audit/compliance | System | `LogAuditEvent` action required | yes, through action only | Optional model class or operational category such as `auth`, `search`, or `backup`. |
| `subject_id` | audit/compliance | System | `LogAuditEvent` action required | yes, through action only | Optional UUID of the affected subject when one exists. |
| `summary` | audit/compliance | System | `LogAuditEvent` action required | yes, through action only | Required human-readable summary; do not include secrets, tokens, passwords, or unnecessary clinical details. |
| `before` | audit/compliance | System | `LogAuditEvent` action required | yes, through action only | Optional structured JSON snapshot; minimize sensitive data. |
| `after` | audit/compliance | System | `LogAuditEvent` action required | yes, through action only | Optional structured JSON snapshot; minimize sensitive data. |
| `ip_address` | security metadata | System | `LogAuditEvent` action required | yes, through action only | Optional request IP; never trust it for authorization decisions. |
| `user_agent` | security metadata | System | `LogAuditEvent` action required | yes, through action only | Optional request user agent; informational only. |
| `created_at` | system-managed | Laravel | Eloquent timestamp / database default | no | Append-oriented event time; events have no `updated_at`. |

## Entry

| Field | Classification | Who may change it | Mutation path | Mass assignment | Required rules |
|---|---|---|---|---|---|
| `id` | system-managed | Laravel | `HasUuids` / model creation | no | Never manually mutate. |
| `type` | domain data | Entry creation/edit flow | `CreateEntry` / `UpdateEntry` | yes, if validated | Must be an `EntryType` value. |
| `status` | authorization-sensitive | Entry source state / review workflow | `CreateEntry` / `UpdateEntry`; approval action required | no | Auto-derived as `draft` or `documented`; `vet_approved` must only come from approval flow; audit required. |
| `title` | domain data | Entry creation/edit flow | `CreateEntry` / `UpdateEntry` | yes, if validated | Required; regenerate slug through `GenerateUniqueEntrySlug` when appropriate. |
| `slug` | domain data | System | `GenerateUniqueEntrySlug` | no | Globally unique; collision-safe suffix strategy. |
| `summary` | domain data | Entry creation/edit flow | `CreateEntry` / `UpdateEntry` | yes, if validated | Optional; sanitize/validate at request boundary. |
| `warnings` | domain data | Entry creation/edit flow | `CreateEntry` / `UpdateEntry` | yes, if validated | Optional; sanitize/validate at request boundary. |
| `created_by` | audit/compliance | System | `CreateEntry` | no | Must reference creator user; audit required. |
| `updated_by` | audit/compliance | System | `UpdateEntry` | no | Must reference last updater; audit required. |
| `approved_by` | audit/compliance | Review workflow | Dedicated approval action required | no | Required with `vet_approved`; audit log pending. |
| `approved_at` | authorization-sensitive | Review workflow | Dedicated approval action required | no | Required with `vet_approved`; audit log pending. |
| `archived_at` | authorization-sensitive | Review/admin workflow | Dedicated archive/unarchive action required | no | Active lists must exclude archived entries. |
| `created_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |
| `updated_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |

## EntrySection

| Field | Classification | Who may change it | Mutation path | Mass assignment | Required rules |
|---|---|---|---|---|---|
| `id` | system-managed | Laravel | `HasUuids` / model creation | no | Never manually mutate. |
| `entry_id` | relationship | Entry section creation flow | `CreateEntry` / `UpdateEntry` | yes, if validated | Must reference an existing entry; cascades when entry is deleted. |
| `key` | domain data | Entry section creation/edit flow | `CreateEntry` / `UpdateEntry` | yes, if validated | Required; unique per entry; should come from `EntryTypeSectionTemplates` when using default templates. |
| `title` | domain data | Entry section creation/edit flow | `CreateEntry` / `UpdateEntry` | yes, if validated | Required; sanitize/validate at request boundary. |
| `body` | domain data | Entry section creation/edit flow | `CreateEntry` / `UpdateEntry` | yes, if validated | Required; sanitize/validate at request boundary. |
| `sort_order` | domain data | Entry section creation/edit flow | `CreateEntry` / `UpdateEntry` | yes, if validated | Required integer; entry relationship orders by this field. |
| `created_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |
| `updated_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |

## EntryAlias

| Field | Classification | Who may change it | Mutation path | Mass assignment | Required rules |
|---|---|---|---|---|---|
| `id` | system-managed | Laravel | `HasUuids` / model creation | no | Never manually mutate. |
| `entry_id` | relationship | Entry alias creation flow | `CreateEntry` / `UpdateEntry` | yes, if validated | Must reference an existing entry; cascades when entry is deleted. |
| `name` | domain data | Entry alias creation/edit flow | `CreateEntry` / `UpdateEntry` | yes, if validated | Required; sanitize/validate at request boundary. |
| `normalized_name` | search/deduplication | System | `NormalizeEntryAliasName` via `CreateEntry` / `UpdateEntry` | no | Required; unique per entry; lowercased, ASCII-folded, whitespace-squished. |
| `created_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |
| `updated_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |

## Source

| Field | Classification | Who may change it | Mutation path | Mass assignment | Required rules |
|---|---|---|---|---|---|
| `id` | system-managed | Laravel | `HasUuids` / model creation | no | Never manually mutate. |
| `type` | domain data | Source catalog admin flow | No app write path yet | yes, if validated | Must be a `SourceType` value. |
| `title` | domain data | Source catalog admin flow | No app write path yet | yes, if validated | Required; sanitize/validate at request boundary. |
| `authors` | domain data | Source catalog admin flow | No app write path yet | yes, if validated | Optional; supports book and non-book references. |
| `edition` | domain data | Source catalog admin flow | No app write path yet | yes, if validated | Optional. |
| `year` | domain data | Source catalog admin flow | No app write path yet | yes, if validated | Optional integer year. |
| `publisher` | domain data | Source catalog admin flow | No app write path yet | yes, if validated | Optional. |
| `isbn` | domain data | Source catalog admin flow | No app write path yet | yes, if validated | Optional; validation should match source type requirements when implemented. |
| `doi` | domain data | Source catalog admin flow | No app write path yet | yes, if validated | Optional. |
| `url` | domain data | Source catalog admin flow | No app write path yet | yes, if validated | Optional; validate URL format at request boundary. |
| `language` | domain data | Source catalog admin flow | No app write path yet | yes, if validated | Optional short language code. |
| `notes` | domain data | Source catalog admin flow | No app write path yet | yes, if validated | Optional internal notes; avoid public rendering unless explicitly intended. |
| `created_by` | audit/compliance | System | Source creation action required | no | Must reference creator user; audit log pending. |
| `created_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |
| `updated_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |

## Species

| Field | Classification | Who may change it | Mutation path | Mass assignment | Required rules |
|---|---|---|---|---|---|
| `id` | system-managed | Laravel | `HasUuids` / model creation | no | Never manually mutate. |
| `name` | catalog data | Species catalog admin flow | `InitialSpeciesSeeder` / no app write path yet | yes, if validated | Required; unique; initial values are `Gatos` and `Perros`. |
| `slug` | catalog data | System/admin catalog flow | `InitialSpeciesSeeder` / slug generation required for app writes | yes, if validated/generated | Required; unique; stable identifier for forms/search. |
| `created_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |
| `updated_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |

## EntrySpecies

| Field | Classification | Who may change it | Mutation path | Mass assignment | Required rules |
|---|---|---|---|---|---|
| `entry_id` | relationship | Entry species selection flow | No app write path yet | relationship only | Must reference an existing entry; cascades when entry is deleted. |
| `species_id` | relationship | Entry species selection flow | No app write path yet | relationship only | Must reference an existing species; restricted while in use. |
| `created_at` | system-managed | Laravel | Pivot timestamps | no | Automatic through `withTimestamps()`. |
| `updated_at` | system-managed | Laravel | Pivot timestamps | no | Automatic through `withTimestamps()`. |

## EntrySource

| Field | Classification | Who may change it | Mutation path | Mass assignment | Required rules |
|---|---|---|---|---|---|
| `entry_id` | relationship | Entry source citation flow | `CreateEntry` / `UpdateEntry` | relationship only | Must reference an existing entry; cascades when entry is deleted. |
| `source_id` | relationship | Entry source citation flow | `CreateEntry` / `UpdateEntry` | relationship only | Must reference an existing source; restricted while in use. |
| `locator` | citation metadata | Entry source citation flow | `CreateEntry` / `UpdateEntry` | relationship only | Optional flexible locator such as `p. 245`, `pp. 245-247`, `cap. 12`, or `tabla 4.3`. |
| `note` | citation metadata | Entry source citation flow | `CreateEntry` / `UpdateEntry` | relationship only | Optional internal/contextual note; sanitize/validate at request boundary. |
| `created_by` | audit/compliance | System | `CreateEntry` / `UpdateEntry` | relationship only | Must reference creator/updater user; audit required. |
| `created_at` | system-managed | Laravel | Pivot timestamps | no | Automatic through `withTimestamps()`. |
| `updated_at` | system-managed | Laravel | Pivot timestamps | no | Automatic through `withTimestamps()`. |

## SectionSource

| Field | Classification | Who may change it | Mutation path | Mass assignment | Required rules |
|---|---|---|---|---|---|
| `entry_section_id` | relationship | Section source citation flow | `CreateEntry` / `UpdateEntry` | relationship only | Must reference an existing entry section; cascades when section is deleted. |
| `source_id` | relationship | Section source citation flow | `CreateEntry` / `UpdateEntry` | relationship only | Must reference an existing source; restricted while in use. |
| `locator` | citation metadata | Section source citation flow | `CreateEntry` / `UpdateEntry` | relationship only | Optional flexible locator such as `p. 245`, `pp. 245-247`, `cap. 12`, or `tabla 4.3`. |
| `note` | citation metadata | Section source citation flow | `CreateEntry` / `UpdateEntry` | relationship only | Optional internal/contextual note; sanitize/validate at request boundary. |
| `created_by` | audit/compliance | System | `CreateEntry` / `UpdateEntry` | relationship only | Must reference creator/updater user; audit required. |
| `created_at` | system-managed | Laravel | Pivot timestamps | no | Automatic through `withTimestamps()`. |
| `updated_at` | system-managed | Laravel | Pivot timestamps | no | Automatic through `withTimestamps()`. |
