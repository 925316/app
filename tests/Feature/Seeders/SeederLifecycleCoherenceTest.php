<?php

use App\Enums\EventType;
use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\EventLog;
use App\Models\License;
use Database\Seeders\AccountSeeder;
use Database\Seeders\EventLogSeeder;
use Database\Seeders\LicenseSeeder;

use function Pest\Laravel\seed;

beforeEach(function () {
    seed([
        AccountSeeder::class,
        LicenseSeeder::class,
    ]);

    Account::query()->orderBy('id')->each(function (Account $account): void {
        $createdAt = $account->created_at ?? now()->subDays(90);
        $base = $createdAt->copy()->addMinutes($account->id * 5 + 10);

        AccountDevice::query()->create([
            'account_id' => $account->id,
            'hwid_hash' => hash('sha256', 'seed-device-'.$account->id),
            'ip_address' => '192.168.10.'.($account->id % 200 + 20),
            'first_seen_at' => $base,
            'last_seen_at' => $base->copy()->addDays(20),
            'bound_at' => $base->copy()->addDays(1),
            'unbound_at' => null,
        ]);
    });

    seed([
        EventLogSeeder::class,
    ]);
});

it('does not create standalone active upgrade licenses', function () {
    $invalidCount = License::query()
        ->where('status', LicenseStatus::ACTIVE->value)
        ->where('privilege', LicensePrivilege::UPGRADE->value)
        ->count();

    expect($invalidCount)->toBe(0);
});

it('creates upgraded history only with plausible ownership and timestamps', function () {
    $upgradedLicenses = License::query()
        ->where('status', LicenseStatus::UPGRADED->value)
        ->get();

    expect($upgradedLicenses)->not->toBeEmpty();

    foreach ($upgradedLicenses as $license) {
        expect($license->used_by)->not->toBeNull();
        expect($license->activated_at)->not->toBeNull();
        expect($license->updated_at)->not->toBeNull();
        expect($license->updated_at?->greaterThanOrEqualTo($license->activated_at))->toBeTrue();

        $relatedActive = License::query()
            ->where('used_by', $license->used_by)
            ->where('status', LicenseStatus::ACTIVE->value)
            ->first();

        expect($relatedActive)->not->toBeNull();
    }
});

it('creates event logs that are causally aligned with referenced entities', function () {
    $licenseEvents = EventLog::query()
        ->whereIn('event_type', [
            EventType::LICENSE_CREATED->value,
            EventType::LICENSE_ACTIVATED->value,
            EventType::LICENSE_UPGRADED->value,
            EventType::LICENSE_SUSPENDED->value,
            EventType::LICENSE_EXPIRED->value,
            EventType::LICENSE_REVOKED->value,
            EventType::DEVICE_BOUND->value,
            EventType::DEVICE_UNBOUND->value,
        ])
        ->get();

    expect($licenseEvents)->not->toBeEmpty();

    foreach ($licenseEvents as $event) {
        if ($event->license_id) {
            $license = License::query()->find($event->license_id);
            expect($license)->not->toBeNull();
            expect($event->created_at->greaterThanOrEqualTo($license->created_at))->toBeTrue();
        }

        if ($event->account_id) {
            $accountCreatedAt = $event->account?->created_at;
            if ($accountCreatedAt) {
                expect($event->created_at->greaterThanOrEqualTo($accountCreatedAt))->toBeTrue();
            }
        }

        if (in_array($event->event_type, [EventType::DEVICE_BOUND->value, EventType::DEVICE_UNBOUND->value], true)) {
            expect($event->details)->toHaveKey('device_id');

            $deviceId = (int) ($event->details['device_id'] ?? 0);
            $device = AccountDevice::query()->find($deviceId);
            expect($device)->not->toBeNull();
            expect($device?->account_id)->toBe($event->account_id);
        }
    }
});
