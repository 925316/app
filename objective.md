# 许可证管理系统 - 开发需求手册

## 📋 修订记录

| 版本 | 日期       | 描述                                       | 作者            |
| ---- | ---------- | ------------------------------------------ | --------------- |
| 1.0  | 2025-12-06 | 初始版本，整合业务需求与技术规格           | Bela            |
| 1.1  | 2025-12-07 | 结构重组，流程细化，编码系统统一           | Bela + DeepSeek |
| 2.0  | 2025-12-10 | 重新规划数据库及预测项目目录，移除无用内容 | Bela            |
| 2.1  | 2025-12-10 | 格式化文档                                 | DeepSeek        |

## 项目目录结构

```bash
[user@localhost wwwroot]$ cd app/
[user@localhost app]$ ls
app        components.json  config            node_modules       phpunit.xml  routes   tsconfig.json
artisan    composer.json    database          package.json       public       storage  vendor
bootstrap  composer.lock    eslint.config.js  package-lock.json  resources    tests    vite.config.ts
[user@localhost app]$ ls -R app/ resources/ database/ storage/ routes/ config/
app/:
Actions  Http  Models  Providers
app/Actions:
Fortify
app/Actions/Fortify:
CreateNewUser.php  PasswordValidationRules.php  ResetUserPassword.php
app/Http:
Controllers  Middleware  Requests
app/Http/Controllers:
Controller.php  Settings
app/Http/Controllers/Settings:
PasswordController.php  ProfileController.php  TwoFactorAuthenticationController.php
app/Http/Middleware:
HandleAppearance.php  HandleInertiaRequests.php
app/Http/Requests:
Settings
app/Http/Requests/Settings:
ProfileUpdateRequest.php  TwoFactorAuthenticationRequest.php
app/Models:
User.php
app/Providers:
AppServiceProvider.php  FortifyServiceProvider.php
config/:
app.php   cache.php     filesystems.php  inertia.php  mail.php   services.php
auth.php  database.php  fortify.php      logging.php  queue.php  session.php
database/:
database.sqlite  factories  migrations  seeders
database/factories:
UserFactory.php
database/migrations:
0001_01_01_000000_create_users_table.php  0001_01_01_000002_create_jobs_table.php
0001_01_01_000001_create_cache_table.php  2025_08_14_170933_add_two_factor_columns_to_users_table.php
database/seeders:
DatabaseSeeder.php
resources/:
css  js  views
resources/css:
app.css
resources/js:
actions  app.ts  components  composables  layouts  lib  pages  routes  ssr.ts  types  wayfinder
resources/js/actions:
App  Illuminate  Laravel
resources/js/actions/App:
Http  index.ts
resources/js/actions/App/Http:
Controllers  index.ts
resources/js/actions/App/Http/Controllers:

INDEX.ts  Settings
resources/js/actions/App/Http/Controllers/Settings:

INDEX.ts  PasswordController.ts  ProfileController.ts  TwoFactorAuthenticationController.ts
resources/js/actions/Illuminate:

INDEX.ts  Routing
resources/js/actions/Illuminate/Routing:

INDEX.ts  RedirectController.ts
resources/js/actions/Laravel:
Fortify  index.ts
resources/js/actions/Laravel/Fortify:
Http  index.ts
resources/js/actions/Laravel/Fortify/Http:
Controllers  index.ts
resources/js/actions/Laravel/Fortify/Http/Controllers:
AuthenticatedSessionController.ts              PasswordResetLinkController.ts
ConfirmablePasswordController.ts               RecoveryCodeController.ts
ConfirmedPasswordStatusController.ts           RegisteredUserController.ts
ConfirmedTwoFactorAuthenticationController.ts  TwoFactorAuthenticatedSessionController.ts
EmailVerificationNotificationController.ts     TwoFactorAuthenticationController.ts
EmailVerificationPromptController.ts           TwoFactorQrCodeController.ts

INDEX.ts                                       TwoFactorSecretKeyController.ts
NewPasswordController.ts                       VerifyEmailController.ts
resources/js/components:
AlertError.vue      AppLogo.vue           DeleteUser.vue    NavFooter.vue           TwoFactorRecoveryCodes.vue
AppContent.vue      AppShell.vue          HeadingSmall.vue  NavMain.vue             TwoFactorSetupModal.vue
AppearanceTabs.vue  AppSidebarHeader.vue  Heading.vue       NavUser.vue             ui
AppHeader.vue       AppSidebar.vue        Icon.vue          PlaceholderPattern.vue  UserInfo.vue
AppLogoIcon.vue     Breadcrumbs.vue       InputError.vue    TextLink.vue            UserMenuContent.vue
resources/js/components/ui:
alert   badge       button  checkbox     dialog         input      label            separator  sidebar   spinner
avatar  breadcrumb  card    collapsible  dropdown-menu  input-otp  navigation-menu  sheet      skeleton  tooltip
resources/js/components/ui/alert:
AlertDescription.vue  AlertTitle.vue  Alert.vue  index.ts
resources/js/components/ui/avatar:
AvatarFallback.vue  AvatarImage.vue  Avatar.vue  index.ts
resources/js/components/ui/badge:
Badge.vue  index.ts
resources/js/components/ui/breadcrumb:
BreadcrumbEllipsis.vue  BreadcrumbLink.vue  BreadcrumbPage.vue       Breadcrumb.vue
BreadcrumbItem.vue      BreadcrumbList.vue  BreadcrumbSeparator.vue  index.ts
resources/js/components/ui/button:
Button.vue  index.ts
resources/js/components/ui/card:
CardAction.vue  CardContent.vue  CardDescription.vue  CardFooter.vue  CardHeader.vue  CardTitle.vue  Card.vue  index.ts
resources/js/components/ui/checkbox:
Checkbox.vue  index.ts
resources/js/components/ui/collapsible:
CollapsibleContent.vue  CollapsibleTrigger.vue  Collapsible.vue  index.ts
resources/js/components/ui/dialog:
DialogClose.vue    DialogDescription.vue  DialogHeader.vue   DialogScrollContent.vue  DialogTrigger.vue  index.ts
DialogContent.vue  DialogFooter.vue       DialogOverlay.vue  DialogTitle.vue          Dialog.vue
resources/js/components/ui/dropdown-menu:
DropdownMenuCheckboxItem.vue  DropdownMenuLabel.vue       DropdownMenuShortcut.vue    DropdownMenuTrigger.vue
DropdownMenuContent.vue       DropdownMenuRadioGroup.vue  DropdownMenuSubContent.vue  DropdownMenu.vue
DropdownMenuGroup.vue         DropdownMenuRadioItem.vue   DropdownMenuSubTrigger.vue  index.ts
DropdownMenuItem.vue          DropdownMenuSeparator.vue   DropdownMenuSub.vue
resources/js/components/ui/input:

INDEX.ts  Input.vue
resources/js/components/ui/input-otp:

INDEX.ts  InputOTPGroup.vue  InputOTPSeparator.vue  InputOTPSlot.vue  InputOTP.vue
resources/js/components/ui/label:

INDEX.ts  Label.vue
resources/js/components/ui/navigation-menu:

INDEX.ts                     NavigationMenuItem.vue  NavigationMenuTrigger.vue
NavigationMenuContent.vue    NavigationMenuLink.vue  NavigationMenuViewport.vue
NavigationMenuIndicator.vue  NavigationMenuList.vue  NavigationMenu.vue
resources/js/components/ui/separator:

INDEX.ts  Separator.vue
resources/js/components/ui/sheet:

INDEX.ts        SheetContent.vue      SheetFooter.vue  SheetOverlay.vue  SheetTrigger.vue
SheetClose.vue  SheetDescription.vue  SheetHeader.vue  SheetTitle.vue    Sheet.vue
resources/js/components/ui/sidebar:

INDEX.ts                 SidebarGroup.vue       SidebarMenuButtonChild.vue  SidebarMenuSub.vue    Sidebar.vue
SidebarContent.vue       SidebarHeader.vue      SidebarMenuButton.vue       SidebarMenu.vue       utils.ts
SidebarFooter.vue        SidebarInput.vue       SidebarMenuItem.vue         SidebarProvider.vue
SidebarGroupAction.vue   SidebarInset.vue       SidebarMenuSkeleton.vue     SidebarRail.vue
SidebarGroupContent.vue  SidebarMenuAction.vue  SidebarMenuSubButton.vue    SidebarSeparator.vue
SidebarGroupLabel.vue    SidebarMenuBadge.vue   SidebarMenuSubItem.vue      SidebarTrigger.vue
resources/js/components/ui/skeleton:

INDEX.ts  Skeleton.vue
resources/js/components/ui/spinner:

INDEX.ts  Spinner.vue
resources/js/components/ui/tooltip:

INDEX.ts  TooltipContent.vue  TooltipProvider.vue  TooltipTrigger.vue  Tooltip.vue
resources/js/composables:
useAppearance.ts  useInitials.ts  useTwoFactorAuth.ts
resources/js/layouts:
app  AppLayout.vue  auth  AuthLayout.vue  settings
resources/js/layouts/app:
AppHeaderLayout.vue  AppSidebarLayout.vue
resources/js/layouts/auth:
AuthCardLayout.vue  AuthSimpleLayout.vue  AuthSplitLayout.vue
resources/js/layouts/settings:
Layout.vue
resources/js/lib:
utils.ts
resources/js/pages:
auth  Dashboard.vue  settings  Welcome.vue
resources/js/pages/auth:
ConfirmPassword.vue  Login.vue     ResetPassword.vue       VerifyEmail.vue
ForgotPassword.vue   Register.vue  TwoFactorChallenge.vue
resources/js/pages/settings:
Appearance.vue  Password.vue  Profile.vue  TwoFactor.vue
resources/js/routes:
appearance  index.ts  login  password  profile  register  storage  two-factor  user-password  verification
resources/js/routes/appearance:

INDEX.ts
resources/js/routes/login:

INDEX.ts
resources/js/routes/password:
confirm  index.ts
resources/js/routes/password/confirm:

INDEX.ts
resources/js/routes/profile:

INDEX.ts
resources/js/routes/register:

INDEX.ts
resources/js/routes/storage:

INDEX.ts
resources/js/routes/two-factor:

INDEX.ts  login
resources/js/routes/two-factor/login:

INDEX.ts
resources/js/routes/user-password:

INDEX.ts
resources/js/routes/verification:

INDEX.ts
resources/js/types:
globals.d.ts  index.d.ts
resources/js/wayfinder:

INDEX.ts
resources/views:
app.blade.php
routes/:
console.php  settings.php  web.php
storage/:
app  framework  logs
storage/app:
private  public
storage/app/private:
storage/app/public:
storage/framework:
cache  sessions  testing  views
storage/framework/cache:
data
storage/framework/cache/data:
storage/framework/sessions:
storage/framework/testing:
storage/framework/views:
69cfa004433ba679b6ad6a6909485b70.php  b508b3ddc8e51e9b82c089d52f2ab144.php  f1332ebe6ffba3a20baf809a645d4449.php
storage/logs:
[user@localhost app]$
```

