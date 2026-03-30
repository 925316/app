<?php

use App\Enums\EventType;
use App\Models\Account;
use App\Models\EventLog;

use function Pest\Laravel\actingAs;

// --- Index ---

it('admin can view logs index', function () {
    $admin = createAdmin();

    actingAs($admin)
        ->get(route('logs.index'))
        ->assertSuccessful()
        ->assertSee('data-page="logs-index"', false)
        ->assertSee('data-filter-box', false)
        ->assertSee('data-clear-logs-form', false)
        ->assertViewIs('logs.index')
        ->assertViewHasAll(['logs', 'statistics', 'eventTypes', 'eventLevels', 'filters']);
});

it('logs index shows statistics with correct keys', function () {
    $admin = createAdmin();

    actingAs($admin)
        ->get(route('logs.index'))
        ->assertViewHas('statistics', fn ($s) => array_key_exists('total', $s)
            && array_key_exists('info', $s)
            && array_key_exists('warning', $s)
            && array_key_exists('error', $s)
        );
});

it('admin can filter logs by event type', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['event_type' => EventType::LICENSE_ACTIVATED->value]);
    EventLog::factory()->create(['event_type' => EventType::LICENSE_REVOKED->value]);

    $response = actingAs($admin)
        ->get(route('logs.index', ['event_type' => EventType::LICENSE_ACTIVATED->value]));

    $response->assertSuccessful()
        ->assertSee('data-active-filters', false);
    $logs = $response->viewData('logs');
    expect($logs->total())->toBe(1);
    expect($logs->first()->event_type)->toBe(EventType::LICENSE_ACTIVATED->value);
});

it('admin can filter logs by event level', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['event_level' => EventLog::LEVEL_INFO]);
    EventLog::factory()->create(['event_level' => EventLog::LEVEL_ERROR]);

    $response = actingAs($admin)
        ->get(route('logs.index', ['event_level' => EventLog::LEVEL_ERROR]));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');
    expect($logs->total())->toBe(1);
});

it('admin can filter logs by account', function () {
    $admin = createAdmin();

    $account = Account::factory()->create();
    EventLog::factory()->create(['account_id' => $account->id]);
    EventLog::factory()->create(['account_id' => null]);

    $response = actingAs($admin)
        ->get(route('logs.index', ['account_id' => $account->id]));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');
    expect($logs->total())->toBe(1);
});

it('admin can filter logs by date range', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['created_at' => now()->subDays(5)]);
    EventLog::factory()->create(['created_at' => now()->subDays(60)]);

    $response = actingAs($admin)
        ->get(route('logs.index', [
            'start_date' => now()->subDays(10)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');
    expect($logs->total())->toBe(1);
});

it('end date filter includes logs created on that date', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['created_at' => now()->subDays(2)->setTime(23, 0, 0)]);
    EventLog::factory()->create(['created_at' => now()->subDays(3)->setTime(23, 59, 59)]);

    $response = actingAs($admin)
        ->get(route('logs.index', [
            'start_date' => now()->subDays(3)->format('Y-m-d'),
            'end_date' => now()->subDays(2)->format('Y-m-d'),
        ]));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');
    expect($logs->total())->toBe(2);
});

it('inverted date range returns safely with no matches', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['created_at' => now()->subDays(2)]);
    EventLog::factory()->create(['created_at' => now()->subDays(6)]);

    $response = actingAs($admin)
        ->get(route('logs.index', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->subDays(10)->format('Y-m-d'),
        ]));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');
    expect($logs->total())->toBe(0);
});

it('malformed event level filter does not crash index', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['event_level' => EventLog::LEVEL_INFO]);
    EventLog::factory()->create(['event_level' => EventLog::LEVEL_WARN]);

    $response = actingAs($admin)
        ->get(route('logs.index', ['event_level' => 'not-an-int']));

    $response->assertSuccessful()
        ->assertViewHas('logs');
});

it('non numeric account id filter is handled safely', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['account_id' => null]);

    $response = actingAs($admin)
        ->get(route('logs.index', ['account_id' => 'abc']));

    $response->assertSuccessful()
        ->assertViewHas('logs');
});

it('admin can search logs by ip address', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['ip_address' => '10.0.0.1']);
    EventLog::factory()->create(['ip_address' => '192.168.1.1']);

    $response = actingAs($admin)
        ->get(route('logs.index', ['search' => '10.0.0.1']));

    $response->assertSuccessful();
    $response->assertViewHas('logs');
});

it('admin can search logs by account username', function () {
    $admin = createAdmin();

    $user = Account::factory()->create(['username' => 'findableuser']);
    EventLog::factory()->create(['account_id' => $user->id]);

    $response = actingAs($admin)
        ->get(route('logs.index', ['search' => 'findableuser']));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');
    expect($logs->total())->toBeGreaterThanOrEqual(1);
});

