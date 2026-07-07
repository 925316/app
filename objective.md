# License Management System - Verified Product and System Specification

## 1. Purpose

This project is a license management system for a desktop client and its Laravel-based web administration backend.

The system serves three surfaces:

1. **End user web flows** for viewing licenses, binding or unbinding devices, downloading packages, and managing the account.
2. **Client API flows** for login, license verification, license activation, device unbind, and update check.
3. **Administrator web flows** for managing accounts, licenses, devices, sessions, packages, and logs.

The product goal is not only to enforce licensing rules. The goal is to provide a system that is:

- consistent across web and API behavior,
- predictable for the desktop client,
- secure enough for license verification and update distribution,
- and operable by administrators through auditable backend workflows.

This document is written against the current codebase. Wherever possible, claims in this file are based on verified implementation from controllers, requests, models, services, migrations, and feature tests. Where the document proposes a stronger future rule than the current implementation, that difference is called out explicitly.

## 2. How to Read This Document

Each section uses one of these evidence levels:

- **Verified in code**: confirmed in the current Laravel application and tests.
- **Project rule**: a rule the project should continue to follow across web and API behavior.
- **Recommended hardening**: a reasonable next step based on code review and external API/security guidance.

When there is tension between older wording and current code, this document prefers the verified implementation and then separately calls out the gap.

## 3. Primary Roles

### 3.1 End User

An end user can:

- sign in through the web application,
- view owned licenses,
- bind exactly one active device at a time,
- unbind the current device,
- request an HWID reset subject to cooldown,
- and download or view package releases available to licensed users.

### 3.2 Desktop Client

The client integrates with the JSON API and currently uses these routes:

- `POST /api/account/login`
- `POST /api/license/check`
- `POST /api/license/activate`
- `POST /api/license/unbind`
- `GET /api/update/check`

The client depends on:

- stable request field names,
- stable machine-readable error codes,
- deterministic signing behavior,
- and consistent device/session/license validation rules.

### 3.3 Administrator

An administrator can:

- manage accounts,
- suspend and unsuspend accounts,
- reset user HWID bindings,
- manage licenses and license transitions,
- review sessions and logs,
- and manage package releases.

Admin access is derived from effective license privilege, not from account role columns.

## 4. Verified Current Route Surface

### 4.1 Verified API Routes

From `routes/api.php`, the current API surface is:

1. `POST /api/account/login`
2. `POST /api/license/check`
3. `POST /api/license/activate`
4. `POST /api/license/unbind`
5. `GET /api/update/check`

There is **no separate heartbeat endpoint**. Heartbeat behavior is implemented through `POST /api/license/check`, which updates `client_sessions.last_heartbeat_at` on successful verification.

### 4.2 Verified Web Routes

From `routes/web.php`, the current web application provides:

- public welcome page,
- authenticated dashboard and profile management,
- user license views and activation-by-key,
- user device bind, unbind, and reset flows,
- package list, detail, and download,
- admin account, session, log, device, license, and package management routes.

Admin-only routes are protected by `auth`, `verified`, and `admin` middleware.

## 5. Core Domain Rules

## 5.1 Permission Source

### Verified in code

- The `accounts` table does not provide role-based admin state.
- `Account::getPrivilegeLevel()` derives privilege from the highest active, unexpired license.
- `Account::hasPrivilege()` compares that derived privilege numerically.
- `AdminMiddleware` grants admin-only access only when `hasPrivilege(7)` is true.

### Project rule

- Administrator rights should continue to come from effective license privilege, not duplicated account role fields.
- The effective privilege source should stay unified across web views, middleware, controllers, and API checks.

## 5.2 License Privilege Levels

### Verified in code

From `LicensePrivilege`:

- `0` = `none`
- `1` = `standard`
- `2` = `upgrade`
- `3` = `ultimate`
- `6` = `tester`
- `7` = `staff`

### Project rule

- `staff` remains the administrative privilege level.
- `upgrade` is not a standalone activation privilege.

## 5.3 License Status Machine

### Verified in code

From `LicenseStatus`, `License`, `LicenseService`, and related tests:

