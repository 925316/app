# License Management System - Development Requirements Manual

- License Management System based on Laravel12 + Inertia(breeze) + Vue

## Database Design

laravel12 built-in authentication:
`cache`
| Name | Type | Constraints | Description |
| ---- | ---- | ---- | ---- |
| `key` | VARCHAR(255) | PK | |
| `value` | MEDIUMTEXT | NOT NULL | |
| `expiration` | INT | NOT NULL | |

`cache_locks`
| Name | Type | Constraints | Description |
| ---- | ---- | ---- | ---- |
| `key` | VARCHAR(255) | PK | |
| `owner` | VARCHAR(255) | NOT NULL | |
| `expiration` | INT | NOT NULL | |

`jobs`
| Name | Type | Constraints | Description |
| -- | ---- | ---- | ---- |
| `id` | BIGINT UNSIGNED | PK, AI | |
| `queue` | VARCHAR(255) | INDEX, NOT NULL | |
| `payload` | LONGTEXT | NOT NULL | |
| `attempts` | TINYINT UNSIGNED | NOT NULL | |
| `reserved_at` | INT UNSIGNED | NULLABLE | |
| `available_at` | INT UNSIGNED | NOT NULL | |
| `created_at` | INT UNSIGNED | NOT NULL | |

`job_batches`
| Name | Type | Constraints | Description |
| ---- | ---- | ---- | ---- |
| `id` | VARCHAR(255) | PK | |
| `name` | VARCHAR(255) | NOT NULL | |
| `total_jobs` | INT | NOT NULL| |
| `pending_jobs` | INT | NOT NULL| |
| `failed_jobs` | INT |NOT NULL | |
| `failed_job_ids` | LONGTEXT |NOT NULL | |
| `options` | MEDIUMTEXT | NULLABLE | |
| `cancelled_at` | INT | NULLABLE | |
| `created_at` | TIMESTAMP |NOT NULL | |
| `finished_at` | TIMESTAMP | NULLABLE| |

`failed_jobs`
| Name | Type | Constraints | Description |
| ---- | ---- | ---- | ---- |
| `id` | BIGINT UNSIGNED | PK, AI | |
| `uuid` | VARCHAR(255) | unique | |
| `connection` | TEXT | NOT NULL | |
| `queue` | TEXT |NOT NULL | |
| `payload` | LONGTEXT |NOT NULL | |
| `exception` | LONGTEXT | NOT NULL| |
| `failed_at` | TIMESTAMP | | |

`sessions`
| Name | Type | Constraints | Description |
| ---- | ---- | ---- | ---- |
| `id` | VARCHAR(255) | PK | |
| `user_id` | bigint | PK, NULLABLE | |
| `ip_address` | VARCHAR(45) | NULLABLE | |
| `user_agent` | TEXT | NULLABLE | |
| `payload` | LONGTEXT | NOT NULL| |
| `last_activity` | INT | NOT NULL| |

`password_reset_tokens`
| Name | Type | Constraints | Description |
| ---- | ---- | ---- | ---- |
| `email` | VARCHAR(255) | PK | Email Address |
| `token` | VARCHAR(255) | NOT NULL | Password Reset Token |
| `created_at` | TIMESTAMP | NULLABLE | Creation Time |

### `accounts`