---

## 🗃️ 数据库设计

### 账户表 `accounts`

| 字段名                     | 类型                         | 约束             | 说明                               |
| -------------------------- | ---------------------------- | ---------------- | ---------------------------------- |
| `id`                       | BIGINT UNSIGNED              | PK, AI           | 主键                               |
| `username`                 | VARCHAR(255)                 | UNIQUE, NOT NULL | 用户名                             |
| `email`                    | VARCHAR(255)                 | UNIQUE, NOT NULL | 邮箱地址                           |
| `password`                 | VARCHAR(255)                 | NOT NULL         | 密码(后端加密)                     |
| `privilege_level`          | ENUM('user', 'basic', 'vip') | DEFAULT 'user'   | 特权等级(0-普通用户,1-Basic,2-VIP) |
| `preferred_language`       | VARCHAR(10)                  | DEFAULT 'en'     | 语言设置                           |
| `last_ip_address`          | VARCHAR(45)                  | NULLABLE         | 最后登录 IP                        |
| `last_user_agent`          | TEXT                         | NULLABLE         | 最后使用的 User-Agent              |
| `hwid_reset_count`         | INT UNSIGNED                 | DEFAULT 0        | HWID 重置次数                      |
| `hwid_last_reset_at`       | TIMESTAMP                    | NULLABLE         | 上次重置 HWID 时间                 |
| `suspension_reason`        | VARCHAR(255)                 | NULLABLE         | 账户封禁原因                       |
| `suspended_until`          | TIMESTAMP                    | NULLABLE         | 封禁截止时间                       |
| `migrated_from`            | VARCHAR(255)                 | NULLABLE         | 迁移来源标识                       |
| `email_verified_at`        | TIMESTAMP                    | NULLABLE         | 邮箱验证时间                       |
| `created_at`, `updated_at` | TIMESTAMP                    |                  | Laravel 时间戳                     |

