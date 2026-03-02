# License Management System - Development Requirements Manual

## Critical Design Constraints

**Please ensure the AI understands and strictly adheres to these fundamental constraints:**

### Development Phase Boundary (Important)

- **Web Phase (current)**: prioritize web routes/controllers/services/models consistency first.
- **API Phase (later)**: `routes/api.php`, `ClientLicenseController`, `ClientPackageController`, request anti-replay (`nonce` + `timestamp`), and response signing (`CryptoService`) can be implemented after Web Phase is stable.
- **API implementation baseline (after Web Phase is stable)**:
  1. `routes/api.php` + `ClientLicenseController@check` as the first heartbeat endpoint.
  2. `ClientHeartbeatRequest` validation for `session_token`, `license_key`, `hwid`, `nonce`, `timestamp`.
  3. Replay protection with Redis nonce guard (5 minutes) and timestamp window (`<= 300s`).
  4. `CryptoService` signs `data` payload and must include RSA signing support.
  5. Add 6-10 API Feature tests (at minimum: success, replay, expired timestamp, HWID mismatch).
  6. Register `api` routing in `bootstrap/app.php` so `routes/api.php` is actually loaded.
  7. Define a stable error-code dictionary for `/api/license/check` (do not rely on free-form message text for client decisions).
  8. Signature metadata is returned in `meta.signature` with fixed fields `algorithm` and `key_id`; signature input is canonical JSON of `data` only.
  9. Heartbeat/session update uses `session_token` as lookup key: successful `check` updates `client_sessions.last_heartbeat_at`; failed checks do not update it.
- Even in phased delivery, shared domain rules (license status machine, privilege source, key regex, bind/unbind consistency) must stay unified.

### 1. Permission System (MOST IMPORTANT!)

- **DO NOT** add `is_admin`, `role`, or any permission fields to the `accounts` table.
- Administrator permissions are determined **SOLELY** by the `privilege` field in the `licenses` table.
- A user is considered administrator when their **highest effective privilege** is `7 (staff)`.
- **Effective license** means: `status = active` and `expires_at > now()`.

### 2. Routing & Access Control

- **Web Context**:
  - Shared user-facing URLs are allowed (e.g. dashboard, licenses, devices, packages).
  - Admin-only operation endpoints may exist and be protected by `AdminMiddleware`.
  - Controllers must still enforce authorization checks internally (defense-in-depth).
- **API Context**:
  - Dedicated `routes/api.php` for Client/Software communication.
  - API responses MUST be JSON.
  - This block belongs to **API Phase** and can be deferred during Web Phase.

### 3. License State Machine

- State transitions MUST follow documented rules exactly (`revoked` is terminal).
- Critical operations (Activate, Bind, Unbind, Upgrade) MUST use Database Transactions.
- **No direct device pointer in `licenses`** (no `current_device_id`).
- Device association is resolved through account ownership (`licenses.used_by -> accounts -> account_devices`).

### 4. Device Binding Restrictions

- An account can have **ONLY ONE ACTIVE DEVICE** at a time.
- Current active device condition: `bound_at IS NOT NULL AND unbound_at IS NULL`.
- Enforce with:
  1. Transaction + row-level lock during bind/unbind.
  2. Database-level uniqueness guard (generated-column unique strategy recommended).
- **HWID Logic**: store only irreversible hash (SHA-256), never raw HWID.
- Device `characteristics` (if collected) is optional and **must not** replace `hwid_hash` as the binding identity source.

### 5. Client-Server Communication Protocol

- **Transport Security**: ALL API requests MUST use HTTPS.
- Replay Attack Protection:
  - Requests MUST include `nonce` and `timestamp`.
  - Server validates timestamp drift (<= 300 seconds).
  - Server MUST reject reused nonce within 5 minutes (Redis `SET key value NX EX 300` recommended).
- Response Signing (Anti-Tamper):
  - Critical responses (`/license/check`, `/license/activate`) MUST include `signature`.
  - Signature is produced from the `data` payload using server private key.
  - Signing algorithm for this project is fixed to **RSA-2048 (SHA-256)**.
  - Canonical JSON serialization rule MUST be fixed and shared with C++ client (same key order/encoding).

---

## API Specification (Client <-> Server)

**Base URL**: `https://your-domain.com/api/`
**Format**: JSON

### 1. Common Request Structure

The C++ client should send requests in this format:

```json
{
    "session_token": "client_session_token",
    "license_key": "XXXXX-XXXXX-XXXXX-XXXXX-XXXXX",
    "hwid": "client_generated_hwid_string",
    "nonce": "random_uuid_or_string",
    "version": "1.0.0",
    "timestamp": 1705555555 // Unix Timestamp
}
```
- Field naming is fixed: use `license_key` consistently (do not use `key` alias).
- `session_token` is required for heartbeat session lookup and update.

### 2. Common Response Structure (With Signature)

