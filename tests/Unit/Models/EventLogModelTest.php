<?php

use App\Enums\EventType;
use App\Models\Account;
use App\Models\EventLog;
use App\Models\License;

beforeEach(function () {
    $this->account = Account::factory()->create();
    $this->license = License::factory()->create();
});

it('can log an event using static method', function () {
    $event = EventLog::log(
        EventType::LICENSE_ACTIVATED,
        EventLog::LEVEL_INFO,
        [
            'account_id' => $this->account->id,
            'license_id' => $this->license->id,
        ]
    );

    expect($event)->toBeInstanceOf(EventLog::class);
    expect($event->event_type)->toBe(EventType::LICENSE_ACTIVATED->value);
    expect($event->event_level)->toBe(EventLog::LEVEL_INFO);
    expect($event->account_id)->toBe($this->account->id);
});

it('can log info level event', function () {
    $event = EventLog::info(EventType::LICENSE_ACTIVATED, [
        'account_id' => $this->account->id,
    ]);

    expect($event->event_level)->toBe(EventLog::LEVEL_INFO);
    expect($event->is_info)->toBeTrue();
});

it('can log warning level event', function () {
    $event = EventLog::warning(EventType::LICENSE_SUSPENDED, [
        'account_id' => $this->account->id,
    ]);

    expect($event->event_level)->toBe(EventLog::LEVEL_WARN);
    expect($event->is_warning)->toBeTrue();
});

it('can log error level event', function () {
    $event = EventLog::error(EventType::LICENSE_REVOKED, [
        'account_id' => $this->account->id,
    ]);

    expect($event->event_level)->toBe(EventLog::LEVEL_ERROR);
    expect($event->is_error)->toBeTrue();
});

it('can get level text attribute', function () {
    $event = EventLog::factory()->create(['event_level' => EventLog::LEVEL_INFO]);
    expect($event->level_text)->toBe('Info');

    $event = EventLog::factory()->create(['event_level' => EventLog::LEVEL_WARN]);
    expect($event->level_text)->toBe('Warning');

    $event = EventLog::factory()->create(['event_level' => EventLog::LEVEL_ERROR]);
    expect($event->level_text)->toBe('Error');
});

it('can get level class attribute', function () {
    $event = EventLog::factory()->create(['event_level' => EventLog::LEVEL_INFO]);
    expect($event->level_class)->toBe('info');

    $event = EventLog::factory()->create(['event_level' => EventLog::LEVEL_WARN]);
    expect($event->level_class)->toBe('warning');

    $event = EventLog::factory()->create(['event_level' => EventLog::LEVEL_ERROR]);
    expect($event->level_class)->toBe('danger');
});

it('filters by info level', function () {
    EventLog::factory()->create(['event_level' => EventLog::LEVEL_INFO]);
    EventLog::factory()->create(['event_level' => EventLog::LEVEL_ERROR]);

    $infoEvents = EventLog::where('event_level', EventLog::LEVEL_INFO)->get();

    expect($infoEvents)->toHaveCount(1);
});

it('filters by warning level', function () {
    EventLog::factory()->create(['event_level' => EventLog::LEVEL_WARN]);
    EventLog::factory()->create(['event_level' => EventLog::LEVEL_INFO]);

    $warningEvents = EventLog::where('event_level', EventLog::LEVEL_WARN)->get();

    expect($warningEvents)->toHaveCount(1);
});

it('filters by error level', function () {
    EventLog::factory()->create(['event_level' => EventLog::LEVEL_ERROR]);
    EventLog::factory()->create(['event_level' => EventLog::LEVEL_INFO]);

    $errorEvents = EventLog::where('event_level', EventLog::LEVEL_ERROR)->get();

    expect($errorEvents)->toHaveCount(1);
});

it('has of type scope', function () {
    EventLog::factory()->create(['event_type' => EventType::LICENSE_ACTIVATED->value]);
    EventLog::factory()->create(['event_type' => EventType::LICENSE_REVOKED->value]);

    $events = EventLog::ofType(EventType::LICENSE_ACTIVATED->value)->get();

    expect($events)->toHaveCount(1);
});

it('has of event type scope', function () {
    EventLog::factory()->create(['event_type' => EventType::LICENSE_ACTIVATED->value]);
    EventLog::factory()->create(['event_type' => EventType::LICENSE_REVOKED->value]);

    $events = EventLog::ofEventType(EventType::LICENSE_ACTIVATED)->get();

    expect($events)->toHaveCount(1);
});

it('has for account scope', function () {
    EventLog::factory()->create(['account_id' => $this->account->id]);
    EventLog::factory()->create(['account_id' => null]);

    $events = EventLog::forAccount($this->account->id)->get();

    expect($events)->toHaveCount(1);
});

it('has for license scope', function () {
    EventLog::factory()->create(['license_id' => $this->license->id]);
    EventLog::factory()->create(['license_id' => null]);

    $events = EventLog::forLicense($this->license->id)->get();

    expect($events)->toHaveCount(1);
});

it('has recent scope', function () {
    EventLog::factory()->create(['created_at' => now()->subDays(5)]);
    EventLog::factory()->create(['created_at' => now()->subDays(60)]);

    $events = EventLog::recent(30)->get();

    expect($events)->toHaveCount(1);
});

it('has between dates scope', function () {
    EventLog::factory()->create(['created_at' => now()->subDays(5)]);
    EventLog::factory()->create(['created_at' => now()->subDays(60)]);

    $events = EventLog::betweenDates(now()->subDays(10), now())->get();

    expect($events)->toHaveCount(1);
});

it('can access account relationship', function () {
    $event = EventLog::factory()->create(['account_id' => $this->account->id]);

    expect($event->account->id)->toBe($this->account->id);
});

it('can access license relationship', function () {
    $event = EventLog::factory()->create(['license_id' => $this->license->id]);

    expect($event->license->id)->toBe($this->license->id);
});

it('can access actor relationship', function () {
    $actor = Account::factory()->create();
    $event = EventLog::factory()->create(['actor_id' => $actor->id]);

    expect($event->actor->id)->toBe($actor->id);
});

it('can get type label attribute', function () {
    $event = EventLog::factory()->create(['event_type' => EventType::LICENSE_ACTIVATED->value]);

    expect($event->type_label)->not->toBeEmpty();
});

it('can get category attribute', function () {
    $event = EventLog::factory()->create(['event_type' => EventType::LICENSE_ACTIVATED->value]);

    expect($event->category)->not->toBeEmpty();
});

it('can get safe ip attribute', function () {
    $event = EventLog::factory()->create(['ip_address' => '192.168.1.100']);

    expect($event->safe_ip)->toContain('.xxx');
});
