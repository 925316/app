<?php

use App\Enums\EventType;
use App\Models\Account;
use App\Models\EventLog;

beforeEach(function () {
    $this->admin = createAdmin();
});

// --- Index ---

it('admin can view logs index', function () {
    $this->actingAs($this->admin)
        ->get(route('logs.index'))
        ->assertSuccessful()
        ->assertViewIs('logs.index')
        ->assertViewHasAll(['logs', 'statistics', 'eventTypes', 'eventLevels', 'filters']);
});

it('logs index shows statistics with correct keys', function () {
    $this->actingAs($this->admin)
        ->get(route('logs.index'))
        ->assertViewHas('statistics', fn ($s) => array_key_exists('total', $s)
            && array_key_exists('info', $s)
            && array_key_exists('warning', $s)
            && array_key_exists('error', $s)
        );
});

it('admin can filter logs by event type', function () {
    EventLog::factory()->create(['event_type' => EventType::LICENSE_ACTIVATED->value]);
    EventLog::factory()->create(['event_type' => EventType::LICENSE_REVOKED->value]);

    $response = $this->actingAs($this->admin)
        ->get(route('logs.index', ['event_type' => EventType::LICENSE_ACTIVATED->value]));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');
    expect($logs->total())->toBe(1);
    expect($logs->first()->event_type)->toBe(EventType::LICENSE_ACTIVATED->value);
});

it('admin can filter logs by event level', function () {
    EventLog::factory()->create(['event_level' => EventLog::LEVEL_INFO]);
    EventLog::factory()->create(['event_level' => EventLog::LEVEL_ERROR]);

    $response = $this->actingAs($this->admin)
        ->get(route('logs.index', ['event_level' => EventLog::LEVEL_ERROR]));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');
    expect($logs->total())->toBe(1);
});

it('admin can filter logs by account', function () {
    $account = Account::factory()->create();
    EventLog::factory()->create(['account_id' => $account->id]);
    EventLog::factory()->create(['account_id' => null]);

    $response = $this->actingAs($this->admin)
        ->get(route('logs.index', ['account_id' => $account->id]));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');
    expect($logs->total())->toBe(1);
});

it('admin can filter logs by date range', function () {
    EventLog::factory()->create(['created_at' => now()->subDays(5)]);
    EventLog::factory()->create(['created_at' => now()->subDays(60)]);

    $response = $this->actingAs($this->admin)
        ->get(route('logs.index', [
            'start_date' => now()->subDays(10)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');
    expect($logs->total())->toBe(1);
});

it('inverted date range returns safely with no matches', function () {
    EventLog::factory()->create(['created_at' => now()->subDays(2)]);
    EventLog::factory()->create(['created_at' => now()->subDays(6)]);

    $response = $this->actingAs($this->admin)
        ->get(route('logs.index', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->subDays(10)->format('Y-m-d'),
        ]));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');
    expect($logs->total())->toBe(0);
});

it('malformed event level filter does not crash index', function () {
    EventLog::factory()->create(['event_level' => EventLog::LEVEL_INFO]);
    EventLog::factory()->create(['event_level' => EventLog::LEVEL_WARN]);

    $response = $this->actingAs($this->admin)
        ->get(route('logs.index', ['event_level' => 'not-an-int']));

    $response->assertSuccessful()
        ->assertViewHas('logs');
});

it('non numeric account id filter is handled safely', function () {
    EventLog::factory()->create(['account_id' => null]);

    $response = $this->actingAs($this->admin)
        ->get(route('logs.index', ['account_id' => 'abc']));

    $response->assertSuccessful()
        ->assertViewHas('logs');
});

it('admin can search logs by ip address', function () {
    EventLog::factory()->create(['ip_address' => '10.0.0.1']);
    EventLog::factory()->create(['ip_address' => '192.168.1.1']);

    $response = $this->actingAs($this->admin)
        ->get(route('logs.index', ['search' => '10.0.0.1']));

    $response->assertSuccessful();
    $response->assertViewHas('logs');
});

it('admin can search logs by account username', function () {
    $user = Account::factory()->create(['username' => 'findableuser']);
    EventLog::factory()->create(['account_id' => $user->id]);

    $response = $this->actingAs($this->admin)
        ->get(route('logs.index', ['search' => 'findableuser']));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');
    expect($logs->total())->toBeGreaterThanOrEqual(1);
});

// --- Show ---

it('admin can view log details', function () {
    $log = EventLog::factory()->create(['event_type' => EventType::LICENSE_ACTIVATED->value]);

    $this->actingAs($this->admin)
        ->get(route('logs.show', $log))
        ->assertSuccessful()
        ->assertViewIs('logs.show')
        ->assertViewHas('log');
});

// --- Clear ---

it('admin can clear old logs', function () {
    EventLog::factory()->create(['created_at' => now()->subDays(40)]);
    EventLog::factory()->create(['created_at' => now()->subDays(10)]);

    $this->actingAs($this->admin)
        ->post(route('logs.clear'), ['days' => 30])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(EventLog::where('created_at', '<=', now()->subDays(30))->count())->toBe(0);
    expect(EventLog::count())->toBe(1);
});

it('clear logs validates days field is required', function () {
    EventLog::factory()->create(['created_at' => now()->subDays(40)]);
    EventLog::factory()->create(['created_at' => now()->subDays(10)]);

    $this->actingAs($this->admin)
        ->post(route('logs.clear'), [])
        ->assertSessionHasErrors('days');

    expect(EventLog::count())->toBe(2);
});

it('clear logs validates days minimum of 1', function () {
    EventLog::factory()->create(['created_at' => now()->subDays(40)]);

    $this->actingAs($this->admin)
        ->post(route('logs.clear'), ['days' => 0])
        ->assertSessionHasErrors('days');

    expect(EventLog::count())->toBe(1);
});

it('clear logs validates days maximum of 365', function () {
    EventLog::factory()->create(['created_at' => now()->subDays(40)]);

    $this->actingAs($this->admin)
        ->post(route('logs.clear'), ['days' => 400])
        ->assertSessionHasErrors('days');

    expect(EventLog::count())->toBe(1);
});

it('clear logs validates days must be integer and keeps data intact', function () {
    EventLog::factory()->create(['created_at' => now()->subDays(40)]);
    EventLog::factory()->create(['created_at' => now()->subDays(5)]);

    $this->actingAs($this->admin)
        ->post(route('logs.clear'), ['days' => 'thirty'])
        ->assertSessionHasErrors('days');

    expect(EventLog::count())->toBe(2);
});
