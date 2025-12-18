<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\EventLog;
use App\Models\License;
use App\Models\AccountDevice;
use App\Models\Upgrade;
use Carbon\Carbon;
use Illuminate\Database\Seeder;


class EventLogSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::all();
        $licenses = License::all();

        $this->createLicenseActivationEvents($licenses);

        $this->createDeviceBindingEvents();

        $this->createDeviceUnbindingEvents();

        $this->createAccountRegistrationEvents($accounts);

        $this->createLoginAnomalyEvents($accounts);

        $this->createAccountSuspensionEvents($accounts);

        $this->createLicenseCreationEvents($licenses);

        $this->createUpgradeEvents();

        $this->createSystemEvents();

        $totalEvents = EventLog::count();
        $byLevel = EventLog::selectRaw('event_level, count(*) as count')
            ->groupBy('event_level')
            ->get()
            ->keyBy('event_level');

        $levelNames = [0 => 'info', 1 => 'warn', 2 => 'error'];

        EventLog::factory()->count(100)->create();

        echo "about " . EventLog::count() . " event logs created.\n";
    }

    private function createLicenseActivationEvents($licenses): void
    {
        $count = 0;

        foreach ($licenses->where('status', 1) as $license) {
            if ($license->used_by && $license->activated_at) {
                EventLog::factory()->accountActivated(
                    $license->used_by,
                    $license->id,
                    $license->created_from_ip ?? '127.0.0.1'
                )->create([
                    'event_type' => 'account_activated',
                    'event_level' => 0,
                    'license_id' => $license->id,
                    'created_at' => $license->activated_at,
                    'updated_at' => $license->activated_at,
                ]);
                $count++;
            }
        }
    }

    private function createDeviceBindingEvents(): void
    {
        $devices = AccountDevice::whereNotNull('bound_at')->get();
        $count = 0;

        foreach ($devices as $device) {
            EventLog::factory()->deviceBound(
                $device->account_id,
                $device->id,
                $device->ip_address
            )->create([
                'created_at' => $device->bound_at,
                'updated_at' => $device->bound_at,
            ]);
            $count++;
        }
    }

    private function createDeviceUnbindingEvents(): void
    {
        $devices = AccountDevice::whereNotNull('unbound_at')->get();
        $count = 0;

        foreach ($devices as $device) {
            EventLog::factory()->deviceUnbound(
                $device->account_id,
                $device->id,
                $device->ip_address
            )->create([
                'created_at' => $device->unbound_at,
                'updated_at' => $device->unbound_at,
            ]);
            $count++;
        }
    }

    private function createAccountRegistrationEvents($accounts): void
    {
        $count = 0;

        foreach ($accounts as $account) {
            EventLog::create([
                'event_type' => 'account.registered',
                'event_level' => 0,
                'account_id' => $account->id,
                'ip_address' => $account->last_ip_address ?? '127.0.0.1',
                'actor_id' => $account->id,
                'details' => json_encode([
                    'username' => $account->username,
                    'email' => $account->email,
                    'action' => 'registration',
                ]),
                'created_at' => $account->created_at,
                'updated_at' => $account->created_at,
            ]);
            $count++;
        }
    }

    private function createLoginAnomalyEvents($accounts): void
    {
        $count = 0;
        $accountsWithAnomalies = $accounts->random(max(1, (int)($accounts->count() * 0.1)));

        foreach ($accountsWithAnomalies as $account) {
            $numAnomalies = rand(1, 3);

            for ($i = 0; $i < $numAnomalies; $i++) {
                EventLog::factory()->loginAnomaly(
                    $account->id,
                    $this->generateRandomIp(),
                    [
                        'location' => $this->generateRandomCountry(),
                        'previous_location' => 'United States',
                        'time_difference' => rand(2, 12) . ' hours',
                    ]
                )->create([
                    'created_at' => now()->subDays(rand(1, 180)),
                ]);
                $count++;
            }
        }
    }

    private function createAccountSuspensionEvents($accounts): void
    {
        $suspendedAccounts = $accounts->where('is_suspended', true);
        $count = 0;

        foreach ($suspendedAccounts as $account) {
            $admin = Account::where('username', 'admin')->first();

            EventLog::factory()->accountSuspended(
                $account->id,
                $admin ? $admin->id : 1,
                $account->suspension_reason ?? 'Violation of terms'
            )->create([
                'created_at' => $account->updated_at,
            ]);
            $count++;
        }
    }

    private function createLicenseCreationEvents($licenses): void
    {
        $count = 0;
        $admin = Account::where('username', 'admin')->first();

        foreach ($licenses as $license) {
            EventLog::factory()->licenseCreated(
                $license->id,
                $admin ? $admin->id : 1,
                $license->created_from_ip ?? '127.0.0.1'
            )->create([
                'created_at' => $license->created_at,
                'updated_at' => $license->created_at,
            ]);
            $count++;
        }
    }

    private function createUpgradeEvents(): void
    {
        $upgrades = Upgrade::all();
        $count = 0;

        foreach ($upgrades as $upgrade) {
            EventLog::create([
                'event_type' => 'upgrade.performed',
                'event_level' => 0,
                'account_id' => $upgrade->account_id,
                'license_id' => $upgrade->license_to,
                'ip_address' => '127.0.0.1',
                'actor_id' => $upgrade->account_id,
                'details' => json_encode([
                    'action' => 'license_upgrade',
                    'from_license' => $upgrade->license_from,
                    'to_license' => $upgrade->license_to,
                    'notes' => $upgrade->notes,
                ]),
                'created_at' => $upgrade->created_at,
                'updated_at' => $upgrade->created_at,
            ]);
            $count++;
        }
    }

    private function createSystemEvents(): void
    {
        $events = [
            [
                'type' => 'system.maintenance',
                'level' => 0,
                'message' => 'System maintenance completed',
                'duration' => '2 hours',
            ],
            [
                'type' => 'database.backup',
                'level' => 0,
                'message' => 'Successfully backed up database',
                'size' => '2.5 GB',
            ],
            [
                'type' => 'security.audit',
                'level' => 0,
                'message' => 'Security audit completed without issues',
                'findings' => 'None',
            ],
        ];

        $count = 0;
        foreach ($events as $event) {
            EventLog::create([
                'event_type' => $event['type'],
                'event_level' => $event['level'],
                'ip_address' => '127.0.0.1',
                'actor_id' => 1,
                'details' => json_encode($event),
                'created_at' => now()->subDays(rand(1, 90)),
                'updated_at' => now()->subDays(rand(1, 90)),
            ]);
            $count++;
        }
    }

    private function generateRandomIp(): string
    {
        return rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
    }

    private function generateRandomCountry(): string
    {
        $countries = ['Russia', 'China', 'Brazil', 'India', 'Nigeria', 'Vietnam', 'Turkey'];
        return $countries[array_rand($countries)];
    }
}