The server must respond in this format. The client will verify the `signature` against `data`.

```json
{
    "code": 200,
    "error_code": null,
    "message": "OK",
    "data": {
        "status": "active", // active, suspended, expired, unbound
        "expires_at": "2026-12-31 23:59:59",
        "expires_timestamp": 1705555555, // Added for C++ easy parsing
        "plan_level": 5,
        "username": "user_name_here" // Added per user requirement
    },
    "signature": "base64_encoded_rsa_signature_of_data_block",
    "meta": {
        "signature": {
            "algorithm": "RSA-2048-SHA256",
            "key_id": "main-2026-01"
        }
    }
}
```

Rules:
- HTTP status code is authoritative; body `code` mirrors HTTP status.
- `error_code` is a stable machine-readable business code (nullable on success).
- Signature metadata for signed responses must be provided in `meta.signature`.

### 3. Error Code Dictionary (`/api/license/check`)

| HTTP | `error_code` | Meaning |
| --- | --- | --- |
| 200 | `null` | Success |
| 401 | `AUTH_REQUIRED` | Session token is missing or invalid |
| 409 | `NONCE_REPLAY` | Nonce reused within protection window |
| 422 | `TIMESTAMP_OUT_OF_WINDOW` | Timestamp drift exceeds allowed window |
| 422 | `DEVICE_MISMATCH` | HWID does not match active bound device |
| 422 | `LICENSE_INVALID` | License key not found or invalid |
| 403 | `LICENSE_INEFFECTIVE` | License not active / suspended / revoked / expired |
| 500 | `SERVER_ERROR` | Internal server error |

### 4. Signature Payload Canonicalization

To avoid cross-language verification mismatch, the signing payload must be deterministic:

1. Use UTF-8 JSON.
2. Sort keys in ascending lexical order.
3. No extra whitespace.
4. Signature input is the canonical JSON string of `data`.
5. Encode signature as Base64.

Client verification must follow the exact same canonicalization process.


---

## Database Design

### `accounts`

No permission fields here. Permissions come from `licenses.privilege`.

| Name                        | Type            | Constraints      | Description                          |
| --------------------------- | --------------- | ---------------- | ------------------------------------ |
| `id`                        | BIGINT UNSIGNED | PK, AI           | Primary Key                          |
| `username`                  | VARCHAR(255)    | UNIQUE, NOT NULL | Username                             |
| `email`                     | VARCHAR(255)    | UNIQUE, NOT NULL | Email Address                        |
| `password`                  | VARCHAR(255)    | NOT NULL         | Laravel default bcrypt               |
| `last_login_at`             | TIMESTAMP       | NULLABLE         | Last Login Time                      |
| `last_ip_address`           | VARCHAR(45)     | NULLABLE         | Last Login IP Address                |
| `last_user_agent`           | TEXT            | NULLABLE         | Last User-Agent Used                 |
| `hwid_reset_count`          | INT UNSIGNED    | DEFAULT 0        | HWID Reset Count                     |
| `hwid_last_reset_at`        | TIMESTAMP       | NULLABLE         | Last HWID Reset Time                 |
| `is_suspended`              | BOOLEAN         | DEFAULT FALSE    | Account Suspension Status            |
| `suspension_reason`         | VARCHAR(255)    | NULLABLE         | Account Suspension Reason            |
| `suspended_until`           | TIMESTAMP       | NULLABLE         | Suspension End Time                  |
| `email_verified_at`         | TIMESTAMP       | NULLABLE         | Laravel Email Verification Time      |
| `two_factor_secret`         | TEXT            | NULLABLE         | Laravel Two-Factor Secret Key        |
| `two_factor_recovery_codes` | TEXT            | NULLABLE         | Laravel Two-Factor Recovery Codes    |
| `two_factor_confirmed_at`   | TIMESTAMP       | NULLABLE         | Laravel Two-Factor Confirmation Time |
| `remember_token`            | VARCHAR(100)    | NULLABLE         | Laravel Token                        |
| `created_at`, `updated_at`  | TIMESTAMP       |                  | Laravel Timestamps                   |

**Optimization**

UNIQUE

- `username`
- `email`

INDEX

- `email_verified_at`
- `created_at`

---

### `account_devices`

| Name                       | Type            | Constraints                         | Description             |
| -------------------------- | --------------- | ----------------------------------- | ----------------------- |
| `id`                       | BIGINT UNSIGNED | PK, AI                              | Primary Key             |
| `account_id`               | BIGINT UNSIGNED | FK`accounts.id`, NOT NULL           | Associated Account ID   |
| `hwid_hash`                | VARCHAR(64)     | NOT NULL                            | Device Hardware ID Hash |
| `ip_address`               | VARCHAR(45)     | NOT NULL                            | IP Address              |
| `country_code`             | CHAR(2)         | NULLABLE                            | Country Code            |
| `first_seen_at`            | TIMESTAMP       | DEFAULT CURRENT_TIMESTAMP, NOT NULL | First Seen Time         |
| `last_seen_at`             | TIMESTAMP       | DEFAULT CURRENT_TIMESTAMP, NOT NULL | Last Seen Time          |
| `bound_at`                 | TIMESTAMP       | NULLABLE                            | Binding Time            |
| `unbound_at`               | TIMESTAMP       | NULLABLE                            | Unbinding Time          |
| `created_at`, `updated_at` | TIMESTAMP       |                                     | Laravel Timestamps      |