- Status values are:
  - `0` = `unused`
  - `1` = `active`
  - `2` = `suspended`
  - `3` = `expired`
  - `4` = `upgraded`
  - `5` = `revoked`
- Only `unused` licenses can be directly activated.
- Only `suspended` licenses can be reactivated.
- Only `active` licenses can be suspended.
- Only `active` licenses can be upgraded.
- Any non-revoked license can be revoked.
- Expiration is represented both by time and by status. `License::isExpired()` returns true if status is already `expired` or `expires_at` is in the past.
- `LicenseObserver` updates the status to `expired` when a changed license is observed as expired.

### Project rule

- The enum plus service layer should remain the runtime source of truth for status transitions.
- Controllers should not invent parallel status rules that disagree with `LicenseStatus` and `LicenseService`.

## 5.4 License Key Format

### Verified in code

From `LicenseService`:

- License keys are uppercase and validated against:

```text
^[A-Z0-9]{5}-[0-9A-F]{5}-[A-Z2-7]{5}-[A-Z3-8]{5}-[A-Z0-9]{5}$
```

- API request validators for heartbeat, activate, and unbind apply this format check.

### Project rule

- `license_key` remains the only API field name for license keys.
- Any looser UI-side pre-check must not replace backend validation.

## 5.5 Device Binding Rules

### Verified in code

From `DeviceController`, `AccountDevice`, API controllers, and tests:

- A user must have at least privilege `1` to bind, unbind, or reset HWID in the web app.
- A device is considered currently bound when `bound_at IS NOT NULL` and `unbound_at IS NULL`.
- Web bind uses a transaction and `lockForUpdate()` re-checking to prevent concurrent binds.
- API login, check, activate, and unbind all hash the incoming HWID with SHA-256 and compare it with the currently bound device hash.
- HWID is stored as `hwid_hash`; raw HWID is not persisted.
- Unbind deletes related client sessions for the same account and device.
- HWID reset unbinds current devices, deletes all sessions for the account, increments reset count, and records reset time.

### Verified limitation in current implementation

- The code strongly enforces one active bound device through request validation and transactional checks.
- Migration `2026_01_10_000017` provides a MySQL-specific hard guarantee using a virtual generated column `is_active_binding` with a unique index on `(account_id, is_active_binding)`. This constraint is not portable to all database engines.

### Project rule

- The account-level one-active-device rule should continue to be treated as a core business rule.
- Device identity should continue to use irreversible `hwid_hash` as the binding key.

### Recommended hardening

- If portability to other database engines is needed, consider an application-level alternative strategy for databases that do not support virtual generated columns with unique indexes.

## 5.6 Session Rules

### Verified in code

From `ClientSession`, API controllers, and tests:

- `client_sessions.session_token` is unique.
- A session belongs to both an account and a device.
- Session liveness is heartbeat-based through `last_heartbeat_at`.
- `ClientSession::scopeActive()` treats a session as active when heartbeat freshness is within 5 minutes.
- API login deletes any existing session for the same account and device before creating a new one.
- `POST /api/license/check` updates `last_heartbeat_at` only on success.
- Unbind deletes sessions for the affected device; HWID reset deletes all sessions for the account.

### Verified limitation in current implementation

- The code does **not** currently enforce a global one-online-session-per-account rule across every possible device combination at the database level.
- The strongest current guarantee is one active session per account-device pair plus the separate business rule that only one device should be bound at a time.

### Project rule

- Session liveness should continue to be heartbeat-driven.
- Any documentation claiming "exactly one online session per account" must be phrased carefully and should not overstate what the code currently enforces.

## 6. API Request Contracts

## 6.1 Shared POST Fields

### Verified in code

The POST endpoints consistently use:

- `session_token` where session lookup is required,
- `license_key` where a license is being checked or changed,
- `hwid`,
- `nonce`,
- `timestamp`,
- optional or required `version` depending on endpoint.

`GET /api/update/check` uses query parameters rather than a JSON body.

## 6.2 Request Validation by Endpoint

### `POST /api/account/login`

### Verified in code

From `ClientLoginRequest` and `ClientLicenseController@login`:

