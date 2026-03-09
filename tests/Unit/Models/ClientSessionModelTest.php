<?php

use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;

beforeEach(function () {
    $this->account = Account::factory()->create();
    $this->device = AccountDevice::factory()->create(['account_id' => $this->account->id]);
    $this->session = ClientSession::factory()->create([
        'account_id' => $this->account->id,
        'device_id' => $this->device->id,
        'last_heartbeat_at' => now()->subMinutes(2),
    ]);
});

// --- isActive ---

it('returns true when session has recent heartbeat', function () {
    expect($this->session->isActive())->toBeTrue();
});

it('returns false when session heartbeat is older than threshold', function () {
    $this->session->update(['last_heartbeat_at' => now()->subMinutes(10)]);

    expect($this->session->fresh()->isActive())->toBeFalse();
});

it('returns false when last_heartbeat_at is null', function () {
    $this->session->update(['last_heartbeat_at' => null]);

    expect($this->session->fresh()->isActive())->toBeFalse();
});

it('respects custom minutes threshold', function () {
    $this->session->update(['last_heartbeat_at' => now()->subMinutes(8)]);

    expect($this->session->fresh()->isActive(5))->toBeFalse();
    expect($this->session->fresh()->isActive(10))->toBeTrue();
});

// --- Scopes ---

it('active scope returns sessions with recent heartbeat', function () {
    $activeSession = ClientSession::factory()->create([
        'account_id' => $this->account->id,
        'device_id' => $this->device->id,
        'last_heartbeat_at' => now()->subMinutes(1),
    ]);

    $expiredSession = ClientSession::factory()->create([
        'account_id' => $this->account->id,
        'device_id' => $this->device->id,
        'last_heartbeat_at' => now()->subHours(1),
    ]);

    $activeSessions = ClientSession::active()->get();

    $activeIds = $activeSessions->pluck('id');
    expect($activeIds->contains($activeSession->id))->toBeTrue();
    expect($activeIds->contains($expiredSession->id))->toBeFalse();
});

it('expired scope returns sessions with old heartbeat', function () {
    $expiredSession = ClientSession::factory()->create([
        'account_id' => $this->account->id,
        'device_id' => $this->device->id,
        'last_heartbeat_at' => now()->subHours(1),
    ]);

    $activeSession = ClientSession::factory()->create([
        'account_id' => $this->account->id,
        'device_id' => $this->device->id,
        'last_heartbeat_at' => now()->subMinutes(1),
    ]);

    $expiredSessions = ClientSession::expired()->get();

    $expiredIds = $expiredSessions->pluck('id');
    expect($expiredIds->contains($expiredSession->id))->toBeTrue();
    expect($expiredIds->contains($activeSession->id))->toBeFalse();
});

it('expired scope includes sessions with null heartbeat', function () {
    $nullSession = ClientSession::factory()->create([
        'account_id' => $this->account->id,
        'device_id' => $this->device->id,
        'last_heartbeat_at' => null,
    ]);

    $expiredSessions = ClientSession::expired()->get();

    expect($expiredSessions->pluck('id')->contains($nullSession->id))->toBeTrue();
});

it('forAccount scope filters by account id', function () {
    $otherAccount = Account::factory()->create();
    $otherDevice = AccountDevice::factory()->create(['account_id' => $otherAccount->id]);

    ClientSession::factory()->create([
        'account_id' => $otherAccount->id,
        'device_id' => $otherDevice->id,
    ]);

    $sessions = ClientSession::forAccount($this->account->id)->get();

    foreach ($sessions as $session) {
        expect($session->account_id)->toBe($this->account->id);
    }
});

it('forDevice scope filters by device id', function () {
    $otherDevice = AccountDevice::factory()
        ->neverBound()
        ->create(['account_id' => $this->account->id]);

    ClientSession::factory()->create([
        'account_id' => $this->account->id,
        'device_id' => $otherDevice->id,
    ]);

    $sessions = ClientSession::forDevice($this->device->id)->get();

    foreach ($sessions as $session) {
        expect($session->device_id)->toBe($this->device->id);
    }
});

it('orderByRecent scope orders by last_heartbeat_at descending', function () {
    $older = ClientSession::factory()->create([
        'account_id' => $this->account->id,
        'device_id' => $this->device->id,
        'last_heartbeat_at' => now()->subMinutes(10),
    ]);

    $newer = ClientSession::factory()->create([
        'account_id' => $this->account->id,
        'device_id' => $this->device->id,
        'last_heartbeat_at' => now()->subMinutes(1),
    ]);

    $sessions = ClientSession::orderByRecent()->get();

    expect($sessions->first()->last_heartbeat_at->gte($sessions->last()->last_heartbeat_at))->toBeTrue();
});

// --- Computed Attributes ---

it('age_in_minutes attribute returns positive value for existing session', function () {
    expect($this->session->age_in_minutes)->toBeGreaterThanOrEqual(0);
});

it('time_since_last_heartbeat attribute returns positive value', function () {
    expect($this->session->time_since_last_heartbeat)->toBeGreaterThanOrEqual(0);
});

it('time_since_last_heartbeat returns null when heartbeat is null', function () {
    $this->session->update(['last_heartbeat_at' => null]);

    expect($this->session->fresh()->time_since_last_heartbeat)->toBeNull();
});

// --- Relationships ---

it('can access account relationship', function () {
    expect($this->session->account->id)->toBe($this->account->id);
});

it('can access device relationship', function () {
    expect($this->session->device->id)->toBe($this->device->id);
});