**Device Characteristic JSON Structure Example:**

- {"resolution": "1920x1080", "timezone": "UTC+8", "platform": "Windows"}

**Optimization:**

UNIQUE

- `(account_id, hwid_hash)`

INDEX

- `(account_id, last_seen_at)`

---

### `licenses` `XXXXX-XXXXX-XXXXX-XXXXX-XXXXX`(ALL-UPPERCASE)

Users with `privilege = 7` have admin rights.

License key generation and validation must follow the same strict backend regex:
`'^[A-Z0-9]{5}-[0-9A-F]{5}-[A-Z2-7]{5}-[A-Z3-8]{5}-[A-Z0-9]{5}$'`.
UI may apply a looser pre-check for usability, but backend regex is the final authority.


| Name                      | Type             | Constraints               | Description                                                         |
| ------------------------- | ---------------- | ------------------------- | ------------------------------------------------------------------- |
| `id`                      | BIGINT UNSIGNED  | PK, AI                    | Primary Key                                                         |
| `key`                     | VARCHAR(50)      | UNIQUE, NOT NULL          | License Key                                                         |
| `privilege`               | TINYINT UNSIGNED | NOT NULL, DEFAULT 0       | License Tier (1=standard, 2=upgrade, 3=ultimate, 6=tester, 7=staff) |
| `status`                  | TINYINT UNSIGNED | DEFAULT 0                 | Current Status                                                      |
| `used_by`                 | BIGINT UNSIGNED  | FK`accounts.id`, NULLABLE | Owning Account ID                                                   |
| `expires_at`              | DATETIME         | NOT NULL                  | Expiration Time (Default: now()->addDay())                          |
| `activated_at`            | TIMESTAMP        | NULLABLE                  | Activation Time                                                     |
| `suspended_at`            | TIMESTAMP        | NULLABLE                  | Suspension Time                                                     |
| `created_from_ip`         | VARCHAR(45)      | NULLABLE                  | Creation IP Address                                                 |
| `notes`                   | TEXT             | NULLABLE                  | Administrator Notes                                                 |
| `created_at`,`updated_at` | TIMESTAMP        |                           | Laravel Timestamps                                                  |

**Status Transition Rules**:

- `status`: 0='unused', 1='active', 2='suspended', 3='expired', 4='upgraded', 5='revoked'

- `unused` → `active`: User first activation
- `active` → `suspended`: Risk control trigger or administrator action
- `active` → `expired`: Reached expiration time
- `active` → `upgraded`: User upgraded license
- `suspended` → `active`: Administrator unsuspension
- Any status → `revoked`: License revoked

**Optimization**

UNIQUE

- `key`

INDEX

- `activated_at`
- `(used_by, status)`
- `(status, expires_at)`
- `(privilege, created_at)`
- `(expires_at, status)`

---

### `event_logs`

| Name                       | Type             | Constraints               | Description                                 |
| -------------------------- | ---------------- | ------------------------- | ------------------------------------------- |
| `id`                       | BIGINT UNSIGNED  | PK, AI                    | Primary Key                                 |
| `event_type`               | VARCHAR(255)     | NOT NULL                  | Event Type e.g., `account.registered`       |
| `event_level`              | TINYINT UNSIGNED | DEFAULT 0                 | Event Level 0=info, 1=warn, 2=error         |
| `account_id`               | BIGINT UNSIGNED  | FK`accounts.id`, NULLABLE | Associated Account ID                       |
| `license_id`               | BIGINT UNSIGNED  | FK`licenses.id`, NULLABLE | Associated License ID                       |
| `ip_address`               | VARCHAR(45)      | NULLABLE                  | Operation IP Address                        |
| `actor_id`                 | BIGINT UNSIGNED  | FK`accounts.id`, NULLABLE | Actor ID (User who performed the operation) |
| `details`                  | JSON             | NULLABLE                  | Event Details                               |
| `created_at`, `updated_at` | TIMESTAMP        | DEFAULT CURRENT_TIMESTAMP | Laravel Timestamps                          |

**Event Type Categories**:

- `account.activated`: License Activation
- `device.bound`: Device Binding
- `device.unbound`: Device Unbinding
- `login.anomaly`: Login from Unusual Location
- `account.suspended`: Account Suspension

**Optimization**

INDEX

- `actor_id`
- `(event_type, created_at)`
- `(account_id, created_at)`
- `(license_id, created_at)`