- Validator rules:
  - `email`: required string email
  - `password`: required string
  - `hwid`: required string
  - `nonce`: required string
  - `timestamp`: required integer
  - `version`: required string
  - `country_code`: optional two-character string
- The controller additionally rejects empty credentials with `AUTH_REQUIRED`.

### `POST /api/license/check`

### Verified in code

From `ClientHeartbeatRequest` and `ClientLicenseController@check`:

- Required: `session_token`, `license_key`, `hwid`, `nonce`, `timestamp`
- Optional: `version`
- The license key format is validated in the request class.

### `POST /api/license/activate`

### Verified in code

From `ClientActivateRequest` and `ClientLicenseController@activate`:

- Required: `session_token`, `license_key`, `hwid`, `nonce`, `timestamp`
- Optional: `version`

### `POST /api/license/unbind`

### Verified in code

From `ClientUnbindRequest` and `ClientLicenseController@unbind`:

- Required: `session_token`, `license_key`, `hwid`, `nonce`, `timestamp`
- Optional: `version`

### `GET /api/update/check`

### Verified in code

From `ClientUpdateCheckRequest` and `ClientPackageController@check`:

- Query fields are all nullable at validator level:
  - `session_token`
  - `release_channel`
  - `current_version`
- Controller-level checks then enforce:
  - non-empty `session_token`
  - `release_channel` in `stable|dev`
  - semantic-version format when `current_version` is present

## 7. API Response Contract

## 7.1 Verified Current Envelope

### Verified in code

API success and error responses currently use this top-level shape:

```json
{
  "code": 200,
  "error_code": null,
  "message": "OK",
  "data": {},
  "signature": "base64...",
  "meta": {
    "signature": {
      "algorithm": "RSA-2048-SHA256",
      "key_id": "main-2026-01"
    }
  }
}
```

Where:

- `code` mirrors the HTTP status code.
- `error_code` is `null` on success and a stable machine-readable code on failure.
- `message` is human-readable.
- `data` contains endpoint-specific payload on success and `null` on error.

### Verified API error codes

From `ApiErrorCode`:

- `AUTH_REQUIRED`
- `NONCE_REPLAY`
- `TIMESTAMP_OUT_OF_WINDOW`
- `DEVICE_MISMATCH`
- `DEVICE_NOT_BOUND`
- `LICENSE_INVALID`
- `LICENSE_INEFFECTIVE`
- `INVALID_CHANNEL`
- `INVALID_VERSION`
- `PACKAGE_NOT_FOUND`
- `RATE_LIMITED`
- `VALIDATION_FAILED`
- `SERVER_ERROR`

## 7.2 Semantics of `code` vs `error_code`

### Verified in code

- `error_code` is actively used by controllers and asserted by feature tests.
- Different business failures intentionally share the same HTTP status class while using different `error_code` values.
- This means `error_code` is not dead weight in the current implementation.

### Recommended contract direction

- Keep `error_code` as the stable business reason field.
- Treat body `code` as a mirror of HTTP status rather than as an independent source of truth.
- If the contract is ever simplified, removing body `code` would be more reasonable than removing `error_code`.

## 7.3 Response Signing

### Verified in code

From `CryptoService`, `ClientLicenseController`, `ClientPackageController`, and `ApiFormRequest`:

- Responses include `signature` and `meta.signature`.
- Signing algorithm identifier is `RSA-2048-SHA256`.
- Signing uses `openssl_sign(..., OPENSSL_ALGO_SHA256)` and base64-encodes the signature.
- Canonicalization recursively sorts associative-array keys and JSON-encodes without escaped Unicode or slashes.
- Successful responses sign the `data` value only.
- Signed controller error responses sign `data`, which is `null` on that path.
- Validation error responses sign `data`, which is `null` on that path.
- Current responses do not emit `meta.signature.covers`.

### Verified security limitation

- Unsigned envelope fields such as `code`, `error_code`, `message`, and signature metadata are not integrity-protected by the current signature behavior.
- Clients should not treat top-level envelope fields outside `data` as signed unless a future protocol hardening change explicitly signs them.

### Recommended hardening

- External API guidance supports separating HTTP status from machine-readable business codes. A future protocol hardening task should add an explicit signature scope before clients treat the full envelope as trusted signed content.

