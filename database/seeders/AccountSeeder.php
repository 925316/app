<?php

namespace Database\Seeders;

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\License;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountSeeder extends Seeder
{
    private const PASSWORD = 'password';

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
        $account = Account::create([
            'username' => 'default',
            'email' => 'default@test.com',
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => now()->subDay(),
            'last_login_at' => now()->subHours(2),
            'last_ip_address' => '127.0.0.1',
        ]);

        $this->createDevice($account, now()->subDays(30));
    }

    private function createTierUsers(): void
    {
        $tiers = [
            ['privilege' => LicensePrivilege::STANDARD, 'username' => 'standard'],
            ['privilege' => LicensePrivilege::UPGRADE, 'username' => 'upgrade'],
            ['privilege' => LicensePrivilege::ULTIMATE, 'username' => 'ultimate'],
        ];

        foreach ($tiers as $tier) {
            $account = $this->createAccountWithLicense(
                username: $tier['username'],
                email: "{$tier['username']}@test.com",
                privilege: $tier['privilege']
            );
            $this->createDevice($account, now()->subDays(rand(10, 60)));
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

        $suspended = Account::create([
            'username' => 'suspended',
            'email' => 'suspended@test.com',
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => now()->subMonth(),
            'is_suspended' => true,
            'suspension_reason' => 'Test suspension',
            'suspended_until' => now()->addDays(7),
        ]);
        $this->createAccountWithLicense(
            username: 'suspended',
            email: 'suspended@test.com',
            privilege: LicensePrivilege::STANDARD,
            account: $suspended
        );

        $unverified = Account::create([
            'username' => 'unverified',
            'email' => 'unverified@test.com',
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => null,
        ]);
        $this->createAccountWithLicense(
            username: 'unverified',
            email: 'unverified@test.com',
            privilege: LicensePrivilege::DEFAULT,
            account: $unverified
        );
    }

    private function generateLicenseKey(string $prefix): string
    {
        $part1 = substr(strtoupper(Str::random(10)), 0, 5);
        $part2 = substr(strtoupper(dechex(rand(0, 1048575))), 0, 5);
        $part2 = str_pad($part2, 5, '0', STR_PAD_LEFT);
        $chars3 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $part3 = '';
        for ($i = 0; $i < 5; $i++) {
            $part3 .= $chars3[rand(0, strlen($chars3) - 1)];
        }
        $chars4 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ345678';
        $part4 = '';
        for ($i = 0; $i < 5; $i++) {
            $part4 .= $chars4[rand(0, strlen($chars4) - 1)];
        }
        $part5 = substr(strtoupper(Str::random(10)), 0, 5);

        return "{$part1}-{$part2}-{$part3}-{$part4}-{$part5}";
    }

    private function createAccountWithLicense(
        string $username,
        string $email,
        LicensePrivilege $privilege,
        ?Account $account = null,
        LicenseStatus $status = LicenseStatus::ACTIVE,
        ?\DateTime $activatedAt = null,
        ?\DateTime $expiresAt = null
    ): Account {
        $account ??= Account::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => now()->subDays(rand(1, 30)),
            'last_login_at' => now()->subHours(rand(1, 72)),
            'last_ip_address' => '192.168.1.'.rand(1, 254),
        ]);

        License::create([
            'key' => $this->generateLicenseKey($privilege->getLabel()),
            'privilege' => $privilege->value,
            'status' => $status->value,
            'used_by' => $account->id,
            'expires_at' => $expiresAt ?? now()->addYear(),
            'activated_at' => $activatedAt ?? now()->subMonth(),
            'notes' => "{$privilege->getLabel()} license",
        ]);

        return $account;
    }

    private function createUpgradeAccount(
        string $username,
        string $email,
        LicensePrivilege $fromPrivilege,
        LicensePrivilege $toPrivilege
    ): Account {
        $account = Account::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => now()->subMonth(),
            'last_login_at' => now()->subDays(rand(1, 7)),
            'last_ip_address' => '192.168.1.'.rand(1, 254),
        ]);

        License::create([
            'key' => $this->generateLicenseKey($toPrivilege->getLabel()),
            'privilege' => $toPrivilege->value,
            'status' => LicenseStatus::ACTIVE->value,
            'used_by' => $account->id,
            'expires_at' => now()->addYear(),
            'activated_at' => now()->subWeek(),
            'notes' => "Current: {$toPrivilege->getLabel()}",
        ]);

        License::create([
            'key' => $this->generateLicenseKey('OLD'),
            'privilege' => $fromPrivilege->value,
            'status' => LicenseStatus::UPGRADED->value,
            'used_by' => $account->id,
            'expires_at' => now()->subWeek(),
            'activated_at' => now()->subMonth(),
            'notes' => "Previous: {$fromPrivilege->getLabel()}",
        ]);

        $this->createDevice($account, now()->subDays(rand(10, 30)));

        return $account;
    }

    private function createDevice(Account $account, \DateTime $firstSeen): void
    {
        $boundAt = (clone $firstSeen)->modify('+'.rand(1, 7).' days');

        AccountDevice::create([
            'account_id' => $account->id,
            'hwid_hash' => hash('sha256', $account->username.Str::random(20)),
            'ip_address' => '192.168.1.'.rand(1, 254),
            'first_seen_at' => $firstSeen,
            'last_seen_at' => now()->subHours(rand(1, 48)),
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
        $this->command->info('  upgrade@test.com          - UPGRADE');
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