**Partitioning Strategy**:

- Monthly Partitioning: `PARTITION BY RANGE (TO_DAYS(created_at))`
- Keep recent 6 months of critical data, archive historical data

---

### `package_releases`

| Name                       | Type                  | Constraints      | Description                          |
| -------------------------- | --------------------- | ---------------- | ------------------------------------ |
| `id`                       | BIGINT UNSIGNED       | PK, AI           | Primary Key                          |
| `version`                  | VARCHAR(50)           | UNIQUE, NOT NULL | Version Number (Semantic Versioning) |
| `release_channel`          | ENUM('stable', 'dev') | DEFAULT 'stable' | Release Channel                      |
| `download_url`             | VARCHAR(255)          | NOT NULL         | Download URL                         |
| `virus_detection_url`      | VARCHAR(255)          | NULLABLE         | Virus detection report URL           |
| `changelog`                | TEXT                  | NULLABLE         | Changelog                            |
| `created_at`, `updated_at` | TIMESTAMP             |                  | Laravel Timestamps                   |

**Optimization**

UNIQUE

- `version`
- `(version, release_channel)`

---

### `client_sessions`

| Name                       | Type            | Constraints                      | Description         |
| -------------------------- | --------------- | -------------------------------- | ------------------- |
| `id`                       | BIGINT UNSIGNED | PK, AI                           | Primary Key         |
| `session_token`            | VARCHAR(128)    | UNIQUE, NOT NULL                 | Session Token       |
| `account_id`               | BIGINT UNSIGNED | FK`accounts.id`, NOT NULL        | Account ID          |
| `device_id`                | BIGINT UNSIGNED | FK`account_devices.id`, NOT NULL | Device ID           |
| `ip_address`               | VARCHAR(45)     | NOT NULL                         | Session IP Address  |
| `client_version`           | VARCHAR(50)     | NOT NULL                         | Client Version      |
| `last_heartbeat_at`        | TIMESTAMP       | NULLABLE                         | Last Heartbeat Time |
| `created_at`, `updated_at` | TIMESTAMP       |                                  | Laravel Timestamps  |

**Optimization**

UNIQUE

- `session_token`

INDEX

- `last_heartbeat_at`

---

### `usage_statistics`

| Name                       | Type             | Constraints | Description                                           |
| -------------------------- | ---------------- | ----------- | ----------------------------------------------------- |
| `id`                       | BIGINT UNSIGNED  | PK, AI      | Primary Key                                           |
| `stat_type`                | TINYINT UNSIGNED | NOT NULL    | Statistics Type 0=global, 1=user, 2=license, 3=server |
| `stat_key`                 | VARCHAR(255)     | NOT NULL    | Statistics Key Name                                   |
| `stat_value`               | DECIMAL(15,2)    | NOT NULL    | Statistics Key Value                                  |
| `created_at`, `updated_at` | TIMESTAMP        |             | Laravel Timestamps                                    |

**Description**
Need to display: Global login count 453459, Global total usage time 26y 4m 13d 20h 32m, User login count 650, User usage time 1y 1m 1d 20h 11m.
Therefore, the backend needs to calculate statistics by category; this table only needs the final results.

---

## Project Structure

### Database Migrations & Seeders

```
database/
├── migrations/
│   ├── 2026_01_10_000010_create_accounts_table.php
│   ├── 2026_01_10_000011_create_account_devices_table.php
│   ├── 2026_01_10_000012_create_licenses_table.php
│   ├── 2026_01_10_000013_create_event_logs_table.php
│   ├── 2026_01_10_000014_create_package_releases_table.php
│   ├── 2026_01_10_000015_create_client_sessions_table.php
│   └── 2026_01_10_000016_create_usage_statistics_table.php
├── seeders/
|   ├── AccountDeviceSeeder.php
│   ├── ClientSessionSeeder.php
│   ├── EventLogSeeder.php
│   ├── LicenseSeeder.php
│   ├── AccountSeeder.php
│   ├── PackageReleaseSeeder.php
│   ├── UsageStatisticSeeder.php
│   └── DatabaseSeeder.php
└── factories/
    ├── AccountFactory.php
    ├── AccountDeviceFactory.php
    ├── ClientSessionFactory.php
    ├── PackageReleaseFactory.php
    ├── UsageStatisticFactory.php
    ├── EventLogFactory.php
    └── LicenseFactory.php
```

### Application Structure

