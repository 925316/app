<?php

use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;

beforeEach(function () {
    $this->admin = createAdmin();
});

// --- Index ---

it('admin can view sessions index', function () {
    $this->actingAs($this->admin)
        ->get(route('sessions.index'))
        ->assertSuccessful()
        ->assertViewIs('sessions.index')
        ->assertViewHasAll(['sessions', 'statistics', 'statusOptions', 'currentFilters']);
});

it('sessions index shows statistics', function () {
    $this->actingAs($this->admin)
        ->get(route('sessions.index'))
        ->assertViewHas('statistics', fn ($s) => array_key_exists('total', $s)
            && array_key_exists('active', $s)
            && array_key_exists('expired', $s)
        );
});

it('admin can filter sessions by active status', function () {
    $account = Account::factory()->create();
    $device = AccountDevice::factory()->create(['account_id' => $account->id]);

    ClientSession::factory()->create([
        'account_id' => $account->id,
        'device_id' => $device->id,
        'last_heartbeat_at' => now()->subMinutes(1),
    ]);

    ClientSession::factory()->create([
        'account_id' => $account->id,
        'device_id' => $device->id,
        'last_heartbeat_at' => now()->subHours(2),
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('sessions.index', ['status' => 'active']));

    $response->assertSuccessful();
    $sessions = $response->viewData('sessions');
    expect($sessions->total())->toBe(1);
});

it('admin can filter sessions by expired status', function () {
    $account = Account::factory()->create();
    $device = AccountDevice::factory()->create(['account_id' => $account->id]);

    ClientSession::factory()->create([
        'account_id' => $account->id,
        'device_id' => $device->id,
        'last_heartbeat_at' => now()->subHours(2),
    ]);

    ClientSession::factory()->create([
        'account_id' => $account->id,
        'device_id' => $device->id,
        'last_heartbeat_at' => now()->subMinutes(1),
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('sessions.index', ['status' => 'expired']));

    $response->assertSuccessful();
    $sessions = $response->viewData('sessions');
    expect($sessions->total())->toBe(1);
});

it('admin can search sessions by account username', function () {
    $user = Account::factory()->create(['username' => 'searchableuser']);
    $device = AccountDevice::factory()->create(['account_id' => $user->id]);

    ClientSession::factory()->create([
        'account_id' => $user->id,
        'device_id' => $device->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('sessions.index', ['search' => 'searchableuser']));

    $response->assertSuccessful();
    $sessions = $response->viewData('sessions');
    expect($sessions->total())->toBe(1);
});

it('invalid sort and direction fallback to safe defaults', function () {
    $account = Account::factory()->create();
    $device = AccountDevice::factory()->create(['account_id' => $account->id]);

    ClientSession::factory()->create([
        'account_id' => $account->id,
        'device_id' => $device->id,
        'created_at' => now()->subMinute(),
        'last_heartbeat_at' => now()->subMinutes(5),
    ]);

    ClientSession::factory()->create([
        'account_id' => $account->id,
        'device_id' => $device->id,
        'created_at' => now()->subDays(1),
        'last_heartbeat_at' => now()->subMinutes(1),
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('sessions.index', [
            'sort' => 'drop_table',
            'direction' => 'sideways',
        ]));

    $response->assertSuccessful();
    $response->assertViewHas('currentFilters', fn (array $filters) => $filters['sort'] === 'last_heartbeat_at'
        && $filters['direction'] === 'desc');
});

it('invalid status filter does not break index and returns unfiltered results', function () {
    $account = Account::factory()->create();
    $device = AccountDevice::factory()->create(['account_id' => $account->id]);

    ClientSession::factory()->create([
        'account_id' => $account->id,
        'device_id' => $device->id,
        'last_heartbeat_at' => now()->subMinutes(1),
    ]);

    ClientSession::factory()->create([
        'account_id' => $account->id,
        'device_id' => $device->id,
        'last_heartbeat_at' => now()->subHours(2),
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('sessions.index', ['status' => 'garbage-status']));

    $response->assertSuccessful();
    $sessions = $response->viewData('sessions');
    expect($sessions->total())->toBe(2);
});

// --- Show ---

it('admin can view session details', function () {
    $account = Account::factory()->create();
    $device = AccountDevice::factory()->create(['account_id' => $account->id]);
    $session = ClientSession::factory()->create([
        'account_id' => $account->id,
        'device_id' => $device->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('sessions.show', $session))
        ->assertSuccessful()
        ->assertViewIs('sessions.show')
        ->assertViewHas('session');
});

// --- Destroy ---

it('admin can terminate a session', function () {
    $account = Account::factory()->create();
    $device = AccountDevice::factory()->create(['account_id' => $account->id]);
    $session = ClientSession::factory()->create([
        'account_id' => $account->id,
        'device_id' => $device->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('sessions.destroy', $session))
        ->assertRedirect(route('sessions.index'))
        ->assertSessionHas('success');

    expect(ClientSession::find($session->id))->toBeNull();
});

it('guest is redirected from session admin routes', function () {
    $account = Account::factory()->create();
    $device = AccountDevice::factory()->create(['account_id' => $account->id]);
    $session = ClientSession::factory()->create([
        'account_id' => $account->id,
        'device_id' => $device->id,
    ]);

    $this->get(route('sessions.index'))->assertRedirect(route('login'));
    $this->get(route('sessions.show', $session))->assertRedirect(route('login'));
    $this->delete(route('sessions.destroy', $session))->assertRedirect(route('login'));
});

it('session admin routes return not found for missing session model', function () {
    $this->actingAs($this->admin)
        ->get(route('sessions.show', 999999))
        ->assertNotFound();

    $this->actingAs($this->admin)
        ->delete(route('sessions.destroy', 999999))
        ->assertNotFound();
});