## 从Fortify抄一下认证字段
也就是
```php
$table->timestamp('email_verified_at')->nullable();
$table->text('two_factor_secret')->nullable();
$table->text('two_factor_recovery_codes')->nullable();
$table->timestamp('two_factor_confirmed_at')->nullable();
```
**索引优化**

UNIQUE

- `username`
- `email`

INDEX

- `email_verified_at`
- `created_at`
- `(privilege_level, suspended_until)`
- `(suspended_until, suspension_reason)`

---

### 设备指纹表 `account_devices`

| 字段名                     | 类型             | 约束                          | 说明                    |
| -------------------------- | ---------------- | ----------------------------- | ----------------------- |
| `id`                       | BIGINT UNSIGNED  | PK, AI                        | 主键                    |
| `account_id`               | BIGINT UNSIGNED  | FK to `accounts.id`, NOT NULL | 关联账户 ID             |
| `hwid_hash`                | VARCHAR(64)      | NOT NULL                      | 设备硬件 ID 哈希        |
| `user_agent_hash`          | VARCHAR(64)      | NOT NULL                      | 用户代理哈希            |
| `ip_address`               | VARCHAR(45)      | NOT NULL                      | IP 地址                 |
| `country_code`             | CHAR(2)          | NULLABLE                      | 国家代码                |
| `device_fingerprint`       | JSON             | NULLABLE                      | 设备特征信息(JSON 格式) |
| `reputation_score`         | TINYINT UNSIGNED | DEFAULT 100                   | 设备信誉评分(0-100)     |
| `first_seen_at`            | TIMESTAMP        | NOT NULL                      | 首次出现时间            |
| `last_seen_at`             | TIMESTAMP        | NOT NULL                      | 最后出现时间            |
| `seen_count`               | INT UNSIGNED     | DEFAULT 1                     | 出现次数                |
| `is_active`                | BOOLEAN          | DEFAULT TRUE                  | 软删除设备记录          |
| `is_trusted`               | BOOLEAN          | DEFAULT FALSE                 | 是否信任设备            |
| `is_suspicious`            | BOOLEAN          | DEFAULT FALSE                 | 是否可疑设备            |
| `risk_factors`             | JSON             | NULLABLE                      | 风险因素记录            |
| `created_at`, `updated_at` | TIMESTAMP        |                               | Laravel 时间戳          |

**设备特征 JSON 结构示例:**

- screen_resolution: 屏幕分辨率
- browser_plugins: 浏览器插件列表
- timezone: 时区信息
- language: 语言设置
- platform: 操作系统平台
- hardware_concurrency: CPU 核心数
- device_memory: 设备内存

**索引优化:**

UNIQUE

- `(account_id, hwid_hash)`

INDEX

- `hwid_hash`
- `is_active`
- `reputation_score`
- `last_seen_at`
- `(reputation_score, last_seen_at)`
- `(account_id, last_seen_at)`
- `(is_trusted, is_suspicious)`

---

### 许可证主表 `licenses`