```
C:\code\HTML\app\
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   |   ├── Api/                     # Dedicated API Controllers for C++ Client
│   │   │   ├── ClientLicenseController.php
│   │   │   │   # Handles: /api/license/activate
│   │   │   │   # Handles: /api/license/check (Heartbeat)
│   │   │   │   # Logic: Validates Key + HWID -> Returns Signed JSON.
│   │   │   │
│   │   │   ├── ClientPackageController.php
│   │   │   |   # Handles: /api/update/check
│   │   │   |   # Returns latest version info + download URL.
|   |   │   │
│   │   │   ├── Auth/                    # Breeze Authentication Controllers (auto-generated)
│   │   │   │   # Handles basic authentication functions: login, registration, password reset, etc.
│   │   │   │   # No modification needed unless extending authentication logic.
│   │   │   │
│   │   │   ├── DashboardController.php  # Dashboard Controller
│   │   │   │   # Homepage controller, displays content based on user permissions.
│   │   │   │   # Regular users: View their own licenses and device status.
│   │   │   │   # Administrators: View system statistics, recent activities, etc.
│   │   │   │
│   │   │   ├── LicenseController.php    # License Management Controller
│   │   │   │   # Core controller! Handles license creation, viewing, updating, activation, revocation.
│   │   │   │   # Pay special attention: License format validation (XXXXX-XXXXX...), status transition logic.
│   │   │   │   # Regular users can only view their own licenses; admins manage all licenses.
│   │   │   │
│   │   │   ├── DeviceController.php     # Device Management Controller
│   │   │   │   # Handles device binding, unbinding, and viewing.
│   │   │   │   # Key restriction: Each account can only bind one device.
│   │   │   │   # Records HWID hash, binding time, unbinding time.
│   │   │   │
│   │   │   ├── PackageController.php    # Software Package Management Controller
│   │   │   │   # Handles package upload, version management, and download.
│   │   │   │   # Regular users: View and download packages.
│   │   │   │   # Administrators: Upload new versions, manage historical versions.
│   │   │   │
│   │   │   └── LogController.php        # Log Viewing Controller
│   │   │       # Administrator access only.
│   │   │       # View system event logs, supports filtering and searching.
│   │   │       # Pay special attention to log pagination and query performance.
│   │   │
│   │   ├── Middleware/
│   │   │   ├── EnsureJsonResponse.php   # Ensures API requests accept application/json
│   │   │   └── AdminMiddleware.php      # Administrator Permission Verification Middleware
│   │   │       # Simple but practical middleware.
│   │   │       # Checks if the user has administrator privileges.
│   │   │       # Can be added to route groups requiring admin permissions.
│   │   │
│   │   └── Requests/                    # Form Request Validation Directory
│   │       ├── Api/
│   │       │   ├── ClientHeartbeatRequest.php  # Validate timestamp, hwid format
│   │       │   └── ClientActivateRequest.php   # Validate license key format
|   |       |
│   │       ├── Auth/                    # Authentication-related request validation (Breeze-generated)
│   │       ├── LicenseRequest.php       # License-related request validation
│   │       │   # Validates data during license creation/updates.
│   │       │   # Pay special attention: License key format regex validation.
│   │       │   # Business logic validation for license status transitions.
│   │       │
│   │       ├── DeviceRequest.php        # Device-related request validation
│   │       │   # Validates HWID format during device binding.
│   │       │   # Checks if the device is already bound.
│   │       │   # Ensures a user can only bind one device.
│   │       │
│   │       └── PackageUploadRequest.php # Package upload request validation
│   │           # Validates uploaded package files.
│   │           # Checks file type, size.
│   │           # Validates version number format.
│   │
│   ├── Models/                          # Data Model Directory (already exists, quite complete)
│   │   ├── Account.php                  # Account Model - Core user account model
│   │   │   # Note: You've already implemented many useful methods, great!
│   │   │   # Pay special attention: last_ip_address uses an accessor for IP conversion.
│   │   │   # Device limit logic needs to be implemented here.
│   │   │
│   │   ├── AccountDevice.php            # Account Device Model
│   │   │   # Records user's device binding status.
│   │   │   # Needs association with the License model (via account).
│   │   │   # Note: hwid_hash should be stored using an irreversible hash.
│   │   │
│   │   ├── License.php                  # License Model
│   │   │   # Core business model!
│   │   │   # Status transitions must strictly follow the rules in the documentation.
│   │   │   # Note: The privilege field corresponds to different license levels.
│   │   │
│   │   ├── EventLog.php                 # Event Log Model
│   │   │   # Audits and tracks all important operations.
│   │   │   # Note: The details field stores JSON data, allowing flexible recording of event details.
│   │   │   # Consider log partitioning or sharding strategy (e.g., monthly).
│   │   │
│   │   ├── PackageRelease.php           # Software Package Release Model
│   │   │   # Relatively simple, mainly manages versions and download links.
│   │   │   # Consider adding a download counter field.
│   │   │
│   │   ├── ClientSession.php            # Client Session Model
│   │   │   # Used for client heartbeat and online status management.
│   │   │   # Requires regular cleanup of expired sessions.
│   │   │
│   │   └── UsageStatistic.php           # Usage Statistics Model
│   │       # Stores pre-calculated statistical data.
│   │       # Note: stat_value uses DECIMAL type, suitable for various statistical data.
│   │       # Requires scheduled background tasks to update statistics.
│   │
│   ├── Services/                        # Business Logic Service Layer Directory
|   │   ├── CryptoService.php            # Handles RSA Signing
│   │   │   # Method: signResponse(array $data): string
│   │   │   # Loads 'private.key' from storage.
│   │   │   # Uses openssl_sign to generate signature.
│   │   ├── LicenseService.php           # License-related business logic
│   │   │   # Core service! Includes license generation, validation, activation, status management.
│   │   │   # License key generation algorithm (25 uppercase alphanumeric chars, 5 groups).
│   │   │   # License activation logic (checks HWID, device limits, etc.).
│   │   │   # Complete business logic for license status transitions.
│   │   │
│   │   ├── PackageService.php           # Package management-related business logic
│   │   │   # Handles package upload, version management, download statistics.
│   │   │   # File storage and validation logic.
│   │   │   # Version number conflict checking.
│   │   │
│   │   └── StatisticsService.php        # Statistics-related business logic
│   │       # Calculates and updates various statistics.
│   │       # E.g., global login count, total usage time.
│   │       # Can set scheduled tasks for periodic updates.
│   │
│   ├── Enums/                           # Enum Class Directory
│   │   ├── LicenseStatus.php            # License Status Enum
│   │   │   # Defines: 0=unused, 1=active, 2=suspended, 3=expired, 4=upgraded, 5=revoked
│   │   │   # Used with $casts in models for type safety.
│   │   │   # Can add methods: getLabel(), getColor(), etc.
│   │   │
│   │   ├── LicensePrivilege.php         # License Privilege Enum
│   │   │   # 1 = Standard (Can be activated directly)
│   │   │   # 2 = Upgrade (Cannot be activated alone; Upgrades Std -> Ult)
│   │   │   # 3 = Ultimate (Can be activated directly)
│   │   │   # 6 = tester (Internal use, grants access to test functions)
│   │   │   # 7 = Staff/Admin (full admin access)
│   │   │
│   │   └── EventType.php                # Event Type Enum
│   │       # Defines system event types.
│   │       # E.g., account.registered, license.activated, device.bound, etc.
│   │       # Used for event classification in EventLog.
│   │
│   └── Providers/                       # Service Provider Directory
│       # Laravel service container configuration.
│       # Generally no need to modify unless registering custom services.
│       ├── AppServiceProvider.php       # Application Service Provider
│       ├── AuthServiceProvider.php      # Authentication Service Provider
│       ├── BroadcastServiceProvider.php # Broadcast Service Provider (can be deleted, not used)
│       ├── EventServiceProvider.php     # Event Service Provider
│       │   # Registers events and listeners.
│       │   # Can define: license activation events, device binding events, etc.
│       │   # Corresponding listeners handle logging, sending notifications, etc.
│       │
│       └── RouteServiceProvider.php     # Route Service Provider
│           # Route model binding configuration.
│           # Can define route model bindings, e.g., licenses/{license}
│
├── resources/views/
│   ├── auth/                           # Breeze Authentication Views (auto-generated)
│   │   # Login, registration, password reset pages.
│   │   # Can customize styles, but functional logic is not recommended for modification.
│   │
│   ├── components/                     # Reusable Components Directory
│   │
│   ├── layouts/                        # Layout Template Directory
│   │   ├── app.blade.php               # Main Layout Template
│   │   │   # Base layout for all authenticated pages.
│   │   │   # Includes navbar, sidebar (if needed), main content area.
│   │   │   # Displays different menu items based on user permissions.
│   │   │
│   │   ├── guest.blade.php             # Guest Layout Template
│   │   │   # Layout for unauthenticated pages like login, registration.
│   │   │   # Usually simple, with only logo and main content.
│   │   │
│   │   └── navigation.blade.php        # Navigation Bar Component
│   │       # Standalone navigation bar.
│   │       # Dynamically displays menus based on user permissions.
│   │       # Regular users: Dashboard, My Licenses, Device Management, Software Download.
│   │       # Administrators: All above + License Management, Package Management, System Logs.
│   │
│   ├── profile/                        # User Profile Related Views
│   │   # Breeze-generated, can be used directly.
│   │   # Users can modify personal info, password, etc.
│   │
│   ├── dashboard/                      # Dashboard Related Views
│   │   ├── index.blade.php             # Dashboard Main Page
│   │   │   # Homepage after user login.
│   │   │   # Uses user privileges to determine content.
│   │   │   # Administrators: System overview, recent activities, statistics.
│   │   │   # Regular users: Their license status, device status, recent activities.
│   │   │
│   │   ├── admin-panel.blade.php       # Administrator Panel Partial View
│   │   │   # Included in index, displays admin-only content.
│   │   │   # System statistics cards.
│   │   │   # Recent event log list.
│   │   │   # Quick actions: Create license, upload package, etc.
│   │   │
│   │   └── user-panel.blade.php        # User Panel Partial View
│   │       # Included in index, displays user content.
│   │       # License status overview.
│   │       # Device binding status.
│   │       # Available package update notifications.
│   │
│   ├── licenses/                       # License Management Related Views
│   │   ├── index.blade.php             # License List Page
│   │   │   # Administrators: View all licenses, supports filtering, search, pagination.
│   │   │   # Regular users: View only their own licenses.
│   │   │   # Display in table or card format, showing key information.
│   │   │
│   │   ├── create.blade.php            # Create New License Page (Admin only)
│   │   │   # Form: Select user (optional), license privilege level, validity period.
│   │   │   # Can pre-generate license key or leave blank for auto-generation.
│   │   │
│   │   ├── show.blade.php              # License Detail Page
│   │   │   # Displays complete license information.
│   │   │   # Includes: Basic info, status history, bound device, operation logs.
│   │   │   # Administrators: Can perform status change operations on this page.
│   │   │
│   │   └── edit.blade.php              # Edit License Page (Admin only)
│   │       # Modify license info: status, validity period, notes, etc.
│   │       # Note: Some fields (e.g., license key) cannot be modified after creation.
│   │
│   ├── devices/                        # Device Management Related Views
│   │   ├── index.blade.php             # Device List Page
│   │   │   # Displays all devices bound by the user (historical records).
│   │   │   # Includes currently active device and historical binding records.
│   │   │   # Shows device info, binding time, last activity time.
│   │   │
│   │   └── manage.blade.php            # Device Management Page
│   │       # Device binding/unbinding page.
│   │       # Displays current binding status.
│   │       # Binding form: Input HWID (usually copied/pasted from client).
│   │       # Unbind button (if already bound).
│   │       # HWID reset count and limit prompts.
│   │
│   ├── packages/                       # Package Management Related Views
│   │   ├── index.blade.php             # Package List Page
│   │   │   # Accessible to all users.
│   │   │   # Sorted by version (newest first).
│   │   │   # Displays: Version number, release channel, update date, file size.
│   │   │   # Download button (requires login and valid license).
│   │   │   # Administrators: Show delete/management buttons.
│   │   │
│   │   ├── upload.blade.php            # Upload New Package Page (Admin only)
│   │   │   # Upload form: Select file, input version number, select release channel, update notes.
│   │   │   # File validation: size, type, virus scan (optional).
│   │   │   # Version number conflict check.
│   │   │
│   │   └── version.blade.php           # Version Management Page (Admin only)
│   │       # Package version history management.
│   │       # Can rollback to old versions, set default version, etc.
│   │       # View download statistics.
│   │
│   ├── logs/                           # System Log Related Views
│   │   └── index.blade.php             # Log Viewing Page (Admin only)
│   │       # Event log list.
│   │       # Supports: Filter by time range, event type, user.
│   │       # Paginated display, each row shows event details.
│   │       # Can view event details (JSON expanded).
│   │
│   ├── welcome.blade.php               # Welcome Page / Homepage
│   │   # Homepage for unauthenticated users.
│   │   # Product introduction, feature showcase.
│   │   # Login/registration entry.
│   │   # Software feature introduction, download link (may require login).
│   │
│   └── errors/                         # Error Pages Directory
│       # Custom error pages: 404, 500, 403, etc.
│       # Keep consistent with application style.
│
└── routes/
   ├── api.php                         # API Route Definitions
   │   # Group: middleware: 'throttle:api'
   |   # POST /account/login     -> AccountController@login
   │   # POST /license/activate  -> ClientLicenseController@activate
   │   # POST /license/check     -> ClientLicenseController@check
   │   # POST /license/unbind    -> ClientLicenseController@unbind
   │   # GET  /update/check      -> ClientPackageController@check
   │
   │
   ├── web.php                         # Web Route Definitions
   │   # Most important route file.
   │   # Includes: Public routes, authenticated route groups, admin route groups.
   │   # Pay special attention to route permission control.
   │   # Use middleware to protect sensitive routes.
   │
   ├── auth.php                        # Authentication-related Routes (Breeze-generated)
   │   # Login, registration, password reset routes.
   │   # Generally no need to modify.
   │
   └── console.php                     # Artisan Command Routes
       # Defines custom Artisan commands.
       # E.g., periodic cleanup of old logs, update statistics, etc.


```

