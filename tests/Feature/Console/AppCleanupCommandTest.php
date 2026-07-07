<?php

use App\Models\ApiSigningKey;
use App\Models\ClientSession;
use App\Models\EventLog;

it('reports cleanup counts without deleting records in dry run mode', function () {
    EventLog::factory()->create(['created_at' => now()->subDays(120)]);
    ClientSession::factory()->noHeartbeat()->create(['created_at' => now()->subDays(2)]);
    ApiSigningKey::factory()->retired(400)->create();

    $this->artisan('app:cleanup --dry-run --logs-days=90 --sessions-minutes=1440 --retired-key-days=365')
        ->expectsOutput('Would delete 1 event log records older than 90 days.')
        ->expectsOutput('Would delete 1 stale client session records older than 1440 minutes.')
        ->expectsOutput('Would delete 1 retired API signing key metadata records older than 365 days.')
        ->assertExitCode(0);

    expect(EventLog::count())->toBe(1)
        ->and(ClientSession::count())->toBe(1)
        ->and(ApiSigningKey::count())->toBe(1);
});

it('deletes only stale cleanup targets', function () {
    $oldLog = EventLog::factory()->create(['created_at' => now()->subDays(120)]);
    $recentLog = EventLog::factory()->create(['created_at' => now()->subDays(2)]);
    $staleSession = ClientSession::factory()->create([
        'created_at' => now()->subDays(2),
        'last_heartbeat_at' => now()->subMinutes(2000),
        'updated_at' => now()->subMinutes(2000),
    ]);
    $activeSession = ClientSession::factory()->active()->create();
    $oldRetiredKey = ApiSigningKey::factory()->retired(400)->create();
    $activeKey = ApiSigningKey::factory()->active()->create();

    $this->artisan('app:cleanup --logs-days=90 --sessions-minutes=1440 --retired-key-days=365')
        ->expectsOutput('Deleted 1 event log records older than 90 days.')
        ->expectsOutput('Deleted 1 stale client session records older than 1440 minutes.')
        ->expectsOutput('Deleted 1 retired API signing key metadata records older than 365 days.')
        ->assertExitCode(0);

    expect(EventLog::query()->whereKey($oldLog)->exists())->toBeFalse()
        ->and(EventLog::query()->whereKey($recentLog)->exists())->toBeTrue()
        ->and(ClientSession::query()->whereKey($staleSession)->exists())->toBeFalse()
        ->and(ClientSession::query()->whereKey($activeSession)->exists())->toBeTrue()
        ->and(ApiSigningKey::query()->whereKey($oldRetiredKey)->exists())->toBeFalse()
        ->and(ApiSigningKey::query()->whereKey($activeKey)->exists())->toBeTrue();
});