| 字段名                     | 类型                                                                    | 约束                                 | 说明                    |
| -------------------------- | ----------------------------------------------------------------------- | ------------------------------------ | ----------------------- |
| `id`                       | BIGINT UNSIGNED                                                         | PK, AI                               | 主键                    |
| `license_key`              | VARCHAR(64)                                                             | UNIQUE, NOT NULL                     | 许可证密钥              |
| `account_id`               | BIGINT UNSIGNED                                                         | FK to `accounts.id`, NULLABLE        | 所属账户 ID             |
| `license_type`             | ENUM('basic', 'vip')                                                    | NOT NULL                             | 许可证类型              |
| `license_tier`             | TINYINT UNSIGNED                                                        | NOT NULL                             | 许可证层级(1basic,2vip) |
| `status`                   | ENUM('unused', 'active', 'suspended', 'expired', 'upgraded', 'revoked') | DEFAULT 'unused'                     | 当前状态                |
| `device_binding_id`        | BIGINT UNSIGNED                                                         | FK to `device_bindings.id`, NULLABLE | 绑定的设备 ID           |
| `account_device_id`        | BIGINT UNSIGNED                                                         | FK to `account_devices.id`, NULLABLE | 绑定的账户设备 ID       |
| `hwid_bound_at`            | TIMESTAMP                                                               | NULLABLE                             | HWID 绑定时间           |
| `hwid_reset_at`            | TIMESTAMP                                                               | NULLABLE                             | 上次 HWID 重置时间      |
| `activation_key_used`      | VARCHAR(255)                                                            | NULLABLE                             | 使用的激活密钥          |
| `expires_at`               | TIMESTAMP                                                               | NOT NULL                             | 过期时间                |
| `activated_at`             | TIMESTAMP                                                               | NULLABLE                             | 激活时间                |
| `suspended_at`             | TIMESTAMP                                                               | NULLABLE                             | 封禁时间                |
| `suspension_reason`        | VARCHAR(255)                                                            | NULLABLE                             | 封禁原因代码            |
| `auto_suspend_reason`      | VARCHAR(255)                                                            | NULLABLE                             | 自动封禁原因            |
| `upgraded_to_id`           | BIGINT UNSIGNED                                                         | FK to `licenses.id`, NULLABLE        | 升级目标许可证 ID       |
| `created_from_ip`          | VARCHAR(45)                                                             | NULLABLE                             | 创建时 IP               |
| `total_activations`        | INT UNSIGNED                                                            | DEFAULT 0                            | 总激活次数              |
| `notes`                    | TEXT                                                                    | NULLABLE                             | 管理员备注              |
| `created_at`, `updated_at` | TIMESTAMP                                                               |                                      | Laravel 时间戳          |

**状态流转规则**:

- `unused` → `active`: 用户首次激活
- `active` → `suspended`: 风控触发或管理员操作
- `active` → `expired`: 达到过期时间
- `active` → `upgraded`: 用户升级许可证
- `suspended` → `active`: 管理员解封
- 任何状态 → `revoked`: 许可证被撤销

**索引优化**

UNIQUE

- `license_key`

INDEX

- `device_binding_id`
- `account_device_id`
- `activated_at`
- `(account_id, status)`
- `(status, expires_at)`
- `(license_type, created_at)`

---

### 激活密钥表 `activation_keys`

| 字段名                     | 类型                                         | 约束                          | 说明            |
| -------------------------- | -------------------------------------------- | ----------------------------- | --------------- |
| `id`                       | BIGINT UNSIGNED                              | PK, AI                        | 主键            |
| `key`                      | VARCHAR(64)                                  | UNIQUE, NOT NULL              | 激活密钥        |
| `key_type`                 | ENUM('license', 'upgrade', 'topup', 'reset') | NOT NULL                      | 密钥类型        |
| `target_license_type`      | ENUM('basic', 'vip')                         | NULLABLE                      | 目标许可证类型  |
| `privilege_level`          | TINYINT UNSIGNED                             | DEFAULT 0                     | 授予的特权等级  |
| `account_id`               | BIGINT UNSIGNED                              | FK to `accounts.id`, NULLABLE | 使用账户 ID     |
| `license_id`               | BIGINT UNSIGNED                              | FK to `licenses.id`, NULLABLE | 关联的许可证 ID |
| `used_at`                  | TIMESTAMP                                    | NULLABLE                      | 使用时间        |
| `expires_at`               | TIMESTAMP                                    | NULLABLE                      | 过期时间        |
| `use_count`                | INT UNSIGNED                                 | DEFAULT 0                     | 已使用次数      |
| `is_revoked`               | BOOLEAN                                      | DEFAULT FALSE                 | 是否已撤销      |
| `notes`                    | VARCHAR(255)                                 | NULLABLE                      | 备注说明        |
| `created_by`               | BIGINT UNSIGNED                              | FK to `accounts.id`, NULLABLE | 创建者账户 ID   |
| `created_at`, `updated_at` | TIMESTAMP                                    |                               | Laravel 时间戳  |

**索引优化**

UNIQUE

- `key`

INDEX

- `account_id`
- `license_id`
- `created_by`
- `(key_type, is_revoked, expires_at)`

---

### 设备绑定历史表 `device_bindings`