| Name                        | Type            | Constraints               | Description                                   |
| --------------------------- | --------------- | ------------------------- | --------------------------------------------- |
| `id`                        | BIGINT UNSIGNED | PK, AI                    | Primary Key                                   |
| `username`                  | VARCHAR(255)    | UNIQUE, NOT NULL          | Username                                      |
| `email`                     | VARCHAR(255)    | UNIQUE, NOT NULL          | Email Address                                 |
| `password`                  | VARCHAR(255)    | NOT NULL                  | Laravel default bcrypt                        |
| `license_id`                | BIGINT UNSIGNED | FK`licenses.id`, NULLABLE | Privilege determines account permission level |
| `last_login_at`             | TIMESTAMP       | NULLABLE                  | Last Login Time                               |
| `last_ip_address`           | VARCHAR(45)     | NULLABLE                  | Last Login IP Address                         |
| `last_user_agent`           | TEXT            | NULLABLE                  | Last User-Agent Used                          |
| `hwid_reset_count`          | INT UNSIGNED    | DEFAULT 0                 | HWID Reset Count                              |
| `hwid_last_reset_at`        | TIMESTAMP       | NULLABLE                  | Last HWID Reset Time                          |
| `is_suspended`              | BOOLEAN         | DEFAULT FALSE             | Account Suspension Status                     |
| `suspension_reason`         | VARCHAR(255)    | NULLABLE                  | Account Suspension Reason                     |
| `suspended_until`           | TIMESTAMP       | NULLABLE                  | Suspension End Time                           |
| `email_verified_at`         | TIMESTAMP       | NULLABLE                  | Breeze Email Verification Time                |
| `two_factor_secret`         | TEXT            | NULLABLE                  | Breeze Two-Factor Secret Key                  |
| `two_factor_recovery_codes` | TEXT            | NULLABLE                  | Breeze Two-Factor Recovery Codes              |
| `two_factor_confirmed_at`   | TIMESTAMP       | NULLABLE                  | Breeze Two-Factor Confirmation Time           |
| `remember_token`            | VARCHAR(100)    | NULLABLE                  | Laravel Token                                 |
| `created_at`, `updated_at`  | TIMESTAMP       |                           | Laravel Timestamps                            |

**Optimization**

UNIQUE

- `username`
- `email`

INDEX

- `license_id`
- `email_verified_at`
- `created_at`

---

### `account_devices`

| Name                       | Type            | Constraints                         | Description                                     |
| -------------------------- | --------------- | ----------------------------------- | ----------------------------------------------- |
| `id`                       | BIGINT UNSIGNED | PK, AI                              | Primary Key                                     |
| `account_id`               | BIGINT UNSIGNED | FK`accounts.id`, NOT NULL           | Associated Account ID                           |
| `hwid_hash`                | VARCHAR(64)     | NOT NULL                            | Device Hardware ID Hash                         |
| `user_agent`               | TEXT            | NULLABLE                            | User Agent                                      |
| `ip_address`               | VARCHAR(45)     | NOT NULL                            | IP Address                                      |
| `country_code`             | CHAR(2)         | NULLABLE                            | Country Code                                    |
| `device_fingerprint`       | JSON            | NULLABLE                            | Device Characteristic Information (JSON Format) |
| `first_seen_at`            | TIMESTAMP       | DEFAULT CURRENT_TIMESTAMP, NOT NULL | First Seen Time                                 |
| `last_seen_at`             | TIMESTAMP       | DEFAULT CURRENT_TIMESTAMP, NOT NULL | Last Seen Time                                  |
| `bound_at`                 | TIMESTAMP       | NULLABLE                            | Binding Time                                    |
| `unbound_at`               | TIMESTAMP       | NULLABLE                            | Unbinding Time                                  |
| `created_at`, `updated_at` | TIMESTAMP       |                                     | Laravel Timestamps                              |

**Device Characteristic JSON Structure Example:**

- screen_resolution: Screen Resolution
- browser_plugins: Browser Plugins List
- timezone: Timezone Information
- language: Language Settings
- platform: Operating System Platform
- hardware_concurrency: CPU Core Count
- device_memory: Device Memory

**Optimization:**

UNIQUE

- `(account_id, hwid_hash)`

INDEX

- `(account_id, last_seen_at)`

---

### `licenses` `{seller_name}-{privilege}-XXXXX-XXXXX-XXXXX-XXXXX-XXXXX`(UPPERCASE)

The database should only record `XXXXX-XXXXX-XXXXX-XXXXX-XXXXX`;
everything else is for verification by the front end or the back end.
`'^[A-Z0-9]{5}-[0-9A-F]{5}-[A-Z2-7]{5}-[A-Z3-8]{5}-[A-Z0-9]{5}$'`.


