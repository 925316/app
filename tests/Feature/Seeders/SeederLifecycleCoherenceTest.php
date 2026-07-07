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

    // AccountSeeder already creates one active device per account;

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

it('seeds template-based normal user journey events for active accounts', function () {
    $activeAccountIds = License::query()
        ->where('status', LicenseStatus::ACTIVE->value)
        ->whereNotNull('used_by')
        ->pluck('used_by')
        ->unique()
        ->values();

    expect($activeAccountIds)->not->toBeEmpty();

    $sampleAccountIds = $activeAccountIds->take(8)->all();

    foreach ($sampleAccountIds as $accountId) {
        $events = EventLog::query()
            ->where('account_id', $accountId)
            ->orderBy('created_at')
            ->get();

        $registeredEvent = $events->first(fn (EventLog $event): bool => $event->event_type === EventType::ACCOUNT_REGISTERED->value);
        $clientLoginEvent = $events->first(fn (EventLog $event): bool => $event->event_type === EventType::ACCOUNT_LOGIN->value
            && (($event->details['step'] ?? null) === 'client_login'));
        $heartbeatEvent = $events->first(fn (EventLog $event): bool => $event->event_type === EventType::ACCOUNT_LOGIN->value
            && (($event->details['step'] ?? null) === 'heartbeat_check'));
        $logoutEvent = $events->first(fn (EventLog $event): bool => $event->event_type === EventType::ACCOUNT_LOGOUT->value);

        expect($registeredEvent)->not->toBeNull();
        expect($clientLoginEvent)->not->toBeNull();
        expect($heartbeatEvent)->not->toBeNull();
        expect($logoutEvent)->not->toBeNull();

        expect($clientLoginEvent->created_at->greaterThanOrEqualTo($registeredEvent->created_at))->toBeTrue();
        expect($heartbeatEvent->created_at->greaterThanOrEqualTo($registeredEvent->created_at))->toBeTrue();
        expect($logoutEvent->created_at->greaterThanOrEqualTo($heartbeatEvent->created_at))->toBeTrue();
    }
});

it('seeds branch flow events for password maintenance and device migration', function () {
    $passwordBranchCount = EventLog::query()
        ->where('event_type', EventType::ACCOUNT_PROFILE_UPDATED->value)
        ->get()
        ->filter(fn (EventLog $event): bool => ($event->details['flow'] ?? null) === 'password_maintenance')
        ->count();

    $hwidMigrationCount = EventLog::query()
        ->where('event_type', EventType::ACCOUNT_HWID_RESET->value)
        ->get()
        ->filter(fn (EventLog $event): bool => ($event->details['flow'] ?? null) === 'device_migration')
        ->count();

    expect($passwordBranchCount)->toBeGreaterThanOrEqual(1);
    expect($hwidMigrationCount)->toBeGreaterThanOrEqual(1);
});
