# **许可证管理系统 - 开发需求手册**

## **修订记录**

| 版本 | 日期       | 描述                             | 作者            |
| ---- | ---------- | -------------------------------- | --------------- |
| 1.0  | 2025-12-06 | 初始版本，整合业务需求与技术规格 | Bela            |
| 1.1  | 2025-12-07 | 结构重组，流程细化，编码系统统一 | Bela + DeepSeek |

---

## **1. 项目概述**

### **1.1 项目目标**

构建一个安全、可扩展的软件许可证分发、激活和管理 Web 平台。系统支持单设备硬件绑定（HWID）、多层次风控监测和完整的用户自助服务，为软件开发者提供商业化的许可证管理解决方案。

### **1.2 核心特性**

-   **用户自助服务**：注册、购买（仅第三方发卡网）、激活许可证、下载软件、管理设备绑定。
-   **设备级绑定**：基于 HWID 的单设备绑定机制，支持用户自助重置（含冷却时间）。
-   **智能风控系统**：基于用户行为、地理位置和设备指纹的自动化风险评估与处置。
-   **许可证生命周期管理**：支持许可证的激活、升级、过期、封禁等全状态管理。
-   **清晰的状态传达**：通过标准化的编码系统，向用户和管理员提供明确的操作反馈与封禁理由。

### **1.3 用户角色**

-   **最终用户**：购买网站的开发者/购买软件的普通用户。
-   **管理员**：管理用户、许可证、监控系统、配置风控规则。
-   **系统**：自动化执行风控规则和后台任务。

---

## **2. 系统架构**

### **2.1 技术栈**

| 层次       | 技术选型             | 说明                                    |
| ---------- | -------------------- | --------------------------------------- |
| **前端**   | Vue 3                | 构建现代响应式用户界面                  |
|            | Inertia.js           | 实现 SPA 体验，同时享受服务端路由和验证 |
|            | Tailwind CSS         | 实用优先的 CSS 框架，快速构建定制化 UI  |
| **后端**   | Laravel 12 (PHP 8.2) | 稳健的 PHP Web 框架，提供完整的功能集   |
| **数据库** | MySQL 10.6 (MariaDB) | 关系型数据库，存储核心业务数据          |
| **缓存**   | Redis                | 用于会话、队列和高速缓存                |
| **构建**   | Node 22.21           | 前端构建工具，支持 ES6+语法             |
| **部署**   | Nginx 1.28 + PHP-FPM | Web 服务器与 PHP 进程管理器             |

### **2.2 核心数据流**

1. **用户注册与购买**：

    ```
    访客 → 访问网站 → 注册账户 → 跳转至第三方支付平台购买 → 获得许可证密钥
    ```

2. **许可证激活与使用**：

    ```
    用户登录 → 在用户面板输入密钥 → 系统激活许可证 → 用户下载软件 →
    软件启动并上报HWID → 服务器完成绑定 → 软件定期发送心跳包维持状态
    ```

3. **风控与状态管理**：
    ```
    系统监控(登录、绑定、心跳) → 触发风控规则 → 生成安全日志 →
    变更许可证状态 → 前端/软件接收状态变更通知并展示对应解释
    ```

---

## **3. 数据模型设计**

### **3.0 核心关系概览**

```
用户 (users) ──┬── 许可证 (licenses) ──┬── 设备绑定 (license_devices)
               │                        ├── 激活历史 (activation_logs)
               │                        ├── 心跳记录 (heartbeats)
               │                        └── 升级映射 (license_upgrades)
               │
               ├── 用户设备 (user_devices)   # 用于风控
               ├── 安全日志 (event_logs)    # 所有操作审计
               └── 会话 (Redis)            # 登录状态管理

其他辅助表：
├── 软件版本 (software_releases)
├── 激活密钥 (activation_keys)
└── 升级映射 (license_upgrades)
```

**核心规则**：

1. 一个用户可以拥有多个许可证
2. 一个许可证一次只能绑定一个设备（`license_devices.is_current = TRUE`）
3. 所有状态变更必须记录到 `activation_logs`
4. 所有风控和安全事件必须记录到 `event_logs`
5. `user_devices` 用于用户行为风控，不与许可证直接绑定