| Name                      | Type             | Constraints               | Description                                                      |
| ------------------------- | ---------------- | ------------------------- | ---------------------------------------------------------------- |
| `id`                      | BIGINT UNSIGNED  | PK, AI                    | Primary Key                                                      |
| `key`                     | VARCHAR(255)     | UNIQUE, NOT NULL          | License Key                                                      |
| `type`                    | TINYINT UNSIGNED | NOT NULL, DEFAULT 1       | License Type (1=base, 2=upgrade)                                 |
| `used_by`                 | BIGINT UNSIGNED  | FK`accounts.id`, NULLABLE | Owning Account ID                                                |
| `privilege`               | TINYINT UNSIGNED | NOT NULL, DEFAULT 0       | License Tier (1=basic, 2=regular, 3=ultimate, 4=tester, 5=staff) |
| `status`                  | TINYINT UNSIGNED | DEFAULT 0                 | Current Status                                                   |
| `expires_at`              | DATETIME         | NOT NULL                  | Expiration Time (Default: now()->addYears(99))                   |
| `activated_at`            | TIMESTAMP        | NULLABLE                  | Activation Time                                                  |
| `suspended_at`            | TIMESTAMP        | NULLABLE                  | Suspension Time                                                  |
| `created_from_ip`         | VARCHAR(45)      | NULLABLE                  | Creation IP Address                                              |
| `notes`                   | TEXT             | NULLABLE                  | Administrator Notes                                              |
| `created_at`,`updated_at` | TIMESTAMP        |                           | Laravel Timestamps                                               |

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

### `upgrades`

| Name                       | Type            | Constraints                                  | Description                |
| -------------------------- | --------------- | -------------------------------------------- | -------------------------- |
| `id`                       | BIGINT UNSIGNED | PK, AI                                       | Auto-increment Primary Key |
| `account_id`               | BIGINT UNSIGNED | FK`accounts.id`, ON DELETE CASCADE, NOT NULL | Account ID                 |
| `license_from`             | BIGINT UNSIGNED | FK`licenses.id`, NOT NULL                    | License Before Upgrade     |
| `license_to`               | BIGINT UNSIGNED | FK`licenses.id`, NOT NULL                    | License After Upgrade      |
| `notes`                    | TEXT            | NULLABLE                                     | Administrator Notes        |
| `created_at`, `updated_at` | TIMESTAMP       |                                              | Laravel Timestamps         |

**Optimization**
INDEX

- `account_id`
- `updated_at`

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
| `checksum_sha256`          | CHAR(64)              | NULLABLE         | File SHA256 Checksum                 |
| `changelog`                | TEXT                  | NULLABLE         | Changelog                            |
| `download_count`           | INT UNSIGNED          | DEFAULT 0        | Download Count                       |
| `created_at`, `updated_at` | TIMESTAMP             |                  | Laravel Timestamps                   |

**Optimization**

UNIQUE

- `version`

---

### `client_sessions`

| Name                       | Type            | Constraints               | Description                |
| -------------------------- | --------------- | ------------------------- | -------------------------- |
| `id`                       | BIGINT UNSIGNED | PK, AI                    | Primary Key                |
| `session_token`            | VARCHAR(128)    | UNIQUE, NOT NULL          | Session Token              |
| `account_id`               | BIGINT UNSIGNED | FK`accounts.id`, NOT NULL | Account ID                 |
| `ip_address`               | VARCHAR(45)     | NOT NULL                  | Session IP Address         |
| `user_agent`               | TEXT            | NULLABLE                  | User-Agent                 |
| `client_version`           | VARCHAR(50)     | NOT NULL                  | Client Version             |
| `language`                 | VARCHAR(10)     | DEFAULT 'en'              | Client Language            |
| `session_data`             | JSON            | NULLABLE                  | Session Data (JSON Format) |
| `last_heartbeat_at`        | TIMESTAMP       | NULLABLE                  | Last Heartbeat Time        |
| `created_at`, `updated_at` | TIMESTAMP       |                           | Laravel Timestamps         |

**Optimization**

UNIQUE

- `session_token`

INDEX

- `last_heartbeat_at`

---

### 使用统计表 `usage_statistics`

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

### `ip_rate_limits`