| 字段名                     | 类型                                       | 约束                                 | 说明              |
| -------------------------- | ------------------------------------------ | ------------------------------------ | ----------------- |
| `id`                       | BIGINT UNSIGNED                            | PK, AI                               | 主键              |
| `license_id`               | BIGINT UNSIGNED                            | FK to `licenses.id`, NOT NULL        | 许可证 ID         |
| `account_id`               | BIGINT UNSIGNED                            | FK to `accounts.id`, NOT NULL        | 账户 ID           |
| `account_device_id`        | BIGINT UNSIGNED                            | FK to `account_devices.id`, NULLABLE | 关联的设备指纹 ID |
| `hwid_hash`                | VARCHAR(64)                                | NOT NULL                             | 设备 HWID 哈希    |
| `ip_address`               | VARCHAR(45)                                | NOT NULL                             | 绑定时的 IP       |
| `user_agent`               | TEXT                                       | NULLABLE                             | User-Agent 信息   |
| `user_agent_hash`          | VARCHAR(64)                                | NULLABLE                             | User-Agent 哈希   |
| `country_code`             | CHAR(2)                                    | NULLABLE                             | 国家代码          |
| `is_active`                | BOOLEAN                                    | DEFAULT TRUE                         | 是否当前激活      |
| `binding_type`             | ENUM('initial', 'reset', 'auto', 'manual') | DEFAULT 'initial'                    | 绑定类型          |
| `regen_count_at_binding`   | INT UNSIGNED                               | DEFAULT 0                            | 绑定时的重置计数  |
| `unbound_at`               | TIMESTAMP                                  | NULLABLE                             | 解绑时间          |
| `unbind_reason`            | VARCHAR(255)                               | NULLABLE                             | 解绑原因          |
| `created_at`, `updated_at` | TIMESTAMP                                  |                                      | Laravel 时间戳    |

**索引优化**

INDEX

- `binding_type`
- `(license_id, is_active, created_at DESC)`
- `(account_id, created_at DESC)`
- `(hwid_hash, created_at DESC)`
- `(account_id, license_id)`

**分区策略**:

- 按月分区:`PARTITION BY RANGE (YEAR(created_at)*100 + MONTH(created_at))`
- 保留最近 12 个月数据

---

### 操作审计日志表 `event_logs`

| 字段名                     | 类型             | 约束                          | 说明                           |
| -------------------------- | ---------------- | ----------------------------- | ------------------------------ |
| `id`                       | BIGINT UNSIGNED  | PK, AI                        | 主键                           |
| `event_type`               | VARCHAR(50)      | NOT NULL                      | 事件类型                       |
| `event_subtype`            | VARCHAR(50)      | NULLABLE                      | 事件子类型                     |
| `account_id`               | BIGINT UNSIGNED  | FK to `accounts.id`, NULLABLE | 关联账户 ID                    |
| `license_id`               | BIGINT UNSIGNED  | FK to `licenses.id`, NULLABLE | 关联许可证 ID                  |
| `performed_by_id`          | BIGINT UNSIGNED  | FK to `accounts.id`, NULLABLE | 操作者账户 ID                  |
| `ip_address`               | VARCHAR(45)      | NULLABLE                      | 操作 IP                        |
| `user_agent`               | TEXT             | NULLABLE                      | User-Agent                     |
| `user_agent_hash`          | VARCHAR(64)      | NULLABLE                      | User-Agent 哈希                |
| `risk_score`               | TINYINT UNSIGNED | NULLABLE                      | 实验性功能-风险评估分数(0-100) |
| `details`                  | JSON             | NULLABLE                      | 事件详情(JSON 格式)            |
| `metadata`                 | JSON             | NULLABLE                      | 元数据                         |
| `created_at`, `updated_at` | TIMESTAMP        |                               | Laravel 时间戳                 |

**事件类型分类**:

- `account.*`: 账户相关操作(注册、登录、修改资料等)
- `license.*`: 许可证操作(激活、绑定、重置、升级等)
- `device.*`: 设备相关操作
- `security.*`: 安全事件(异常登录、风控触发等)
- `admin.*`: 管理员操作
- `system.*`: 系统自动操作

**索引优化**

INDEX

- `performed_by_id`
- `(event_type, created_at)`
- `(account_id, created_at)`
- `(license_id, created_at)`

**分区策略**:

- 按月分区:`PARTITION BY RANGE (TO_DAYS(created_at))`
- 保留最近 6 个月热数据,历史数据归档

---

### 心跳监控表 `heartbeat_logs`