```


### **3.1 核心表结构**

#### **3.1.1 用户表 (`users`)**

| 字段名                     | 类型                  | 约束             | 说明                             |
| -------------------------- | --------------------- | ---------------- | -------------------------------- |
| `id`                       | BIGINT UNSIGNED       | PK, AI           | 主键                             |
| `name`                     | VARCHAR(255)          | NOT NULL         | 用户显示名称                     |
| `email`                    | VARCHAR(255)          | UNIQUE, NOT NULL | 登录邮箱，唯一标识               |
| `password`                 | VARCHAR(255)          | NOT NULL         | 加密存储（如 bcrypt）            |
| `role`                     | ENUM('user', 'admin') | DEFAULT 'user'   | 用户角色                         |
| `privilege_level`          | TINYINT UNSIGNED      | DEFAULT 0        | 权限级别（0-普通，1-VIP，2-等）  |
| `email_verified_at`        | TIMESTAMP             | NULLABLE         | 邮箱验证时间                     |
| `register_ip`              | VARCHAR(45)           | NULLABLE         | 注册时 IP 地址（IPv4/IPv6 兼容） |
| `last_login_ip`            | VARCHAR(45)           | NULLABLE         | 最后登录 IP                      |
| `last_login_at`            | TIMESTAMP             | NULLABLE         | 最后登录时间                     |
| `banned_at`                | TIMESTAMP             | NULLABLE         | 账户封禁时间                     |
| `suspension_reason`        | VARCHAR(255)          | NULLABLE         | 封禁原因描述                     |
| `migrated_id`              | VARCHAR(255)          | NULLABLE         | 从旧系统迁移时的原始 ID          |
| `created_at`, `updated_at` | TIMESTAMP             |                  | 时间戳                           |

**索引**：`email`(UNIQUE), `role`, `banned_at`, `privilege_level`.

---

#### **3.1.2 许可证表 (`licenses`)**

| 字段名                     | 类型                                                         | 约束             | 说明                                    |
| -------------------------- | ------------------------------------------------------------ | ---------------- | --------------------------------------- |
| `id`                       | BIGINT UNSIGNED                                              | PK, AI           | 主键                                    |
| `user_id`                  | BIGINT UNSIGNED                                              | FK, NULLABLE     | 所属用户 ID，激活后绑定                 |
| `license_key`              | VARCHAR(64)                                                  | UNIQUE, NOT NULL | 许可证密钥，格式由系统生成 |
| `type`                     | ENUM('basic', 'vip')                                         | NOT NULL         | 许可证类型                              |
| `status`                   | ENUM('unused', 'active', 'expired', 'suspended', 'upgraded') | DEFAULT 'unused' | 当前状态                                |
| `hwid_reset_at`            | TIMESTAMP                                                    | NULLABLE         | 上次重置 HWID 时间                      |
| `hwid_reset_count`         | INT UNSIGNED                                                 | DEFAULT 0        | HWID 重置次数，用于风控                 |
| `expires_at`               | TIMESTAMP                                                    | NOT NULL         | 过期时间，永久许可证可设为`2099-12-31`  |
| `activated_at`             | TIMESTAMP                                                    | NULLABLE         | 激活时间                                |
| `suspension_reason_code`   | CHAR(3)                                                      | NULLABLE         | 封禁事由 ID，如`110`                    |
| `abnormal_behavior_flag`   | BOOLEAN                                                      | DEFAULT FALSE    | 风控异常标记，用于内部监控              |
| `source`                   | ENUM('system', 'promotion', 'manual')                        | DEFAULT 'system' | 许可证来源                              |
| `note`                     | TEXT                                                         | NULLABLE         | 管理员备注                              |
| `previous_status`          | ENUM('unused', 'active', 'expired', 'suspended', 'upgraded') | NULLABLE         | 上一个状态，用于审计                    |
| `created_at`, `updated_at` | TIMESTAMP                                                    |                  | 时间戳                                  |

**索引**：`license_key`(UNIQUE), `user_id`, `status`, `expires_at`.

**状态流转规则**：

| 当前状态 | 可转状态 | 触发条件 | 备注 |
|---------|---------|---------|------|
| unused | active | 用户激活 | 必须验证密钥有效 |
| active | suspended | 风控触发/管理员封禁 | 记录reason_code |
| active | expired | 到达过期时间 | 定时任务处理 |
| active | upgraded | 用户升级 | 旧证不可再用 |
| suspended | active | 管理员解封 | 需人工审核 |
| expired | - | 无 | 最终状态 |
| upgraded | - | 无 | 最终状态 |

**不可逆状态**：`expired`, `upgraded`
**需人工干预状态**：`suspended` → `active`

---

#### **3.1.3 设备绑定表 (`license_devices`)**

| 字段名            | 类型            | 约束         | 说明                     |
| ----------------- | --------------- | ------------ | ------------------------ |
| `id`              | BIGINT UNSIGNED | PK, AI       | 主键                     |
| `license_id`      | BIGINT UNSIGNED | FK, NOT NULL | 许可证 ID                |
| `hwid_hash`       | VARCHAR(255)    | NOT NULL     | 设备硬件 ID 的哈希值     |
| `ip`              | VARCHAR(45)     | NULLABLE     | 首次绑定时的 IP 地址     |
| `user_agent_hash` | VARCHAR(255)    | NULLABLE     | 用户代理的哈希值         |
| `is_current`      | BOOLEAN         | DEFAULT TRUE | 是否为当前绑定设备       |
| `trusted`         | BOOLEAN         | DEFAULT TRUE | 是否可信设备（风控标记） |
| `first_seen_at`   | TIMESTAMP       | NOT NULL     | 首次绑定时间             |
| `last_seen_at`    | TIMESTAMP       | NOT NULL     | 最后活跃时间             |
| `unbound_at`      | TIMESTAMP       | NULLABLE     | 解绑时间                 |

**索引**：`license_id`, `hwid_hash`, `last_seen_at`, `is_current`.

**设备绑定规则**：
1. 一个许可证同时只能有一个`is_current = TRUE`的设备
2. 绑定新设备时，自动将旧设备标记为`is_current = FALSE`
3. 历史设备记录保留用于审计和风控分析
---

#### **3.1.4 事件日志表 (`event_logs`)**

| 字段名               | 类型                                                    | 约束         | 说明                                             |
| -------------------- | ------------------------------------------------------- | ------------ | ------------------------------------------------ |
| `id`                 | BIGINT UNSIGNED                                         | PK, AI       | 主键                                             |
| `user_id`            | BIGINT UNSIGNED                                         | FK, NULLABLE | 关联用户 ID                                      |
| `license_id`         | BIGINT UNSIGNED                                         | FK, NULLABLE | 关联许可证 ID                                    |
| `event_type`         | ENUM('security', 'activation', 'system', 'user_action') | NOT NULL     | 事件大类                                         |
| `action_code`        | VARCHAR(50)                                             | NOT NULL     | 具体动作代码，如`LICENSE_ACTIVATE`, `HWID_RESET` |
| `reason_code`        | CHAR(3)                                                 | NULLABLE     | 三位数事由 ID（风控事件专用）                    |
| `detail_id`          | VARCHAR(10)                                             | NULLABLE     | 详情 ID（风控事件专用）                          |
| `trigger_ip`         | VARCHAR(45)                                             | NULLABLE     | 触发 IP 地址                                     |
| `trigger_user_agent` | TEXT                                                    | NULLABLE     | 触发 User-Agent                                  |
| `details`            | JSON                                                    | NOT NULL     | 完整上下文信息                                   |
| `admin_id`           | BIGINT UNSIGNED                                         | FK, NULLABLE | 操作管理员 ID（手动操作时）                      |
| `performed_at`       | TIMESTAMP                                               |              | 操作时间                                         |
| `expires_at`         | TIMESTAMP                                               | NULLABLE     | 过期时间（如临时封禁）                           |
| `resolved_at`        | TIMESTAMP                                               | NULLABLE     | 问题解决时间                                     |
| `resolved_by`        | BIGINT UNSIGNED                                         | FK, NULLABLE | 解决人（管理员 ID）                              |

**索引**：`user_id`, `license_id`, `performed_at`, `event_type`, `action_code`, `reason_code`.

---

#### **3.1.5 许可证升级映射表 (`license_upgrades`)**

| 字段名           | 类型            | 约束         | 说明             |
| ---------------- | --------------- | ------------ | ---------------- |
| `id`             | BIGINT UNSIGNED | PK, AI       | 主键             |
| `old_license_id` | BIGINT UNSIGNED | FK, NOT NULL | 原基础许可证 ID  |
| `new_license_id` | BIGINT UNSIGNED | FK, NOT NULL | 新 VIP 许可证 ID |
| `upgraded_at`    | TIMESTAMP       |              | 升级时间         |

**索引**：`old_license_id`(UNIQUE), `new_license_id`(UNIQUE).

---

#### **3.1.6 激活历史表 (`activation_logs`)**

| 字段名         | 类型                                                                 | 约束         | 说明                    |
| -------------- | -------------------------------------------------------------------- | ------------ | ----------------------- |
| `id`           | BIGINT UNSIGNED                                                      | PK, AI       | 主键                    |
| `license_id`   | BIGINT UNSIGNED                                                      | FK, NOT NULL | 许可证 ID               |
| `user_id`      | BIGINT UNSIGNED                                                      | FK, NOT NULL | 用户 ID                 |
| `operation`    | ENUM('activate', 'bind', 'unbind', 'reset_hwid', 'renew', 'upgrade') | NOT NULL     | 操作类型                |
| `old_value`    | TEXT                                                                 | NULLABLE     | 操作前的值（如旧 HWID） |
| `new_value`    | TEXT                                                                 | NULLABLE     | 操作后的值（如新 HWID） |
| `ip`           | VARCHAR(45)                                                          | NULLABLE     | 操作 IP                 |
| `user_agent`   | TEXT                                                                 | NULLABLE     | 操作时的 User-Agent     |
| `performed_at` | TIMESTAMP                                                            |              | 操作时间                |

**索引**：`license_id`, `user_id`, `performed_at`.

---

#### **3.1.7 软件版本表 (`software_releases`)**

| 字段名                     | 类型            | 约束             | 说明                     |
| -------------------------- | --------------- | ---------------- | ------------------------ |
| `id`                       | BIGINT UNSIGNED | PK, AI           | 主键                     |
| `version`                  | VARCHAR(50)     | UNIQUE, NOT NULL | 版本号（遵循语义化版本） |
| `download_url`             | VARCHAR(255)    | NOT NULL         | 下载链接                 |
| `changelog`                | TEXT            | NULLABLE         | 更新日志                 |
| `force_update`             | BOOLEAN         | DEFAULT FALSE    | 是否强制更新             |
| `release_date`             | TIMESTAMP       | NOT NULL         | 发布日期                 |
| `created_at`, `updated_at` | TIMESTAMP       |                  | 时间戳                   |

**索引**：`version`(UNIQUE), `release_date`.

---

#### **3.1.8 心跳记录表 (`heartbeats`)**

| 字段名           | 类型            | 约束         | 说明            |
| ---------------- | --------------- | ------------ | --------------- |
| `id`             | BIGINT UNSIGNED | PK, AI       | 主键            |
| `license_id`     | BIGINT UNSIGNED | FK, NOT NULL | 许可证 ID       |
| `hwid`           | VARCHAR(255)    | NOT NULL     | 设备硬件 ID     |
| `ip`             | VARCHAR(45)     | NULLABLE     | 心跳 IP         |
| `user_agent`     | TEXT            | NULLABLE     | 心跳 User-Agent |
| `client_version` | VARCHAR(50)     | NOT NULL     | 客户端版本号    |
| `received_at`    | TIMESTAMP       | NOT NULL     | 心跳时间        |

**索引**：`license_id`, `received_at`, `hwid`.

---

### **3.1.9 会话管理（Redis）**

- **存储结构**：使用 Redis 存储用户会话，键格式：`session:{token}`
- **会话数据**：包含 `user_id`、`license_id`（如有）、`device_info`、`expires_at`
- **过期时间**：24小时自动过期
- **审计记录**：在 `event_logs` 中记录详细事件

**说明**：删除原 sessions 表，使用 Redis 提高性能并减少数据库压力。软件端使用短期令牌时，建议 Redis 键格式为 `client_token:{token}`，过期时间2小时。

---

#### **3.1.10 用户设备表 (`user_devices`)**

| 字段名               | 类型             | 约束         | 说明                             |
| -------------------- | ---------------- | ------------ | -------------------------------- |
| `id`                 | BIGINT UNSIGNED  | PK, AI       | 主键                             |
| `user_id`            | BIGINT UNSIGNED  | FK, NOT NULL | 用户 ID                          |
| `device_fingerprint` | VARCHAR(255)     | NOT NULL     | 设备指纹哈希（综合 HWID、IP 等） |
| `ip_country`         | VARCHAR(2)       | NULLABLE     | 设备首次出现国家代码             |
| `trust_score`        | TINYINT UNSIGNED | DEFAULT 100  | 设备信任评分（0-100）            |
| `first_seen_at`      | TIMESTAMP        | NOT NULL     | 首次出现时间                     |
| `last_seen_at`       | TIMESTAMP        | NOT NULL     | 最后活跃时间                     |

**索引**：`user_id`, `device_fingerprint`, `last_seen_at`, `trust_score`.

---

#### **3.1.11 激活密钥表 (`activation_keys`)**

| 字段名                     | 类型                       | 约束             | 说明                       |
| -------------------------- | -------------------------- | ---------------- | -------------------------- |
| `id`                       | BIGINT UNSIGNED            | PK, AI           | 主键                       |
| `key`                      | VARCHAR(64)                | UNIQUE, NOT NULL | 激活密钥                   |
| `type`                     | ENUM('license', 'upgrade') | NOT NULL         | 密钥类型                   |
| `target_license_type`      | ENUM('basic', 'vip')       | NULLABLE         | 目标许可证类型             |
| `value`                    | INT UNSIGNED               | NULLABLE         | 面值（如充值天数、积分等） |
| `used_by`                  | BIGINT UNSIGNED            | FK, NULLABLE     | 使用用户 ID                |
| `used_at`                  | TIMESTAMP                  | NULLABLE         | 使用时间                   |
| `expires_at`               | TIMESTAMP                  | NULLABLE         | 密钥过期时间               |
| `created_at`, `updated_at` | TIMESTAMP                  |                  | 时间戳                     |

**索引**：`key`(UNIQUE), `type`, `used_by`, `expires_at`.

---

### **3.2 数据字典（枚举值说明）**

#### **3.2.1 用户角色 (`users.role`)**

-   `user`：普通用户。
-   `admin`：管理员。

#### **3.2.2 许可证类型 (`licenses.type`)**

-   `basic`：基础版。
-   `vip`：VIP 版。

#### **3.2.3 许可证状态 (`licenses.status`)**

-   `unused`：未使用。
-   `active`：已激活，正常使用中。
-   `expired`：已过期。
-   `suspended`：已封禁。
-   `upgraded`：已升级（原许可证被升级，不可再使用）。

#### **3.2.4 安全日志操作类型 (`security_logs.action_type`)**

-   `auto_ban`：系统自动封禁。
-   `auto_warn`：系统自动警告。
-   `manual_ban`：管理员手动封禁。
-   `manual_warn`：管理员手动警告。
-   `system_alert`：系统告警（仅记录，不直接对用户操作）。

#### **3.2.5 激活历史操作类型 (`activation_logs.operation`)**

-   `activate`：激活许可证。
-   `bind`：绑定设备。
-   `unbind`：解绑设备。
-   `reset_hwid`：重置 HWID。
-   `renew`：续期。
-   `upgrade`：升级。

#### **3.2.6 激活密钥类型 (`activation_keys.type`)**

-   `license`：用于激活新许可证。
-   `upgrade`：用于升级现有许可证。
-   `renewal`：用于续期现有许可证。

---

### **3.3 “事由 ID”编码系统**

> 说明：为确保后端风控、API 返回与前端显示的一致性，事由编码不仅用于日志记录，也会映射为 API 错误/状态码以及前端的提示 key。

#### **3.3.1 编码格式**

-   **主编码 (`reason_code`)**：三位数字 `ABC`
    -   `A` (操作大类): `1` = 封禁 (`ban`), `2` = 警告 (`warn`)
    -   `B` (违规分类): `1`=违规使用, `2`=账户安全, `3`=滥用行为
    -   `C` (具体条例): 从 0 开始的序号。
-   **详情编码 (`detail_id`)**：字符串，用于在同一`reason_code`下提供更具体的场景区分。例如：`"HWID_MISMATCH"`, `"MULTI_COUNTRY_24H"`。

#### **3.3.2 编码示例 与 API/前端的映射规则**

1. **错误级别/自动行为**：当 `reason_code` 属于封禁类（A=1），后端应返回明确的 `action_type`（auto_ban/manual_ban），并在 `security_logs` 中记录是否允许申诉与申诉入口信息。
2. **示例常用映射（建议作为启动集）**
   | `reason_code` | `detail_id` | 前端解释（示例） | 触发场景 |
   |---------------|-------------|------------------|----------|
   | `110` | `INTEGRITY_FAIL` | 许可证因软件完整性验证失败被封禁。 | 软件检测到篡改。 |
   | `120` | `WEB_INJECTION` | 许可证因检测到网页注入行为被封禁。 | 前端代码被篡改。 |
   | `121` | `MULTI_COUNTRY_24H` | 账户因 24 小时内在多个国家登录被暂时保护。 | 触发异地登录规则。 |
   | `122` | `HWID_MISMATCH` | 许可证因尝试绑定多个设备被封禁。 | 同一许可证在不同 HWID 设备登录。 |
   | `221` | `UNVERIFIED_EMAIL` | 请验证您的邮箱以确保账户安全。 | 账户邮箱未验证。 |
   | `231` | `RATE_LIMIT_EXCEEDED` | 您的请求过于频繁，请稍后再试。 | 短时间内多次调用敏感 API。 |

#### **3.3.3 使用规则**

1. **记录**：任何导致许可证状态变为 `suspended` 或生成警告的操作，都必须在 `security_logs` 表中记录 `reason_code` 和 `detail_id`。
2. **关联**：若操作导致许可证封禁，需同时更新 `licenses.suspension_reason_code` 字段。
3. **前端映射**：在 Vue 应用内维护一个常量映射对象（如 `reasonMap.js`），将 `reason_code` 和 `detail_id` 的组合映射为对用户友好的多语言文本。**此映射不存储在数据库中**，便于前端独立维护和更新。

---

### **3.4 索引策略**

#### **用户表索引策略**

1. 主键 `id`。
2. 唯一索引 `email` 用于登录和用户识别。
3. 索引 `role` 用于角色筛选。
4. 索引 `banned_at` 用于快速查询被封禁用户。
5. 索引 `privilege_level` 用于权限分级查询。

#### **许可证表索引策略**

1. 主键 `id`。
2. 唯一索引 `license_key` 用于激活和查询。
3. 复合索引 `(user_id, status)` 用于用户查看自己的许可证列表。
4. 索引 `expires_at` 用于定时任务清理过期许可证。
5. 索引 `hwid` 用于设备绑定查询和风控。
6. 索引 `status` 用于状态筛选。

#### **设备绑定表索引策略**

1. 主键 `id`。
2. 索引 `license_id` 用于查询某个许可证的所有绑定设备。
3. 索引 `hwid_hash` 用于追踪设备使用情况。
4. 索引 `last_seen_at` 用于清理老旧设备记录。

#### **风控与审计日志表索引策略**

1. 主键 `id`。
2. 索引 `user_id` 用于查询用户的所有安全事件。
3. 索引 `license_id` 用于查询许可证的安全事件。
4. 复合索引 `(performed_at, action_type)` 用于时间范围和类型筛选。
5. 索引 `reason_code` 用于统计和分析常见事由。
6. 索引 `expires_at` 用于自动解除临时封禁。

#### **激活历史表索引策略**

1. 主键 `id`。
2. 索引 `license_id` 用于查询某个许可证的所有操作历史。
3. 索引 `user_id` 用于查询用户的所有操作历史。
4. 索引 `performed_at` 用于按时间排序和范围查询。

#### **心跳记录表索引策略**

1. 主键 `id`。
2. 索引 `license_id` 用于查询某个许可证的心跳记录。
3. 复合索引 `(received_at, hwid)` 用于时间范围和设备分析。
4. 按时间分区：建议按月分区，提升查询性能并便于归档。

#### **会话表索引策略**

1. 主键 `id`。
2. 唯一索引 `token` 用于会话验证。
3. 索引 `user_id` 用于查询用户的所有会话。
4. 索引 `expires_at` 用于自动清理过期会话。

#### **激活密钥表索引策略**

1. 主键 `id`。
2. 唯一索引 `key` 用于密钥验证。
3. 索引 `type` 用于按类型筛选。
4. 索引 `used_by` 用于查询用户使用的密钥。
5. 索引 `expires_at` 用于清理过期密钥。

---

### **3.5 数据保留与归档策略**

#### **永久保留数据**

-   **用户表**：永久保留，但可软删除。
-   **许可证表**：永久保留，但可将过期超过一年的记录标记为历史状态。

#### **定期保留数据（保留期限）**

-   **设备绑定表**：保留最近 2 年记录，过期记录移至历史表。
-   **风控与审计日志表**：核心数据保留 180 天，过期数据迁移至历史库。
-   **激活历史表**：保留最近 2 年记录，过期记录移至历史表。
-   **心跳记录表**：保留最近 30 天，按月分区，过期数据自动删除。
-   **会话表**：会话过期后保留 7 天用于审计，然后删除。
-   **激活密钥表**：使用后保留 1 年，未使用且过期的密钥可定期清理。

#### **归档策略**

-   所有移至历史库的数据应压缩存储，并建立相应的历史查询接口。
-   归档操作应在业务低峰期（如凌晨）通过定时任务执行。

## **4. 核心业务流程**

### **4.1 许可证激活流程**

**用户目标**：将购买的许可证密钥与自己的账户绑定。

1. **用户操作**：登录后，在用户面板的"激活许可证"区域输入完整的许可证密钥。
2. **服务端验证**：
   a. 查询 `licenses` 表，确保密钥存在、状态为 `unused`、且 `expires_at` 在未来。
3. **执行激活**：
   a. 更新 `licenses` 记录：`user_id` = 当前用户 ID, `status` = `'active'`, `activated_at` = NOW()。
   b. 在 `event_logs` 中记录激活操作（`event_type`=`activation`, `action_code`=`LICENSE_ACTIVATE`）。
4. **响应**：返回成功消息，并引导用户下载软件。

### **4.2 HWID 绑定与验证流程（软件端）**

**用户目标**：在软件中登录账户，静默完成设备绑定。
1. **用户操作**：在已安装的软件中输入网站注册的邮箱和密码进行登录。仅限账户密码。
2. **软件行为**：调用服务端 **`/api/client/auth/login`** 接口，上报邮箱、密码与 HWID。

### **4.3 HWID 重置流程（用户自助）**

**用户目标**：解绑当前设备，以便在新设备上使用。

1. **用户操作**：在网站用户面板的许可证卡片上点击“重置 HWID”按钮。
2. **服务端验证**：
   a. 确认用户拥有 `active` 状态的许可证。
   b. 检查 `licenses.hwid_reset_at` 字段，确保距离上次重置已超过 **72 小时** 冷却期。
3. **执行重置**：
   a. 更新 `licenses` 记录：`hwid` = NULL, `hwid_bound_at` = NULL, `hwid_reset_at` = NOW()。
   b. 在 `activation_logs` 中记录 `reset_hwid` 操作。
4. **响应**：提示重置成功。许可证状态变为“未绑定设备”，用户需在新设备的软件中重新登录以绑定。

### **4.4 许可证升级流程**

**用户目标**：将已有的基础版许可证升级为 VIP 版。

1. **用户操作**：在用户面板的"升级许可证"页面，输入VIP许可证密钥
2. **服务端验证**：
   a. 验证新密钥：存在、状态为`unused`、类型为`vip`、未过期
   b. 验证旧许可证：属于当前用户、状态为`active`、类型为`basic`
3. **执行升级**：
   a. **旧证处理**：
     - 状态更新为`'upgraded'`
     - 解绑所有设备（`license_devices.is_current = FALSE`）
     - 停止接受心跳（返回`ERR_LICENSE_UPGRADED`）
   b. **新证激活**：
     - 绑定当前用户，状态更新为`'active'`
     - 继承旧证的有效期（或按新证规则）
   c. **设备绑定**：用户需要在软件中重新登录绑定新许可证
   d. **记录关系**：在`license_upgrades`表中插入映射记录
   e. 在`event_logs`中记录升级操作
4. **响应**：返回升级成功消息，提示用户重新登录软件

### **4.5 风控自动处理流程**

#### **风控执行顺序**
```

