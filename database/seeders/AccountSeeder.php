<?php

namespace Database\Seeders;

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountSeeder extends Seeder
{
    private const PASSWORD = 'password';

    private const ACCOUNT_AGE_MIN_DAYS = 370;

    private const ACCOUNT_AGE_MAX_DAYS = 430;

    public function run(): void
    {
        $this->command->info('Creating test accounts...');

        $this->createDefaultUser();
        $this->createTierUsers();
        $this->createStaffAndTester();
        $this->createUpgradeUsers();
        $this->createEdgeCaseUsers();

        $this->displayStats();
    }

    private function createDefaultUser(): void
    {
        [$createdAt, $updatedAt] = $this->seededAccountTimestamps();
        $emailVerifiedAt = $createdAt->copy()->addDays(2);
        $lastLoginAt = $updatedAt->copy()->subHours(2);

        $account = $this->createSeedAccount([
            'username' => 'default',
            'email' => 'default@test.com',
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => $emailVerifiedAt,
            'last_login_at' => $lastLoginAt,
            'last_ip_address' => '127.0.0.1',
        ], $createdAt, $updatedAt);

        $this->createDevice($account, now()->subDays(30));
    }

    private function createTierUsers(): void
    {
        $tiers = [
            ['privilege' => LicensePrivilege::STANDARD, 'username' => 'standard'],
            ['privilege' => LicensePrivilege::STANDARD, 'username' => 'upgrade'],
            ['privilege' => LicensePrivilege::ULTIMATE, 'username' => 'ultimate'],
        ];

        foreach ($tiers as $tier) {
            $account = $this->createAccountWithLicense(
                username: $tier['username'],
                email: "{$tier['username']}@test.com",
                privilege: $tier['privilege']
            );
            $this->createDevice($account, now()->subDays(fake()->numberBetween(10, 60)));
        }
    }

    private function createStaffAndTester(): void
    {
        $staff = $this->createAccountWithLicense(
            username: 'staff',
            email: 'staff@test.com',
            privilege: LicensePrivilege::STAFF
        );
        $this->createDevice($staff, now()->subDays(5));

        $tester = $this->createAccountWithLicense(
            username: 'tester',
            email: 'tester@test.com',
            privilege: LicensePrivilege::TESTER
        );
        $this->createDevice($tester, now()->subDays(3));
    }

    private function createUpgradeUsers(): void
    {
        $this->createUpgradeAccount(
            'default_standard',
            'default.standard@test.com',
            LicensePrivilege::DEFAULT,
            LicensePrivilege::STANDARD
        );

        $this->createUpgradeAccount(
            'default_staff',
            'default.staff@test.com',
            LicensePrivilege::DEFAULT,
            LicensePrivilege::STAFF
        );

        $this->createUpgradeAccount(
            'standard_ultimate',
            'standard.ultimate@test.com',
            LicensePrivilege::STANDARD,
            LicensePrivilege::ULTIMATE
        );

        $this->createUpgradeAccount(
            'tester_staff',
            'tester.staff@test.com',
            LicensePrivilege::TESTER,
            LicensePrivilege::STAFF
        );
    }

    private function createEdgeCaseUsers(): void
    {
        $expired = $this->createAccountWithLicense(
            username: 'expired',
            email: 'expired@test.com',
            privilege: LicensePrivilege::ULTIMATE,
            status: LicenseStatus::EXPIRED,
            activatedAt: now()->subMonths(6),
            expiresAt: now()->subDays(7)
        );
        $this->createDevice($expired, now()->subMonths(5));

        $suspended = $this->createSeedAccount([
            'username' => 'suspended',
            'email' => 'suspended@test.com',
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => now()->subDays(365),
            'is_suspended' => true,
            'suspension_reason' => 'Test suspension',
            'suspended_until' => now()->addDays(7),
        ], now()->subDays(390), now()->subDays(10));
        $this->createAccountWithLicense(
            username: 'suspended',
            email: 'suspended@test.com',
            privilege: LicensePrivilege::STANDARD,
            account: $suspended
        );

        $unverified = $this->createSeedAccount([
            'username' => 'unverified',
            'email' => 'unverified@test.com',
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => null,
        ], now()->subDays(380), now()->subDays(20));
        $this->createAccountWithLicense(
            username: 'unverified',
            email: 'unverified@test.com',
            privilege: LicensePrivilege::DEFAULT,
            account: $unverified
        );
    }

    private function generateLicenseKey(string $prefix): string
    {
        return LicenseService::generateLicenseKey();
    }

    private function createAccountWithLicense(
        string $username,
        string $email,
        LicensePrivilege $privilege,
        ?Account $account = null,
        LicenseStatus $status = LicenseStatus::ACTIVE,
        ?\DateTimeInterface $activatedAt = null,
        ?\DateTimeInterface $expiresAt = null
    ): Account {
        if (! $account) {
            [$createdAt, $updatedAt] = $this->seededAccountTimestamps();
            $emailVerifiedAt = $createdAt->copy()->addDays(fake()->numberBetween(1, 30));
            $lastLoginAt = $updatedAt->copy()->subHours(fake()->numberBetween(1, 72));

            if ($lastLoginAt->lessThan($emailVerifiedAt)) {
                $lastLoginAt = $emailVerifiedAt->copy()->addHours(fake()->numberBetween(1, 24));
            }

            $account = $this->createSeedAccount([
                'username' => $username,
                'email' => $email,
                'password' => Hash::make(self::PASSWORD),
                'email_verified_at' => $emailVerifiedAt,
                'last_login_at' => $lastLoginAt,
                'last_ip_address' => '192.168.1.'.fake()->numberBetween(1, 254),
            ], $createdAt, $updatedAt);
        }

        $resolvedActivatedAt = $activatedAt
            ? now()->setTimestamp($activatedAt->getTimestamp())
            : now()->subMonth();
        $createdAt = $resolvedActivatedAt->copy()->subDays(fake()->numberBetween(1, 20));
        $updatedAt = $resolvedActivatedAt->copy();

        if ($status === LicenseStatus::EXPIRED) {
            $updatedAt = ($expiresAt ? now()->setTimestamp($expiresAt->getTimestamp()) : now()->subDay());
        }

        License::create([
            'key' => $this->generateLicenseKey($privilege->getLabel()),
            'privilege' => $privilege->value,
            'status' => $status->value,
            'used_by' => $account->id,
            'expires_at' => $expiresAt ?? now()->addYear(),
            'activated_at' => $resolvedActivatedAt,
            'notes' => "{$privilege->getLabel()} license",
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);

        return $account;
    }

    private function createUpgradeAccount(
        string $username,
        string $email,
        LicensePrivilege $fromPrivilege,
        LicensePrivilege $toPrivilege
    ): Account {
        [$createdAt, $updatedAt] = $this->seededAccountTimestamps();
        $emailVerifiedAt = $createdAt->copy()->addDays(5);
        $lastLoginAt = $updatedAt->copy()->subDays(fake()->numberBetween(1, 7));

        $account = $this->createSeedAccount([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => $emailVerifiedAt,
            'last_login_at' => $lastLoginAt,
            'last_ip_address' => '192.168.1.'.fake()->numberBetween(1, 254),
        ], $createdAt, $updatedAt);

        License::create([
            'key' => $this->generateLicenseKey($toPrivilege->getLabel()),
            'privilege' => $toPrivilege->value,
            'status' => LicenseStatus::ACTIVE->value,
            'used_by' => $account->id,
            'expires_at' => now()->addYear(),
            'activated_at' => now()->subWeek(),
            'notes' => "Current: {$toPrivilege->getLabel()}",
            'created_at' => now()->subDays(21),
            'updated_at' => now()->subWeek(),
        ]);

        License::create([
            'key' => $this->generateLicenseKey('OLD'),
            'privilege' => $fromPrivilege->value,
            'status' => LicenseStatus::UPGRADED->value,
            'used_by' => $account->id,
            'expires_at' => now()->subWeek(),
            'activated_at' => now()->subMonth(),
            'notes' => "Previous: {$fromPrivilege->getLabel()}",
            'created_at' => now()->subDays(50),
            'updated_at' => now()->subDays(7),
        ]);

        $this->createDevice($account, now()->subDays(fake()->numberBetween(10, 30)));

        return $account;
    }

    private function createSeedAccount(array $attributes, Carbon $createdAt, Carbon $updatedAt): Account
    {
        if (isset($attributes['username']) && is_string($attributes['username'])) {
            $normalizedUsername = strtolower(preg_replace('/[^a-z0-9]/', '', $attributes['username']) ?? '');
            if ($normalizedUsername === '') {
                $normalizedUsername = 'user'.fake()->unique()->numberBetween(1000, 999999);
            }
            $attributes['username'] = $normalizedUsername;
        }

        if (isset($attributes['email']) && is_string($attributes['email'])) {
            $attributes['email'] = strtolower($attributes['email']);
        }

        $account = Account::create($attributes);

        Account::withoutTimestamps(function () use ($account, $createdAt, $updatedAt): void {
            $account->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ])->saveQuietly();
        });

        return $account;
    }

    private function seededAccountTimestamps(): array
    {
        $createdAt = now()->subDays(fake()->numberBetween(self::ACCOUNT_AGE_MIN_DAYS, self::ACCOUNT_AGE_MAX_DAYS));
        $updatedAt = $createdAt->copy()->addDays(fake()->numberBetween(30, 340));

        if ($updatedAt->greaterThan(now()->subDay())) {
            $updatedAt = now()->subDay();
        }

        return [$createdAt, $updatedAt];
    }

    private function createDevice(Account $account, \DateTimeInterface $firstSeen): void
    {
        $boundAt = now()->setTimestamp($firstSeen->getTimestamp())->addDays(fake()->numberBetween(1, 7));

        AccountDevice::create([
            'account_id' => $account->id,
            'hwid_hash' => hash('sha256', $account->username.Str::random(20)),
            'ip_address' => '192.168.1.'.fake()->numberBetween(1, 254),
            'first_seen_at' => $firstSeen,
            'last_seen_at' => now()->subHours(fake()->numberBetween(1, 48)),
            'bound_at' => $boundAt,
            'unbound_at' => null,
        ]);
    }

    private function displayStats(): void
    {
        $this->command->newLine();
        $this->command->info('======================================');
        $this->command->info('  Test Accounts Created');
        $this->command->info('  Default password: '.self::PASSWORD);
        $this->command->info('======================================');
        $this->command->newLine();

        $this->command->info('Accounts:');
        $this->command->info('  default@test.com          - DEFAULT (no license)');
        $this->command->info('  standard@test.com         - STANDARD');
        $this->command->info('  upgrade@test.com          - STANDARD (upgrade candidate)');
        $this->command->info('  ultimate@test.com         - ULTIMATE');
        $this->command->info('  staff@test.com            - STAFF');
        $this->command->info('  tester@test.com           - TESTER');
        $this->command->newLine();
        $this->command->info('Upgrade scenarios:');
        $this->command->info('  default.standard@test.com - DEFAULT -> STANDARD');
        $this->command->info('  default.staff@test.com    - DEFAULT -> STAFF');
        $this->command->info('  standard.ultimate@test.com - STANDARD -> ULTIMATE');
        $this->command->info('  tester.staff@test.com     - TESTER -> STAFF');
        $this->command->newLine();
        $this->command->info('Edge cases:');
        $this->command->info('  expired@test.com      - Expired license');
        $this->command->info('  suspended@test.com    - Suspended account');
        $this->command->info('  unverified@test.com   - Unverified email');
        $this->command->newLine();
    }
}