it('admin can search logs by event type and account email', function () {
    $admin = createAdmin();

    $account = Account::factory()->create(['email' => 'log-search@example.com']);

    EventLog::factory()->create([
        'event_type' => EventType::ACCOUNT_LOGIN->value,
        'account_id' => $account->id,
    ]);

    EventLog::factory()->create([
        'event_type' => EventType::LICENSE_REVOKED->value,
    ]);

    $byType = actingAs($admin)
        ->get(route('logs.index', ['search' => EventType::ACCOUNT_LOGIN->value]));
    $byType->assertSuccessful();
    expect($byType->viewData('logs')->total())->toBeGreaterThanOrEqual(1);

    $byEmail = actingAs($admin)
        ->get(route('logs.index', ['search' => 'log-search@example.com']));
    $byEmail->assertSuccessful();
    expect($byEmail->viewData('logs')->total())->toBeGreaterThanOrEqual(1);
});

it('logs pagination preserves applied filters', function () {
    $admin = createAdmin();

    EventLog::factory()->count(30)->create([
        'event_type' => EventType::ACCOUNT_LOGIN->value,
    ]);

    EventLog::factory()->count(10)->create([
        'event_type' => EventType::LICENSE_REVOKED->value,
    ]);

    $response = actingAs($admin)
        ->get(route('logs.index', [
            'event_type' => EventType::ACCOUNT_LOGIN->value,
        ]));

    $response->assertSuccessful();
    $logs = $response->viewData('logs');

    expect($logs->nextPageUrl())->toContain('event_type='.urlencode(EventType::ACCOUNT_LOGIN->value));
});

// --- Show ---

it('admin can view log details', function () {
    $admin = createAdmin();

    $log = EventLog::factory()->create(['event_type' => EventType::LICENSE_ACTIVATED->value]);

    actingAs($admin)
        ->get(route('logs.show', $log))
        ->assertSuccessful()
        ->assertViewIs('logs.show')
        ->assertViewHas('log');
});

// --- Clear ---

it('admin can clear old logs', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['created_at' => now()->subDays(40)]);
    EventLog::factory()->create(['created_at' => now()->subDays(10)]);

    actingAs($admin)
        ->post(route('logs.clear'), ['days' => 30])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(EventLog::where('created_at', '<=', now()->subDays(30))->count())->toBe(0);
    expect(EventLog::count())->toBe(1);
});

it('clear logs validates days field is required', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['created_at' => now()->subDays(40)]);
    EventLog::factory()->create(['created_at' => now()->subDays(10)]);

    actingAs($admin)
        ->post(route('logs.clear'), [])
        ->assertSessionHasErrors('days');

    expect(EventLog::count())->toBe(2);
});

it('clear logs validates days minimum of 1', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['created_at' => now()->subDays(40)]);

    actingAs($admin)
        ->post(route('logs.clear'), ['days' => 0])
        ->assertSessionHasErrors('days');

    expect(EventLog::count())->toBe(1);
});

it('clear logs validates days maximum of 365', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['created_at' => now()->subDays(40)]);

    actingAs($admin)
        ->post(route('logs.clear'), ['days' => 400])
        ->assertSessionHasErrors('days');

    expect(EventLog::count())->toBe(1);
});

it('clear logs validates days must be integer and keeps data intact', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['created_at' => now()->subDays(40)]);
    EventLog::factory()->create(['created_at' => now()->subDays(5)]);

    actingAs($admin)
        ->post(route('logs.clear'), ['days' => 'thirty'])
        ->assertSessionHasErrors('days');

    expect(EventLog::count())->toBe(2);
});

it('clear logs deletes entries exactly on the cutoff boundary', function () {
    $admin = createAdmin();

    $fixedNow = now()->startOfSecond();
    \Illuminate\Support\Carbon::setTestNow($fixedNow);

    EventLog::factory()->create(['created_at' => $fixedNow->copy()->subDays(30)]);
    EventLog::factory()->create(['created_at' => $fixedNow->copy()->subDays(29)]);

    actingAs($admin)
        ->post(route('logs.clear'), ['days' => 30])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(EventLog::count())->toBe(1);

    \Illuminate\Support\Carbon::setTestNow();
});

it('clear logs accepts boundary values one and three hundred sixty five', function () {
    $admin = createAdmin();

    EventLog::factory()->create(['created_at' => now()->subDays(2)]);
    EventLog::factory()->create(['created_at' => now()->subDays(370)]);

    actingAs($admin)
        ->post(route('logs.clear'), ['days' => 1])
        ->assertRedirect()
        ->assertSessionHas('success');

    EventLog::factory()->create(['created_at' => now()->subDays(370)]);

    actingAs($admin)
        ->post(route('logs.clear'), ['days' => 365])
        ->assertRedirect()
        ->assertSessionHas('success');
});