登录/激活请求 → IP 检查 → 频率检查 → HWID 检查 → 账户状态检查 → 许可证状态检查

```

#### **预设规则库（可按配置开关）**

- **规则 R1：异地登录保护**（开关：`risk.rule.multi_country`）
  - **描述**：24小时内，同一账户从超过3个不同国家登录
  - **动作**：自动封禁账户下所有许可证
  - **配置**：`country_limit=3`, `time_window=24h`

- **规则 R2：HWID 冲突检测**（开关：`risk.rule.hwid_mismatch`）
  - **描述**：1小时内，同一许可证在不同HWID设备上登录
  - **动作**：自动封禁该许可证
  - **配置**：`time_window=1h`

- **规则 R3：高频异常请求**（开关：`risk.rule.rate_limit`）
  - **描述**：5分钟内，同一IP对敏感接口发起超过10次失败请求
  - **动作**：临时冻结15分钟
  - **配置**：`max_attempts=10`, `window=5m`, `freeze=15m`

#### **风控配置方式**
1. **配置文件**：`config/risk.php` 定义默认规则和阈值
2. **数据库配置**：`risk_rules` 表（可选，如需动态调整）
3. **优先级**：数据库配置 > 配置文件 > 默认值

#### **风控执行后流程**
1. **状态同步**：许可证状态变更后，所有相关请求返回对应状态码
2. **前端解释**：根据 `suspension_reason_code` 和 `detail_id` 显示对应解释
3. **申诉渠道**：在封禁提示中提供联系客服的链接
```