| 字段名                     | 类型                                 | 约束                          | 说明             |
| -------------------------- | ------------------------------------ | ----------------------------- | ---------------- |
| `id`                       | BIGINT UNSIGNED                      | PK, AI                        | 主键             |
| `license_id`               | BIGINT UNSIGNED                      | FK to `licenses.id`, NOT NULL | 许可证 ID        |
| `account_id`               | BIGINT UNSIGNED                      | FK to `accounts.id`, NOT NULL | 账户 ID          |
| `hwid_hash`                | VARCHAR(64)                          | NOT NULL                      | 设备 HWID 哈希   |
| `session_id`               | VARCHAR(255)                         | NULLABLE                      | 客户端会话 ID    |
| `client_version`           | VARCHAR(50)                          | NOT NULL                      | 客户端版本       |
| `ip_address`               | VARCHAR(45)                          | NULLABLE                      | 心跳 IP          |
| `country_code`             | CHAR(2)                              | NULLABLE                      | 国家代码         |
| `user_agent`               | TEXT                                 | NULLABLE                      | User-Agent       |
| `uptime_seconds`           | INT UNSIGNED                         | NULLABLE                      | 客户端运行时间   |
| `memory_usage_mb`          | INT UNSIGNED                         | NULLABLE                      | 内存使用量       |
| `is_offline_report`        | BOOLEAN                              | DEFAULT FALSE                 | 是否为离线后上报 |
| `next_heartbeat_expected`  | TIMESTAMP                            | NULLABLE                      | 下次心跳预期时间 |
| `session_status`           | ENUM('active','idle','stale','dead') | DEFAULT 'dead'                | 会话状态         |
| `heartbeat_count`          | INT UNSIGNED                         | DEFAULT 0                     | 心跳总次数       |
| `avg_heartbeat_interval`   | INT UNSIGNED                         | NULLABLE                      | 平均心跳间隔(秒) |
| `missed_heartbeats`        | INT UNSIGNED                         | DEFAULT 0                     | 丢失心跳次数     |
| `received_at`              | TIMESTAMP                            |                               | 心跳接收时间     |
| `created_at`, `updated_at` | TIMESTAMP                            |                               | Laravel 时间戳   |

**索引优化**

INDEX

- `(license_id, received_at)`
- `(hwid_hash, received_at)`
- `(session_status, received_at)`
- `(session_id, session_status)`
- `(license_id, missed_heartbeats)`

**分区策略**:

- 按周分区:`PARTITION BY RANGE (TO_DAYS(received_at))`
- 保留最近 4 周详细数据,按月汇总归档

---

### 软件发布表 `package_releases`

| 字段名                     | 类型                                   | 约束             | 说明               |
| -------------------------- | -------------------------------------- | ---------------- | ------------------ |
| `id`                       | BIGINT UNSIGNED                        | PK, AI           | 主键               |
| `version`                  | VARCHAR(50)                            | UNIQUE, NOT NULL | 版本号(语义化版本) |
| `release_channel`          | ENUM('stable', 'beta', 'alpha', 'dev') | DEFAULT 'stable' | 发布渠道           |
| `min_license_tier`         | TINYINT UNSIGNED                       | DEFAULT 1        | 最低许可证层级要求 |
| `download_url`             | VARCHAR(255)                           | NOT NULL         | 下载地址           |
| `checksum_sha256`          | CHAR(64)                               | NULLABLE         | 文件 SHA256 校验和 |
| `file_size_bytes`          | BIGINT UNSIGNED                        | NULLABLE         | 文件大小(字节)     |
| `changelog`                | TEXT                                   | NULLABLE         | 更新日志           |
| `is_critical`              | BOOLEAN                                | DEFAULT FALSE    | 是否为关键更新     |
| `is_force_update`          | BOOLEAN                                | DEFAULT FALSE    | 是否强制更新       |
| `release_date`             | TIMESTAMP                              | NOT NULL         | 发布日期           |
| `end_of_support`           | TIMESTAMP                              | NULLABLE         | 支持截止日期       |
| `download_count`           | INT UNSIGNED                           | DEFAULT 0        | 下载次数           |
| `created_at`, `updated_at` | TIMESTAMP                              |                  | Laravel 时间戳     |

**索引优化**

UNIQUE

- `version`

INDEX

- `is_critical`
- `(release_channel, release_date)`
- `(min_license_tier, release_date)`

---

### 会话管理表 `sessions`

| 字段名                     | 类型            | 约束                          | 说明                |
| -------------------------- | --------------- | ----------------------------- | ------------------- |
| `id`                       | BIGINT UNSIGNED | PK, AI                        | 主键                |
| `session_token`            | VARCHAR(255)    | UNIQUE, NOT NULL              | 会话代码            |
| `account_id`               | BIGINT UNSIGNED | FK to `accounts.id`, NOT NULL | 账户 ID             |
| `license_id`               | BIGINT UNSIGNED | FK to `licenses.id`, NULLABLE | 许可证 ID           |
| `hwid_hash`                | VARCHAR(64)     | NULLABLE                      | 设备 HWID 哈希      |
| `ip_address`               | VARCHAR(45)     | NOT NULL                      | 会话 IP             |
| `user_agent`               | TEXT            | NULLABLE                      | User-Agent          |
| `client_version`           | VARCHAR(50)     | NOT NULL                      | 客户端版本          |
| `language`                 | VARCHAR(10)     | DEFAULT 'en'                  | 客户端语言          |
| `session_data`             | JSON            | NULLABLE                      | 会话数据(JSON 格式) |
| `last_heartbeat_at`        | TIMESTAMP       | NULLABLE                      | 最后心跳时间        |
| `expires_at`               | TIMESTAMP       | NOT NULL                      | 会话过期时间        |
| `terminated_at`            | TIMESTAMP       | NULLABLE                      | 会话终止时间        |
| `termination_reason`       | VARCHAR(255)    | NULLABLE                      | 终止原因            |
| `created_at`, `updated_at` | TIMESTAMP       |                               | Laravel 时间戳      |

**索引优化**

UNIQUE

- `session_token`

INDEX

- `last_heartbeat_at`
- `(account_id, expires_at)`
- `(expires_at, account_id)`
- `(license_id, expires_at)`

---

### IP 速率限制表 `ip_rate_limits`

