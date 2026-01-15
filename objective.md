# License Management System - Development Requirements Manual

- License Management System based on 
`Laravel 12`, `PHP 8.2+`, `Laravel breeze 2.3`, `Tailwind CSS 3`

## Database Design

### `accounts`

| Name                        | Type            | Constraints               | Description                                   |
| --------------------------- | --------------- | ------------------------- | --------------------------------------------- |
| `id`                        | BIGINT UNSIGNED | PK, AI                    | Primary Key                                   |
| `username`                  | VARCHAR(255)    | UNIQUE, NOT NULL          | Username                                      |
| `email`                     | VARCHAR(255)    | UNIQUE, NOT NULL          | Email Address                                 |
| `password`                  | VARCHAR(255)    | NOT NULL                  | Laravel default bcrypt                        |
| `last_login_at`             | TIMESTAMP       | NULLABLE                  | Last Login Time                               |
| `last_ip_address`           | VARCHAR(45)     | NULLABLE                  | Last Login IP Address                         |
| `last_user_agent`           | TEXT            | NULLABLE                  | Last User-Agent Used                          |
| `hwid_reset_count`          | INT UNSIGNED    | DEFAULT 0                 | HWID Reset Count                              |
| `hwid_last_reset_at`        | TIMESTAMP       | NULLABLE                  | Last HWID Reset Time                          |
| `is_suspended`              | BOOLEAN         | DEFAULT FALSE             | Account Suspension Status                     |
| `suspension_reason`         | VARCHAR(255)    | NULLABLE                  | Account Suspension Reason                     |
| `suspended_until`           | TIMESTAMP       | NULLABLE                  | Suspension End Time                           |
| `email_verified_at`         | TIMESTAMP       | NULLABLE                  | Laravel Email Verification Time                |
| `two_factor_secret`         | TEXT            | NULLABLE                  | Laravel Two-Factor Secret Key                  |
| `two_factor_recovery_codes` | TEXT            | NULLABLE                  | Laravel Two-Factor Recovery Codes              |
| `two_factor_confirmed_at`   | TIMESTAMP       | NULLABLE                  | Laravel Two-Factor Confirmation Time           |
| `remember_token`            | VARCHAR(100)    | NULLABLE                  | Laravel Token                                 |
| `created_at`, `updated_at`  | TIMESTAMP       |                           | Laravel Timestamps                            |

**Optimization**

UNIQUE

- `username`
- `email`

INDEX

- `email_verified_at`
- `created_at`

---

### `account_devices`

| Name                       | Type            | Constraints                         | Description                                     |
| -------------------------- | --------------- | ----------------------------------- | ----------------------------------------------- |
| `id`                       | BIGINT UNSIGNED | PK, AI                              | Primary Key                                     |
| `account_id`               | BIGINT UNSIGNED | FK`accounts.id`, NOT NULL           | Associated Account ID                           |
| `hwid_hash`                | VARCHAR(64)     | NOT NULL                            | Device Hardware ID Hash                         |
| `ip_address`               | VARCHAR(45)     | NOT NULL                            | IP Address                                      |
| `country_code`             | CHAR(2)         | NULLABLE                            | Country Code                                    |
| `first_seen_at`            | TIMESTAMP       | DEFAULT CURRENT_TIMESTAMP, NOT NULL | First Seen Time                                 |
| `last_seen_at`             | TIMESTAMP       | DEFAULT CURRENT_TIMESTAMP, NOT NULL | Last Seen Time                                  |
| `bound_at`                 | TIMESTAMP       | NULLABLE                            | Binding Time                                    |
| `unbound_at`               | TIMESTAMP       | NULLABLE                            | Unbinding Time                                  |
| `created_at`, `updated_at` | TIMESTAMP       |                                     | Laravel Timestamps                              |

**Device Characteristic JSON Structure Example:**

- {"resolution": "1920x1080", "timezone": "UTC+8", "platform": "Windows"}

**Optimization:**

UNIQUE

- `(account_id, hwid_hash)`

INDEX

- `(account_id, last_seen_at)`

---

### `licenses` `XXXXX-XXXXX-XXXXX-XXXXX-XXXXX`(ALL-UPPERCASE)

`'^[A-Z0-9]{5}-[0-9A-F]{5}-[A-Z2-7]{5}-[A-Z3-8]{5}-[A-Z0-9]{5}$'`.

| Name                      | Type             | Constraints               | Description                                                      |
| ------------------------- | ---------------- | ------------------------- | ---------------------------------------------------------------- |
| `id`                      | BIGINT UNSIGNED  | PK, AI                    | Primary Key                                                      |
| `key`                     | VARCHAR(50)     | UNIQUE, NOT NULL          | License Key                                                      |
| `type`                    | TINYINT UNSIGNED | NOT NULL, DEFAULT 1       | License Type (1=base, 2=upgrade)                                 |
| `privilege`               | TINYINT UNSIGNED | NOT NULL, DEFAULT 0       | License Tier (1=basic, 2=regular, 3=ultimate, 4=tester, 5=staff) |
| `status`                  | TINYINT UNSIGNED | DEFAULT 0                 | Current Status                                                   |
| `used_by`                 | BIGINT UNSIGNED  | FK`accounts.id`, NULLABLE | Owning Account ID                                                |
| `expires_at`              | DATETIME         | NOT NULL                  | Expiration Time (Default: now()->addDay())                   |
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
| `device_id`                | BIGINT UNSIGNED | FK`account_devices.id`, NOT NULL  | Device ID                  |
| `ip_address`               | VARCHAR(45)     | NOT NULL                  | Session IP Address         |
| `client_version`           | VARCHAR(50)     | NOT NULL                  | Client Version             |
| `last_heartbeat_at`        | TIMESTAMP       | NULLABLE                  | Last Heartbeat Time        |
| `created_at`, `updated_at` | TIMESTAMP       |                           | Laravel Timestamps         |

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

# Project Structure

## Database Migrations & Seeders:

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