---

# 5. 接口设计（重写替换版）

示例项目结构：

```

app/
├── Models/
│ ├── User.php
│ ├── License.php
│ ├── LicenseDevice.php
│ ├── SecurityLog.php
│ ├── ActivationLog.php
│ ├── SoftwareRelease.php
│ └── ActivationKey.php
│
├── Http/
│ ├── Controllers/
│ │ ├── Web/
│ │ │ ├── DashboardController.php
│ │ │ ├── LicenseController.php ← 网站用户操作
│ │ │ ├── ProfileController.php
│ │ │ └── ReleaseController.php
│ │ ├── Client/
│ │ │ ├── AuthController.php ← 软件端登录接口
│ │ │ └── HeartbeatController.php
│ │ └── Admin/
│ │ ├── UserController.php
│ │ ├── LicenseController.php
│ │ └── SecurityController.php
│ │
│ ├── Requests/
│ │ ├── License/
│ │ │ ├── ActivateRequest.php
│ │ │ ├── UpgradeRequest.php
│ │ │ └── ResetHwidRequest.php
│ │ ├── Client/
│ │ │ ├── ClientLoginRequest.php
│ │ │ └── HeartbeatRequest.php
│ │ └── Admin/...
│ │
│ └── Middleware/
│ ├── VerifySignature.php ← 软件端 HMAC 验证
│ ├── ThrottleKeyActions.php
│ └── AdminAccess.php
│
├── Services/
│ ├── LicenseService.php ← 激活、升级、绑定逻辑
│ ├── HwidService.php
│ ├── RiskControlService.php
│ └── ClientAuthService.php
│
├── Actions/
│ ├── ActivateLicense.php
│ ├── ResetHwid.php
│ ├── BindHwid.php
│ ├── SuspendLicense.php
│ └── GenerateSecurityLog.php
│
└── Policies/

```