### error_code to HTTP Status Mapping

| error_code | HTTP Status |
|---|---|
| AUTH_REQUIRED | 401 |
| NONCE_REPLAY | 409 |
| TIMESTAMP_OUT_OF_WINDOW | 422 |
| DEVICE_MISMATCH | 422 |
| DEVICE_NOT_BOUND | 409 |
| LICENSE_INVALID | 422 |
| LICENSE_INEFFECTIVE | 403 |
| INVALID_CHANNEL | 422 |
| INVALID_VERSION | 422 |
| PACKAGE_NOT_FOUND | 404 |
| RATE_LIMITED | 429 |
| VALIDATION_FAILED | 422 |
| SERVER_ERROR | 500 |

## 7.4 Canonical JSON Rule

### Verified in code

The current implementation canonicalizes JSON by:

1. recursively sorting associative-array keys,
2. leaving indexed arrays in order,
3. encoding as UTF-8 JSON without escaped Unicode or escaped slashes.

### Recommended hardening

- If long-term cross-language verification is critical, formalize the canonicalization rule more rigorously and version it clearly, because C++ and PHP serializers can diverge at the edges if the canonicalization contract is underspecified.

## 8. Endpoint Behavior Summary

## 8.1 `POST /api/account/login`

### Verified in code

- Applies rate limiting for repeated failed logins.
- Rejects stale or future timestamps beyond 300 seconds.
- Uses nonce replay protection scoped to `account.login|sha1(email)`.
- Requires a currently bound device matching the provided HWID hash.
- Requires the account to be unsuspended and to have effective privilege at least `1`.
- Deletes any existing session for the same account and device before creating a new session.
- Returns `session_token`, account summary, and effective license summary.
- Records an `account.login` event.

## 8.2 `POST /api/license/check`

### Verified in code

- Functions as the current heartbeat and license verification endpoint.
- Rejects invalid or missing session token with `AUTH_REQUIRED`.
- Rejects malformed license key format through request validation.
- Rejects stale or future timestamps beyond 300 seconds.
- Uses nonce replay protection scoped to `license.check|session_token`.
- Confirms session existence, license existence, effective license state, session-account ownership, and device HWID match.
- Updates `last_heartbeat_at` only on success.
- Returns `status`, `expires_at`, `expires_timestamp`, `plan_level`, and `username` on success.

## 8.3 `POST /api/license/activate`

### Verified in code

- Requires valid session, timestamp, nonce, license key, and device match.
- Uses nonce replay protection scoped to `license.activate|session_token`.
- Rejects non-existent or ineffective licenses.
- Rejects upgrade-only licenses as standalone activations.
- Rejects activation when the account already has an active effective license that blocks the operation under current controller rules.
- Returns signed success payload on successful activation.

## 8.4 `POST /api/license/unbind`

### Verified in code

- Requires valid session, timestamp, nonce, license key, and device match.
- Uses nonce replay protection scoped to `license.unbind|session_token`.
- Requires an effective owned license and an active bound device.
- Runs inside a transaction, locks the bound device row, sets `unbound_at`, deletes related client sessions, and logs `device.unbound`.
- Returns `status`, `license_key`, `device_id`, `unbound_at`, and `unbound_timestamp`.

## 8.5 `GET /api/update/check`

### Verified in code

- Does not use nonce or timestamp.
- Requires a valid session token and a still-bound device.
- Requires the session account to have at least privilege `1`.
- Validates `release_channel` and `current_version` at controller level.
- Returns the latest release for `stable` or `dev`.
- Validates that `download_url` and optional `virus_detection_url` are safe public HTTPS URLs.
- Returns `current_version`, `version`, `release_channel`, `update_available`, `reason`, `download_url`, `changelog`, and `virus_detection_url`.

## 9. Anti-Replay and Timing Rules

### Verified in code

From `NonceGuardService` and API controllers:

- POST endpoints use nonce replay protection with TTL 300 seconds.
- The nonce storage key is `api:nonce:` plus a SHA-256 hash of `scope|nonce`.
- Redis `SET ... EX ... NX` is attempted first.
- Cache fallback is used if Redis fails.
- API timestamp freshness is enforced with an absolute `<= 300 seconds` window.