## Implementation Checklist

### Phase One: Database Migration
1. **Create migration files** (in order):
   - `accounts` table
   - `account_devices` table
   - `licenses` table (core table; pay attention to the status transition rules)
   - `event_logs` table (note JSON fields and partitioning strategy)
   - `package_releases` table
   - `client_sessions` table
   - `usage_statistics` table
2. **Key Generation**: Generate an **RSA Key Pair** (Private/Public).
* Store `private.key` securely in the Laravel `storage/` folder (DO NOT commit to Git).
* `public.key` will be embedded into the C++ Client source code later.

**Key Points**:
- Strictly follow the field types, constraints, and indexes specified in the manual
- Pay attention to foreign key relationship settings
- The `key` field in the `licenses` table must comply with the regex format

### Phase Two: Core Models and Enums
1. **Create model files**:
   - Account.php (detailed method descriptions already provided)
   - AccountDevice.php
   - License.php (core model; implement the state machine)
   - EventLog.php
   - PackageRelease.php
   - ClientSession.php
   - UsageStatistic.php

2. **Create enum classes**:
   - LicenseStatus.php (statuses 0–5)
   - EventType.php (classified system events)

### Phase Three: Business Logic Layer
1. **Create service classes**:
   - LicenseService.php (core: key generation/validation/status management)
   - PackageService.php (package management logic)
   - StatisticsService.php (statistics calculation)