## 5.0 通用约定（API 风格与返回包）

-   **基地址**：所有接口以 `/api` 前缀；管理后台以 `/admin/api` 前缀，客户端（软件）以 `/api/client` 前缀。

-   **HTTP 状态码**：API 使用标准 HTTP 状态码：

    -   200：业务成功（包括部分警告，但请求被正确处理）
    -   400：请求格式或参数错误
    -   401：未认证（Session 过期或 Signature 校验失败）
    -   403：无权限或被封禁
    -   404：资源不存在
    -   429：速率限制触发
    -   500：服务器错误

-   **鉴权**：

    -   网站用户 API：基于 Laravel Session + CSRF（浏览器）
    -   软件客户端 API：必须使用 HMAC-SHA256 签名，头部包含：
        -   `X-License-Key`：许可证密钥
        -   `X-Timestamp`：UTC 秒级时间戳
        -   `X-Signature`：HMAC-SHA256 签名
    -   签名算法：`HMAC-SHA256(请求体序列化 + timestamp, license_secret)`
    -   签名验证失败返回 401

-   **速率限制（示例策略，需按环境调整）**：

    -   认证、激活、重置类敏感接口：每 IP 每分钟 5 次失败尝试限流；连续失败触发 R3 风控（见 4.5）。
    -   心跳接口：每设备默认不得超过每 10 秒一次（后端可按 token 限制）。

-   **审计与日志**：任何对许可证状态变更（激活/绑定/重置/封禁/续期）必须写入 `activation_logs` 和 `security_logs`（如触发风控）。

---

## 5.1 网站用户 API（Session 验证，适用于前端面板）

> 说明：下列为用户面板主要端点，每个端点列出：认证、请求字段、校验、响应关键字段、常见错误与风控触发点。

### `/api/user/licenses` — 获取当前用户的许可证列表

-   **方法**：GET
-   **认证**：Laravel Session
-   **请求参数**：page（可选）、limit（可选）
-   **返回 data 字段示例说明**：列表中每个许可证包含 `license_id`、`license_key`（部分脱敏）、`type`、`status`、`expires_at`、`hwid_bound`（布尔）、`hwid_reset_at`、`hwid_reset_cooldown_seconds`、`suspension_reason_code` 与 `suspension_detail_id`（若存在）
-   **常见错误**：401（未登录）、403（账号被封禁）
-   **风控**：无（只读）

### `/api/license/activate` — 激活许可证（用户面板）

-   **方法**：POST
-   **认证**：Session
-   **请求字段**：

    -   `license_key`：字符串，必填，格式校验（正则或分段格式）
    -   `agree_tos`：布尔，可选（如购后需同意条款）