### Project rule

- Nonce scope must continue to be endpoint-aware so replay on one route does not incorrectly poison another route.

## 10. Database Summary

## 10.1 `accounts`

### Verified in code

- Stores identity, password, login tracking, suspension state, HWID reset counters, email verification, and two-factor fields.
- No admin role column exists.
- `username` and `email` are unique.

## 10.2 `account_devices`

### Verified in code

- Stores `account_id`, `hwid_hash`, packed IP, optional `country_code`, optional `characteristics`, and binding timestamps.
- Unique key exists on `(account_id, hwid_hash)`.
- The schema also contains a unique index on `(account_id, bound_at, unbound_at)`, but this should not be oversold as a perfect enforcement of the one-active-device business rule.

## 10.3 `licenses`

### Verified in code

- Stores key, privilege, status, owner account, expiry, activation and suspension timestamps, origin IP, and notes.
- `key` is unique.

## 10.4 `client_sessions`

### Verified in code

- Stores session token, account, device, packed IP, client version, and last heartbeat.
- `session_token` is unique.
- Index exists on `last_heartbeat_at`.

## 10.5 Other Supporting Tables

### Verified in code

- `event_logs` records significant events.
- `package_releases` stores downloadable package metadata.
- `usage_statistics` exists for aggregated statistics.

## 11. Verified Test Coverage Themes

### Verified in code

Current feature tests cover at least these API themes:

- successful signed responses,
- nonce replay rejection,
- stale and future timestamp rejection,
- validation failure envelope shape,
- device mismatch,
- license ineffective and invalid cases,
- session and ownership checks,
- update check contract shape,
- and rate limiting for login.

This test coverage is strong enough to justify documenting the current API contract as real behavior rather than aspiration.

## 12. Known Mismatches Between Older Spec Wording and Current Code

### Verified from code review

1. There is no standalone heartbeat endpoint. Heartbeat is implemented by `POST /api/license/check`.
2. Login request validation marks `email` and `password` as required in the current implementation.
3. `version` is optional on check, activate, and unbind request validators in the current implementation.
4. The project has often described a stronger one-online-session rule than the code currently enforces.
5. Current response signing protects successful `data` payloads, while signed controller errors and validation errors sign `null` through `data`; no current response emits `meta.signature.covers`.

These are important because the document should not claim stronger guarantees than the implementation actually provides.

## 13. Recommended Direction for the Next Revision Cycle

These are not claims about current behavior. They are the most reasonable next steps given the verified code and external API/security guidance.

1. **Future protocol hardening**: define and emit an explicit signature scope, then update client-side validation to verify exactly that scope.
2. **Clarify the contract around body `code`**. Keep it only as a mirror of HTTP status, or eventually remove it if backward compatibility allows.
3. **Evaluate database portability** for the one-active-device rule if migration to non-MySQL engines is planned, since the current guarantee uses MySQL-specific virtual generated columns.
4. **Align validator rules with controller behavior** for update-check fields so validation and runtime behavior tell the same story.
5. **Keep `error_code` stable** as the business-level machine code that clients use for branching.
6. **Add session cleanup scheduled task** to prevent indefinite session accumulation for accounts that stop sending heartbeats.
7. **Consider Redis fallback atomicity** for `NonceGuardService` when Redis is unavailable, since cache-based fallback may not provide the same atomicity guarantees.
8. **Monitor `LicenseObserver` recursion risk** in the `updated()` event handler, since status changes triggered within the observer could re-enter the observer.

## 14. Summary

The current codebase already implements a coherent license-management backend with:

- privilege-derived permissions,
- a usable license state model,
- application-level bound-device enforcement backed by transactional checks,
- nonce and timestamp protection for state-changing or authenticated POST routes,
- signed API responses that currently protect successful `data` payloads and sign `null` for signed controller-error and validation-error paths,
- and strong feature-test coverage for the current API surface.

The main work for documentation is not inventing a new system. It is documenting the existing system honestly, separating verified behavior from future hardening, and avoiding claims that the current code does not yet fully guarantee.