| 字段名                     | 类型            | 约束          | 说明           |
| -------------------------- | --------------- | ------------- | -------------- |
| `id`                       | BIGINT UNSIGNED | PK, AI        | 主键           |
| `ip_address`               | VARCHAR(45)     | NOT NULL      | IP 地址        |
| `endpoint`                 | VARCHAR(255)    | NOT NULL      | 接口端点       |
| `request_count`            | INT UNSIGNED    | DEFAULT 1     | 请求计数       |
| `first_request_at`         | TIMESTAMP       | NOT NULL      | 首次请求时间   |
| `last_request_at`          | TIMESTAMP       | NOT NULL      | 最后请求时间   |
| `is_blocked`               | BOOLEAN         | DEFAULT FALSE | 是否被封锁     |
| `blocked_until`            | TIMESTAMP       | NULLABLE      | 封锁截止时间   |
| `block_reason`             | VARCHAR(255)    | NULLABLE      | 封锁原因       |
| `created_at`, `updated_at` | TIMESTAMP       |               | Laravel 时间戳 |

**索引优化**

INDEX

- `ip_address`
- `endpoint`
- `(ip_address, endpoint)`
- `(is_blocked, blocked_until)`
- `(last_request_at, endpoint)`

**清理策略**:

- 自动清理超过 24 小时的非封锁记录
- 定期归档历史封锁记录

---

## 🧱 后端结构 (Laravel)

```
app/
├── Console/
│   ├── Commands/
│   │   ├── License/
│   │   │   ├── GenerateLicenses.php
│   │   │   ├── CleanupExpiredLicenses.php
│   │   │   └── ProcessHeartbeats.php
│   │   └── Security/
│   │       ├── MonitorSuspiciousActivity.php
│   │       └── CleanupOldLogs.php
│   └── Kernel.php
├── Events/
│   ├── License/
│   │   ├── LicenseActivated.php
│   │   ├── LicenseSuspended.php
│   │   ├── LicenseExpired.php
│   │   └── LicenseUpgraded.php
│   ├── Account/
│   │   ├── AccountRegistered.php
│   │   ├── AccountSuspended.php
│   │   └── DeviceBound.php
│   └── Security/
│       ├── SuspiciousActivityDetected.php
│       └── RateLimitExceeded.php
├── Exceptions/
│   ├── License/
│   │   ├── LicenseActivationException.php
│   │   ├── LicenseSuspendedException.php
│   │   └── HWIDValidationException.php
│   └── Api/
│       ├── ApiException.php
│       └── ValidationException.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── Auth/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   └── DeviceAuthController.php
│   │   │   ├── License/
│   │   │   │   ├── LicenseController.php
│   │   │   │   ├── ActivationController.php
│   │   │   │   └── UpgradeController.php
│   │   │   ├── Account/
│   │   │   │   ├── ProfileController.php
│   │   │   │   └── DevicesController.php
│   │   │   ├── Software/
│   │   │   │   └── UpdateController.php
│   │   │   ├── Heartbeat/
│   │   │   │   └── HeartbeatController.php
│   │   │   └── Admin/
│   │   │       ├── LicenseManagementController.php
│   │   │       └── UserManagementController.php
│   │   └── Web/
│   │       ├── DashboardController.php
│   │       └── SettingsController.php
│   ├── Middleware/
│   │   ├── Api/
│   │   │   ├── CheckLicenseStatus.php
│   │   │   ├── ValidateHWID.php
│   │   │   ├── RateLimitByIP.php
│   │   │   └── CheckPrivilegeLevel.php
│   │   └── Web/
│   │       └── RedirectIfAuthenticated.php
│   ├── Requests/
│   │   ├── Api/
│   │   │   ├── License/
│   │   │   │   ├── ActivateLicenseRequest.php
│   │   │   │   └── ResetHWIDRequest.php
│   │   │   ├── Account/
│   │   │   │   ├── RegisterRequest.php
│   │   │   │   └── UpdateProfileRequest.php
│   │   │   └── Heartbeat/
│   │   │       └── HeartbeatRequest.php
│   │   └── Admin/
│   │       └── GenerateLicensesRequest.php
│   └── Resources/
│       ├── License/
│       │   ├── LicenseResource.php
│       │   └── LicenseCollection.php
│       ├── Account/
│       │   ├── AccountResource.php
│       │   └── DeviceResource.php
│       └── Api/
│           └── ApiResponse.php
├── Models/
│   ├── Account.php
│   ├── AccountDevice.php
│   ├── License.php
│   ├── ActivationKey.php
│   ├── DeviceBinding.php
│   ├── EventLog.php
│   ├── HeartbeatLog.php
│   ├── PackageRelease.php
│   ├── Session.php
│   └── IpRateLimit.php
├── Observers/
│   ├── LicenseObserver.php
│   ├── AccountObserver.php
│   └── EventLogObserver.php
├── Policies/
│   ├── LicensePolicy.php
│   ├── AccountPolicy.php
│   └── AdminPolicy.php
├── Services/
│   ├── License/
│   │   ├── LicenseService.php
│   │   ├── ActivationService.php
│   │   └── HWIDService.php
│   ├── Security/
│   │   ├── RiskAssessmentService.php
│   │   ├── FraudDetectionService.php
│   │   └── RateLimitService.php
│   ├── Software/
│   │   └── UpdateService.php
│   └── Analytics/
│       └── UsageAnalyticsService.php
├── Traits/
│   ├── HasLicenses.php
│   ├── HasDevices.php
│   ├── HasPrivileges.php
│   └── HasActivityLog.php
└── Providers/
    ├── LicenseServiceProvider.php
    ├── SecurityServiceProvider.php
    └── EventServiceProvider.php
```