-   **校验要点**：

    -   密钥存在、状态为 `unused`、未过期
    -   用户未超出持证数量（如一人多证政策）

-   **成功响应 data**：`license_id`、`status`（active）、`activated_at`、`hwid_bound`（false）
-   **失败/错误**：

    -   400：`ERR_LICENSE_INVALID`（密钥格式不符或不存在）
    -   403：`ERR_LICENSE_ALREADY_USED`（已被其他账户激活）
    -   429：`WARN_RATE_LIMIT`（频率限制）

-   **审计**：写 `activation_logs`。若激活行为异常（大批量激活来自同一 IP），触发 R3。

### `/api/license/upgrade` — 使用 VIP 密钥升级当前基础许可证

-   **方法**：POST
-   **认证**：Session
-   **请求字段**：

    -   `new_license_key`：字符串，必填
    -   `target_license_id`：数值，必填（要升级的旧证 ID）

-   **逻辑要点**：

    -   验证旧证属于当前用户且处于 `active` 且为可升级类型（basic）
    -   验证 `new_license_key` 为 `unused`、类型为 `vip`
    -   升级策略：旧证状态设为 `upgraded`；新证绑定用户、状态 `active`，清空 HWID 以强制重新绑定（记录在 activation_logs）；同时写入 license_upgrades

-   **响应**：升级成功与新证信息、提示用户需要在客户端重新登录绑定
-   **错误**：

    -   400：`ERR_UPGRADE_INVALID`（不满足升级条件）
    -   403：`ERR_UPGRADE_DENIED`（权限/状态问题）

-   **审计**：写 `activation_logs` 与 `license_upgrades`。

### `/api/license/reset-hwid` — 用户自助重置 HWID（有冷却）

-   **方法**：POST
-   **认证**：Session
-   **请求字段**：`target_license_id` 必填
-   **校验**：

    -   证属于用户且处于 `active`
    -   与 `licenses.hwid_reset_at` 比较，必须超过冷却期（默认 72 小时，可配置）

-   **执行**：

    -   将 license 的 hwid 字段置空、更新 `hwid_reset_at`、`hwid_reset_count`++，写入 activation_logs

-   **返回**：操作成功的提示与新冷却截止时间（或剩余秒数）以便前端展示倒计时
-   **错误/风控**：

    -   400：`ERR_HWID_RESET_TOO_SOON`（冷却中）
    -   429：`WARN_RATE_LIMIT`
    -   若频繁 reset（超过策略阈值），触发 R3 与可能的 `security_logs` 警告

### `/api/releases` — 获取软件版本列表

-   **方法**：GET
-   **认证**：公开或 Session（公开接口用于浏览器下载页面）
-   **返回 data**：版本数组，包含 `version`、`download_url`、`changelog`、`force_update`、`release_date`
-   **风控**：无

### `/api/security-logs` — 获取当前用户相关安全日志（警告/提示）

-   **方法**：GET
-   **认证**：Session
-   **参数**：page, limit, filter[action_type] 可选
-   **返回**：列表每项带 `performed_at`、`reason_code`、`detail_id`、`message_key`、`expires_at`（如有）
-   **注意**：对 detail 文本的真实解释以前端 `reasonMap` 为准

---

## 5.2 软件客户端 API（基于许可证签名认证 —— **核心接口需非常严格**）

> 重要：客户端 API 的错误会直接影响软件可用性与营收。签名、时戳、防重放、速率都必须严格实现。

#### 认证规范（总体）

-   请求头要求：`X-License-Key`、`X-Timestamp`、`X-Signature`
-   时间戳误差窗口：±120 秒，超出返回 401 并记录异常
-   每个许可证在数据库保存 `license_secret`

### `/api/client/auth/login` — 软件登录与 HWID 绑定/验证（**核心**）

-   **方法**：POST
-   **认证**：X-License-Key + X-Signature
-   **请求体字段**：

    -   `email`：用户邮箱，必填
    -   `password`：用户密码，必填
    -   `hwid`：设备 HWID 字符串，必填
    -   `client_info`：对象，可包含 `os`, `app_version`, `user_agent` 等（可选）

-   **后端处理要点（详列）**：

    1. 验证签名与时间戳 → 401 若失败
    2. 验证用户邮箱密码 → 401 若失败（写入 security_logs 并计入速率限制）
    3. 查找该用户拥有的有效许可证（或指定 license_id） → 403 若无有效许可证
    4. HWID 判定分三种场景（务必明确返回 status 字段）：

        - **绑定场景（license.hwid 为空）**：写入 hwid、hwid_bound_at、返回 status=`bound`、session_token、expires_at
        - **一致场景（一致）**：更新最后活跃时间、返回 status=`ok`、session_token
        - **冲突场景（不一致）**：记录 security_logs，执行 R2 风控动作（视策略可直接 suspend 或返回提示要求重置），返回 403，并同时返回 `reason_code`=`202`、`detail_id`=`HWID_MISMATCH`、code=`ERR_HWID_CONFLICT`

    5. 记录 activation_logs（操作类型：bind 或 login）

-   **成功返回 data**（字段说明）：`status`（bound/ok）、`license_type`、`license_id`、`expires_at`、`session_token`（短期，用于心跳）、`server_time`
-   **错误与风控**：

    -   403 + reason_code 202：HWID 冲突（ERR_HWID_CONFLICT）
    -   429：若短时间内大量失败尝试，触发 R3（ERR_RATE_LIMIT）
    -   被 suspend 的 license：返回 403，包含 `suspension_reason_code` 与 `message_key`

### `/api/client/auth/heartbeat` — 心跳包

-   **方法**：POST（或 GET 简化检查更新）
-   **认证**：同上，或使用 `session_token`（若 server 支持）
-   **请求体字段**：`hwid`、`version`、`session_token`（或由头部鉴权）
-   **行为**：

    -   接收心跳，更新 `heartbeats` 表（或按采样写入），返回 `ok` 或 `suspended` 状态
    -   若有强制更新，返回 `force_update=true` 与 `latest_version` 和 `download_url`
    -   若 license 被 suspend，返回 403 并包含 `reason_code` 与 `message_key`

-   **速率**：建议默认最小间隔 10 秒；超频返回 429 并写入 `security_logs`（触发 R3）

### `/api/client/releases/latest` — 检查最新版本

-   **方法**：GET
-   **认证**：可选（若需要返回针对 license 的强制更新策略则需认证）
-   **返回 data**：`version`、`download_url`、`changelog`、`force_update`
-   **注意**：如 `force_update=true`，客户端必须强制提示并阻止继续使用旧版本（视产品策略）。

---

## 5.3 后台管理 API（管理员权限）

> 管理 API 均需管理员 Session + 强校验。敏感操作（封禁、重置 HWID、手动恢复）必须记录 admin_id 并写入 security_logs 与 activation_logs。

### 典型端点（每个端点需完善请求字段与审计记录）