2. **Create request validation classes**:
   - LicenseRequest.php (license key format validation)
   - DeviceRequest.php (HWID validation)
   - PackageUploadRequest.php (file validation)

3. **API implementation baseline (after Web Phase is complete)**
   - Register `api` routing entry in `bootstrap/app.php` and map to `routes/api.php`.

   **Error contract for `/api/license/check`**
   - Keep response shape stable as `{ code, error_code, message, data, signature, meta }`.
   - HTTP status is authoritative; `code` mirrors HTTP status.
   - Client behavior must key off `error_code`, not message text.
   - Fixed business codes: `AUTH_REQUIRED`, `NONCE_REPLAY`, `TIMESTAMP_OUT_OF_WINDOW`, `DEVICE_MISMATCH`, `LICENSE_INVALID`, `LICENSE_INEFFECTIVE`, `SERVER_ERROR`.

   **Signature contract**
   - Response keeps `{ code, error_code, message, data, signature, meta }`.
   - Signature metadata lives under `meta.signature` with `algorithm` and `key_id`.
   - Signature input is fixed: canonical JSON of `data` only.
   1) **`routes/api.php` + `ClientLicenseController@check`**
   - Implement one endpoint first: `POST /api/license/check`.
   - Keep JSON-only response contract.

   2) **`ClientHeartbeatRequest`**
   - Validate required fields: `session_token`, `license_key`, `hwid`, `nonce`, `timestamp`.
   - Keep field naming fixed as `license_key` (no `key` alias).

   3) **Replay protection (`nonce` + `timestamp`)**
   - Timestamp drift check must be within `<= 300` seconds.
   - Reject nonce reuse within 5 minutes (Redis `SET key value NX EX 300`).

   4) **`CryptoService` response signing**
   - Sign canonicalized `data` payload and return `signature`.
   - RSA-2048 (SHA-256) signing is required.
   - Return signature metadata under `meta.signature` with `algorithm` and `key_id`.

   5) **API Feature tests (6-10 cases)**
   - Must cover at least: success, nonce replay rejection, expired/out-of-window timestamp, HWID mismatch.
   - Add remaining cases up to 6-10 total based on endpoint contract and error branches.

   **`ClientLicenseController@check` required logic**
   - Validate request schema.
   - Enforce timestamp window and nonce anti-replay.
   - Resolve session by `session_token`; if missing/invalid, return `AUTH_REQUIRED`.
   - Find license by `license_key` and verify effective status.
   - Resolve account active device and compare `hwid` hash.
   - On mismatch, return `Device Mismatch` error.
   - On success, construct `data` -> sign with `CryptoService` -> return `{ code, error_code, message, data, signature, meta }`.
   - On success, update `client_sessions.last_heartbeat_at`; on failure, do not update heartbeat timestamp.

### Phase Four: Controllers and Routing
1. **Create controllers**:
   - DashboardController.php (dashboard)
   - LicenseController.php (core controller)
   - DeviceController.php
   - PackageController.php
   - LogController.php (admin only)

2. **Create middleware**:
   - AdminMiddleware.php

3. **Configure routing**:
   - In `web.php`:
     - Public routes (welcome page)
     - Authenticated user route group
     - Admin route group (using AdminMiddleware)

### Phase Five: View Construction
1. **Create view files**:
   - Layout files: `app.blade.php`, `guest.blade.php`, `navigation.blade.php`
   - Dashboard views: `dashboard/index.blade.php` + two panels
   - License management views: `index`, `create`, `show`, `edit`
   - Device management views: `index`, `manage`
   - Package management views: `index`, `upload`, `version`
   - Log views: `index` (admin only)

### Phase Six: Data Seeding
1. **Create factories and seeders**:
    - Create a Factory for each model
    - Create seeders and populate test data in order
    - Run database seeding

### Phase Seven: Testing and Verification
1. **Validate core functionalities**:
    - License generation and activation flow
    - Device binding restriction logic
    - Permission controls (user vs. admin)
    - Verify correct status transitions