## 🗃️ 数据库迁移与填充:

```
database/
├── migrations/
│   ├── 2025_12_10_000001_create_accounts_table.php
│   ├── 2025_12_10_000002_create_account_devices_table.php
│   ├── 2025_12_10_000003_create_licenses_table.php
│   ├── 2025_12_10_000004_create_activation_keys_table.php
│   ├── 2025_12_10_000005_create_device_bindings_table.php
│   ├── 2025_12_10_000006_create_event_logs_table.php
│   ├── 2025_12_10_000007_create_heartbeat_logs_table.php
│   ├── 2025_12_10_000008_create_package_releases_table.php
│   ├── 2025_12_10_000009_create_sessions_table.php
│   └── 2025_12_10_000010_create_ip_rate_limits_table.php
├── seeders/
│   ├── AdminUserSeeder.php
│   ├── LicenseTiersSeeder.php
│   ├── DefaultSettingsSeeder.php
│   └── TestDataSeeder.php
├── factories/
│   ├── AccountFactory.php
│   ├── LicenseFactory.php
│   └── ActivationKeyFactory.php
└── seeders/
    └── DatabaseSeeder.php
```

## 🖥️ 前端结构 (Vue 3 + TypeScript)

```
resources/js/
├── api/
│   ├── client/
│   │   ├── axios.ts
│   │   └── interceptors.ts
│   ├── services/
│   │   ├── auth.service.ts
│   │   ├── license.service.ts
│   │   ├── account.service.ts
│   │   ├── software.service.ts
│   │   └── heartbeat.service.ts
│   └── types/
│       ├── api.types.ts
│       ├── license.types.ts
│       └── account.types.ts
├── components/
│   ├── layout/
│   │   ├── AdminLayout.vue
│   │   ├── UserLayout.vue
│   │   └── LicenseLayout.vue
│   ├── license/
│   │   ├── LicenseCard.vue
│   │   ├── LicenseStatus.vue
│   │   ├── ActivationForm.vue
│   │   └── HWIDResetRequest.vue
│   ├── account/
│   │   ├── AccountProfile.vue
│   │   ├── DeviceList.vue
│   │   ├── SecuritySettings.vue
│   │   └── PrivilegeBadge.vue
│   ├── admin/
│   │   ├── LicenseManager.vue
│   │   ├── UserManager.vue
│   │   ├── AnalyticsDashboard.vue
│   │   └── KeyGenerator.vue
│   ├── software/
│   │   ├── UpdateChecker.vue
│   │   ├── ReleaseNotes.vue
│   │   └── DownloadButton.vue
│   └── ui/
│       ├── LicenseStatusBadge.vue
│       ├── DeviceInfoCard.vue
│       ├── RiskIndicator.vue
│       └── CountdownTimer.vue
├── composables/
│   ├── useLicense.ts
│   ├── useDevice.ts
│   ├── useAuth.ts
│   ├── useWebSocket.ts
│   └── useAnalytics.ts
├── stores/
│   ├── auth.store.ts
│   ├── license.store.ts
│   ├── account.store.ts
│   ├── software.store.ts
│   └── notification.store.ts
├── pages/
│   ├── auth/
│   │   ├── Login.vue
│   │   ├── Register.vue
│   │   └── TwoFactor.vue
│   ├── dashboard/
│   │   ├── UserDashboard.vue
│   │   └── AdminDashboard.vue
│   ├── license/
│   │   ├── MyLicenses.vue
│   │   ├── ActivateLicense.vue
│   │   ├── LicenseDetails.vue
│   │   └── UpgradeLicense.vue
│   ├── account/
│   │   ├── Profile.vue
│   │   ├── Devices.vue
│   │   ├── Security.vue
│   │   └── Billing.vue
│   ├── software/
│   │   ├── Download.vue
│   │   ├── Changelog.vue
│   │   └── Support.vue
│   └── admin/
│       ├── LicensesManagement.vue
│       ├── UsersManagement.vue
│       ├── KeysManagement.vue
│       ├── Analytics.vue
│       └── Settings.vue
├── router/
│   ├── index.ts
│   ├── routes/
│   │   ├── auth.routes.ts
│   │   ├── user.routes.ts
│   │   ├── admin.routes.ts
│   │   └── license.routes.ts
│   └── guards/
│       ├── auth.guard.ts
│       ├── license.guard.ts
│       └── admin.guard.ts
├── utils/
│   ├── license/
│   │   ├── keyGenerator.ts
│   │   ├── hwidUtils.ts
│   │   └── validation.ts
│   ├── security/
│   │   ├── encryption.ts
│   │   └── fingerprint.ts
│   ├── time/
│   │   ├── format.ts
│   │   └── countdown.ts
│   └── validation/
│       └── schemas.ts
└── types/
    ├── models/
    │   ├── License.ts
    │   ├── Account.ts
    │   └── Device.ts
    └── api/
        └── responses.ts
```

## ⚙️ 配置文件

```
config/
├── license.php         # 许可证配置
├── security.php        # 安全配置
├── hwid.php            # HWID 配置
├── rate-limiting.php   # 速率限制配置
├── software.php        # 软件发布配置
└── services.php        # 服务配置
```