-   `/admin/api/users` — GET 列表、POST 创建（管理员）
-   `/admin/api/users/{id}/ban` — POST，需 `reason_code`、`detail_id`、可选 `expires_at`
-   `/admin/api/licenses` — GET，查询与筛选
-   `/admin/api/licenses/{id}/suspend` — POST，需 `reason_code`、`detail_id`
-   `/admin/api/licenses/{id}/reset-hwid` — POST，无视冷却（必须写 admin 操作日志）
-   `/admin/api/security-logs` — GET，高级查询、导出
-   `/admin/api/software-releases` — POST，发布新版本（支持上传或外链）

**重要合规点**：

-   后台强制二次确认机制：对于不可逆或影响用户长期可用性的动作（如永久封禁、永久清除 license），UI 需提示风险，API 需提供 `force=true` 参数并记录管理员输入的确认理由。
-   所有管理行为必须具备导出审计记录能力（按操作、时间、管理员查询并导出 CSV/JSON）。

---

## 5.4 错误码与前端展示的对接（统一约定）

-   后端应提供稳定的 `code`（短字符串）与 `message_key`，供前端按语言表展示。不要在生产流程中直接显示后端 `message`。
-   **示例短码与 HTTP 映射（建议）**：

    -   `ERR_HWID_CONFLICT` → HTTP 403，包含 `reason_code`=`202`、`detail_id`=`HWID_MISMATCH`
    -   `ERR_LICENSE_INVALID` → HTTP 400
    -   `ERR_LICENSE_ALREADY_USED` → HTTP 403
    -   `ERR_LICENSE_TAMPER` → HTTP 403，`reason_code`=`110`
    -   `WARN_RATE_LIMIT` → HTTP 429，`reason_code`=`231`（可选）
    -   `ERR_SIGNATURE_INVALID` → HTTP 401

-   前端应依据 `message_key` + 本地化文案显示用户可理解信息，并在需要时提供“申诉/联系客服”链接（由后端在 `security_logs` 中写入申诉信息或在响应中包含 `appeal_url` 字段）。

---

## 5.5 设计注意事项（落地提示）

1. **幂等设计**：对会造成状态变更的 POST 接口（如激活、重置）建议支持幂等键或幂等检查以防止重复提交。
2. **最小化暴露敏感字段**：license_key 在列表显示时应脱敏（前后端约定脱敏策略）。
3. **审计不可绕过**：所有会变更许可证状态的 API 必须记录 acting_user/admin_id、ip 与 user_agent 快照。
4. **速率与风控联动**：API 层和风控服务需要共享速率/异常策略（例如 Redis 计数器）。
5. **测试与模拟**：为关键场景（大量激活、频繁重置、并发心跳）准备模拟脚本与负载测试，确保风控规则不会误伤正常用户。

---

# 6. 前端页面与交互（重写替换版）

示例项目结构：

```

resources/js/
├── Pages/
│ ├── Home/
│ │ └── Index.vue
│ ├── Pricing/
│ │ └── Index.vue
│ ├── Dashboard/
│ │ ├── Index.vue
│ │ ├── Components/
│ │ │ ├── LicenseCard.vue
│ │ │ ├── HwidResetDialog.vue
│ │ │ ├── LicenseStatusBadge.vue
│ │ │ └── ActivationLogTable.vue
│ │ └── Modals/
│ │ └── UpgradeModal.vue
│ ├── Auth/...
│ └── Admin/
│ ├── Users.vue
│ ├── Licenses.vue
│ ├── SecurityLogs.vue
│ └── Releases.vue
│
├── Components/
│ ├── Navigation/
│ ├── Alerts/
│ ├── Buttons/
│ └── Cards/
│
├── Stores/
│ └── user.js
│
├── Composables/
│ ├── useCooldown.js
│ ├── useLicenseState.js
│ └── useSuspensionReason.js
│
└── Utils/
└── reasonMap.js ← detail_id 与提示文案映射

```

## 6.0 全局规范（替换与细化）

-   **导航栏**：组件化，展示登录态后用户头像下拉（含 “仪表盘 / 文档 / 注销”）。导航链接应对 SEO 页面（首页/定价/文档）采用服务端渲染或 SSR-friendly Inertia 路由。
-   **页脚与条款**：页脚应包含“服务条款、隐私政策、退款说明”链接，签署购买时必须用户勾选并前端保存同意快照（用于争议证据）。
-   **响应式与可访问性**：重要控件需支持键盘与屏幕阅读器；颜色对比满足 WCAG AA。
-   **本地化**：前端文本使用国际化文件（message_key 从后端返回），并保持 `reasonMap` 与后端 `reason_code` 对齐。

## 6.1 页面与组件清单（文件结构示意与职责说明）

（下为目录示意与关键文件的功能说明，便于你将来替换 resources/js/Pages/\*）

-   `Pages/Home/Index.vue`

    -   Hero、功能卡、购买 CTA（跳转外部支付），产品介绍，展示系统核心功能

-   `Pages/Pricing/Index.vue`

    -   周期选择器（month/90/lifetime），价格动态切换（页面本地计算），购买按钮触发后端创建订单或跳转第三方支付。props：plans、pricing_rules

-   `Pages/Dashboard/Index.vue`（**关键页面**）

    -   **接收 props**（Inertia 提供）：

        -   `user`（基本信息、privilege_level）
        -   `licenses`（数组，每项包含：license_id, license_key_masked, type, status, expires_at, hwid_bound:boolean, hwid_reset_at, hwid_reset_cooldown_seconds, suspension_reason_code, suspension_detail_id）
        -   `releases`（latest release info）
        -   `activation_logs_page`（第一页激活历史）

    -   **子组件**：

        -   `LicenseCard`（每个许可证一张卡）

            -   显示：产品名、类型、状态徽章、有效期、脱敏 HWID、重置按钮（显示冷却倒计时）、升级按钮（若 basic）
            -   状态徽章（LicenseStatusBadge）：根据 status（active/expired/suspended/upgraded）展示颜色与 tooltip（tooltip 使用 message_key 返回本地化文案）
            -   操作确认：重置 HWID 弹窗（HwidResetDialog）包含冷却说明、二次确认、并展示预计生效时间

        -   `ActivationLogTable`：折叠式，分页加载
        -   `SecurityBanner`：若有 `suspension_reason_code` 展示顶部警示条，包含 `message_key` 本地化文本与 “申诉” 链接（若后端返回 `appeal_url`，优先使用）

    -   **行为契约**：

        -   点击 “重置 HWID” 发起 `/api/license/reset-hwid`，成功则局部刷新该许可证数据并在 LicenseCard 中显示新的冷却倒计时
        -   点击 “升级” 打开 `UpgradeModal`，提交后刷新 licenses 列表
        -   Download 按钮触发浏览器下载或跳转到 /api/releases 的对应 URL

-   `Pages/Docs/Index.vue`

    -   左侧树形目录（服务端渲染内容目录），右侧内容区支持版本切换与代码块（仅文档，非接口执行）

-   `Pages/Admin/*`

    -   Users.vue、Licenses.vue、SecurityLogs.vue、Releases.vue：每个页面接收分页数据与过滤条件，管理操作（封禁/解除/重置）均弹出二次确认并要求选择 `reason_code` 与 `detail_id` 下拉（下拉数据由后端提供或由前端本地化列表同步）