| Name                       | Type            | Constraints   | Description        |
| -------------------------- | --------------- | ------------- | ------------------ |
| `id`                       | BIGINT UNSIGNED | PK, AI        | Primary Key        |
| `ip_address`               | VARCHAR(45)     | NOT NULL      | IP Address         |
| `endpoint`                 | VARCHAR(255)    | NOT NULL      | API Endpoint       |
| `request_count`            | INT UNSIGNED    | DEFAULT 1     | Request Count      |
| `last_request_at`          | TIMESTAMP       | NOT NULL      | Last Request Time  |
| `is_blocked`               | BOOLEAN         | DEFAULT FALSE | Blocked Status     |
| `blocked_until`            | TIMESTAMP       | NULLABLE      | Block End Time     |
| `block_reason`             | VARCHAR(255)    | NULLABLE      | Block Reason       |
| `created_at`, `updated_at` | TIMESTAMP       |               | Laravel Timestamps |

**Optimization**

INDEX

- `ip_address`
- `endpoint`
- `(ip_address, endpoint)`
- `(is_blocked, blocked_until)`
- `(last_request_at, endpoint)`

**Cleanup Strategy**:

- Automatically clean up non-blocked records older than 24 hours

---

# Project Structure

## Database Migrations & Seeders:

```
database/
├── migrations/
│   ├── 2025_12_18_000000_create_cache_table.php
│   ├── 2025_12_18_000001_create_cache_locks_table.php
│   ├── 2025_12_18_000002_create_jobs_table.php
│   ├── 2025_12_18_000003_create_job_batches_table.php
│   ├── 2025_12_18_000004_create_failed_jobs_table.php
│   ├── 2025_12_18_000005_create_sessions_table.php
│   ├── 2025_12_18_000006_create_password_reset_tokens_table.php
│   ├── 2025_12_18_000010_create_accounts_table.php
│   ├── 2025_12_18_000011_create_account_devices_table.php
│   ├── 2025_12_18_000012_create_licenses_table.php
│   ├── 2025_12_18_000013_create_upgrades_table.php
│   ├── 2025_12_18_000014_create_event_logs_table.php
│   ├── 2025_12_18_000015_create_package_releases_table.php
│   ├── 2025_12_18_000016_create_client_sessions_table.php
│   ├── 2025_12_18_000017_create_usage_statistics_table.php
│   └── 2025_12_18_000018_create_ip_rate_limits_table.php
├── seeders/
|   ├── AccountDeviceSeeder.php
│   ├── ClientSessionSeeder.php
│   ├── EventLogSeeder.php
│   ├── LicenseSeeder.php
│   ├── UpgradeSeeder.php
│   ├── AccountSeeder.php
│   ├── IpRateLimitSeeder.php
│   ├── PackageReleaseSeeder.php
│   ├── UsageStatisticSeeder.php
│   └── DatabaseSeeder.php
└── factories/
    ├── AccountFactory.php
    ├── AccountDeviceFactory.php
    ├── ClientSessionFactory.php
    ├── IpRateLimitFactory.php
    ├── PackageReleaseFactory.php
    ├── UsageStatisticFactory.php
    ├── EventLogFactory.php
    ├── LicenseFactory.php
    └── UpgradeFactory.php

```

## Backend Structure (Laravel)

```
app/Http/Controllers/
├── LicenseController.php
└── Admin/
    ├── DashboardController.php
    ├── LicenseAdminController.php
    └── UserAdminController.php

app/Services/
├── LicenseService.php
└── HwidService.php

app/Http/Middleware/
├── CheckLicense.php
└── CheckAdmin.php
```

## Frontend Structure (Vue 3 + TypeScript)

```
resources/js/pages/
├── licenses/
│   ├── Index.vue
│   └── Activate.vue
└── admin/
    ├── Dashboard.vue
    ├── Licenses.vue
    └── Users.vue

resources/js/components/
├── license/
│   ├── LicenseCard.vue
│   └── ActivationForm.vue
└── admin/
    └── UserTable.vue

resources/js/
└── api/
    └── license.api.js
```
