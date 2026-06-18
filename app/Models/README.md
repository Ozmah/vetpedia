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
| `role` | authorization-sensitive | Admin management flow | `UpdateUserRole` | no | Prevent superadmin demotion; authorize caller before use. |
| `suspended_at` | authorization-sensitive | Admin management flow | Dedicated suspend/unsuspend action | no | Block login and active sessions. |
| `deleted_at` | system-managed | `DeleteUser` | SoftDeletes via `$user->delete()` | no | Prevent superadmin deletion. |
| `created_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |
| `updated_at` | system-managed | Laravel | Eloquent timestamps | no | Automatic. |
| `remember_token` | auth-sensitive | Laravel auth | Remember-me flow | framework only | Hidden from serialization. |
| `two_factor_secret` | auth-sensitive | Fortify | Fortify 2FA flow | framework only | Hidden from serialization. |
| `two_factor_recovery_codes` | auth-sensitive | Fortify | Fortify 2FA flow | framework only | Hidden from serialization. |
| `two_factor_confirmed_at` | auth-sensitive | Fortify | Fortify 2FA flow | framework only | Hidden from serialization. |