## 6.2 关键组件设计（职责与数据契约）

（说明组件应接收的 props 与触发的事件）

### LicenseCard（许可证卡片）

-   **Props**：

    -   `license`：对象（见 Dashboard props）

-   **展示**：

    -   header：产品名 + 状态徽章
    -   主体：类型、到期日、绑定设备（脱敏显示，若 hwid_bound=false 显示“未绑定”）
    -   操作区：`Reset HWID`（若可用）、`Upgrade`（若 basic）、`Download`、`View Logs`

-   **事件**：

    -   `onResetHwid`：触发后端请求，接收成功后的 `hwid_reset_at` 与冷却时长并显示倒计时
    -   `onUpgrade`：打开升级 modal

-   **状态/边界场景**：

    -   若 `suspension_reason_code` 存在，展示红色警示条并将主要操作禁用（除 “申诉/联系客服”）

### HwidResetDialog（重置确认弹窗）

-   **显示内容**：

    -   当前 HWID 状态说明、冷却规则（例如 72 小时）、操作风险说明（解绑旧设备将需要在新设备重新登录）

-   **操作**：

    -   二次确认按钮（Disable until user checks “我理解风险”）
    -   发起请求时展示 loading，并根据返回更新 LicenseCard

### LicenseStatusBadge（状态徽章）

-   **输入**：`status`、`suspension_reason_code`、`suspension_detail_id`
-   **输出**：视觉徽章与 tooltip（tooltip 使用 `message_key` 显示本地化文本）

### ActivationLogTable（激活/绑定历史）

-   **行为**：分页加载，支持按时间/操作类型/IP 过滤，行内可展开查看触发上下文（来自 `activation_logs.trigger_data`）

## 6.3 数据流与交互细则（Inertia props 与行为）

-   **页面加载**：所有页面尽量在服务端准备好必要 props（licenses、user、releases），以减少前端额外 API 请求。对于大量历史数据使用分页 lazy-load（Inertia 的 partial reload）。
-   **动作后的局部更新**：例如 HWID 重置后，不必刷新整个页面，只替换对应 license 的 props（Inertia partial props 或局部 refetch）。
-   **错误展示**：

    -   前端统一错误处理：若响应包含 `message_key`，用本地化文案展示；若包含 `reason_code` 且为封禁类，展示全局不可用遮罩并提示申诉入口。

-   **乐观更新与回滚**：

    -   对于立即可见但可回退的操作（例如 UI 显示冷却倒计时），可以采用乐观更新，但应对后端失败进行回滚与提示。

-   **无状态客户端考虑**：

    -   若你将前端做成 SPA 并使用 Token 而非 Session，请在文档中明确 token 获取/刷新流程及安全存储指南（HttpOnly cookie 优先）。

## 6.4 用户体验细节（必须要有）

-   **悬浮/模态文案**：所有风控原因不直接显示后端 raw message，使用 message_key 从本地化文件取人类可读解释。解释应包含：发生了什么、对用户的影响、下一步建议（例如“若非本人操作，请申诉”），以及申诉入口。
-   **冷却倒计时**：重置 HWID 后在 LicenseCard 上以“剩余冷却时间（小时:分钟）”形式展示，且该倒计时应准确到秒并在前端本地计算，以减少不必要的后端请求。
-   **失败/重试策略**：对网络抖动或短暂后端错误提供用户友好提示与重试按钮（不自动重复提交敏感操作）。
-   **敏感操作的二次确认**：重置 HWID、永久封禁、管理员强制 reset 都需要明确的二次确认与操作理由输入框（对于管理员动作同时记录日志）。

## 6.5 管理端 UX（重要）

-   **强制选择事由**：任何管理员对用户/许可证做封禁或警告，必须从下拉选择 `reason_code` 与 `detail_id`。前端应拉取最新的事由字典（`admin:reason-dict` endpoint）以避免前后不一致。
-   **批量操作谨慎**：对于批量封禁/重置，增加“模拟”步骤（先展示将影响的记录）并要求管理员逐条确认或上传审批文件。
-   **导出与审计**：管理界面支持按条件导出 security_logs 与 activation_logs，导出时包含操作人、时间与详细触发快照。

---

## **7. 安全与部署**

1. **软件 API 通信安全**：软件端与服务端的所有通信使用 HMAC-SHA256 签名，配合 HTTPS 确保数据传输安全。
2. **密钥管理**：许可证密钥的生成与注入由独立工具完成，确保密钥安全性。
3. **API 安全**：
    - **软件 API**：强制使用 HMAC-SHA256 签名，时间戳防重放
    - **管理 API**：对 `/admin/*` 路由实施 IP 白名单限制
4. **数据安全**：
    - 密码使用 `bcrypt` 哈希
    - 敏感日志不记录明文密码或密钥

### **7.2 部署与运维**

1. **服务器配置**：
    - Nginx 配置禁止直接访问 `.env`、`storage/`、`vendor/` 等敏感目录。
    - 为 PHP-FPM 配置适当的进程管理和内存限制。
    - 使用 SSL/TLS 加密所有流量。
2. **环境与发布**：
    - 使用 `.env` 文件管理环境变量，确保生产环境密钥**永不**提交至代码库。
    - 部署流程包含自动化测试（PHPUnit, Pest）和数据库迁移。
3. **备份与监控**：
    - 定期自动化备份数据库（至少每日）并异地存储。
    - 配置应用日志监控（如 Laravel Logs 至 Papertrail/Loggly）和服务器健康检查。
    - 设置队列工作者（如处理邮件发送）的进程监控，确保其持续运行。
4. **合规与条款**：
    - 确保页脚的法律条款（服务条款、隐私政策、退款政策）清晰、可见且符合实际业务操作。
    - 在用户注册和购买前，要求其明确同意相关条款。

---

## **8. 客户端 HWID 规范**

### **8.1 HWID 生成规则**

HWID 由以下设备信息生成：

1. **主板序列号**（首要标识）
2. **硬盘序列号**（主硬盘）
3. **MAC 地址**（第一个有效网卡）
4. **CPU 信息**（型号+ID）

**生成算法**：
HWID = SHA256(主板序列号 + 硬盘序列号 + MAC 地址 + CPU ID)

### **8.2 不可变原则**

1. **软件更新不改变 HWID**：相同硬件环境下，任何版本软件应生成相同 HWID
2. **硬件变更处理**：
    - 更换硬盘：HWID 变化，视为新设备
    - 更换网卡：HWID 变化，视为新设备
    - 其他外设：不影响 HWID

### **8.3 客户端实现要求**

1. **缓存机制**：首次计算后缓存 HWID，减少重复计算
2. **降级策略**：如果某项信息获取失败，使用备用标识
3. **版本兼容**：HWID 算法版本记录在客户端信息中

### **8.4 特殊情况处理**

| 场景         | 处理方式                         |
| ------------ | -------------------------------- |
| 虚拟机       | 使用虚拟机唯一标识               |
| 云桌面       | 使用云服务商提供的设备 ID        |
| 硬件信息缺失 | 使用组合标识，标记为"不稳定设备" |
