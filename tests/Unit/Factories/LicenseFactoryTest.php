<?php

use App\Enums\LicenseStatus;
use App\Models\License;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-03-22 12:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('active state keeps a valid timeline', function () {
    $license = License::factory()->active()->create();

    expect($license->status)->toBe(LicenseStatus::ACTIVE)
        ->and($license->used_by)->not->toBeNull()
        ->and($license->activated_at)->not->toBeNull()
        ->and($license->created_at->lessThanOrEqualTo($license->activated_at))->toBeTrue()
        ->and($license->updated_at->equalTo($license->activated_at))->toBeTrue()
        ->and($license->expires_at->greaterThan(now()))->toBeTrue();
});

it('active state clamps near-future created_at override and avoids inverted ranges', function () {
    $license = License::factory()->state([
        'created_at' => now()->addMinutes(5)->toDateTimeString(),
    ])->active()->create();

    expect($license->created_at->lessThanOrEqualTo(now()->subMinute()))->toBeTrue()
        ->and($license->created_at->lessThanOrEqualTo($license->activated_at))->toBeTrue()
        ->and($license->updated_at->equalTo($license->activated_at))->toBeTrue();
});

it('active state accepts DateTimeInterface created_at override', function () {
    $createdAt = new DateTimeImmutable('2026-03-01 09:15:00');

    $license = License::factory()->state([
        'created_at' => $createdAt,
    ])->active()->create();

    expect($license->created_at->equalTo(Carbon::instance($createdAt)))->toBeTrue()
        ->and($license->created_at->lessThanOrEqualTo($license->activated_at))->toBeTrue();
});

it('suspended state keeps ordered lifecycle timestamps', function () {
    $license = License::factory()->suspended()->create();

    expect($license->status)->toBe(LicenseStatus::SUSPENDED)
        ->and($license->activated_at)->not->toBeNull()
        ->and($license->suspended_at)->not->toBeNull()
        ->and($license->created_at->lessThanOrEqualTo($license->activated_at))->toBeTrue()
        ->and($license->activated_at->lessThanOrEqualTo($license->suspended_at))->toBeTrue()
        ->and($license->updated_at->equalTo($license->suspended_at))->toBeTrue();
});

it('expired state keeps ordered lifecycle timestamps and past expiry', function () {
    $license = License::factory()->expired()->create();

    expect($license->status)->toBe(LicenseStatus::EXPIRED)
        ->and($license->activated_at)->not->toBeNull()
        ->and($license->created_at->lessThanOrEqualTo($license->activated_at))->toBeTrue()
        ->and($license->activated_at->lessThanOrEqualTo($license->expires_at))->toBeTrue()
        ->and($license->updated_at->equalTo($license->expires_at))->toBeTrue()
        ->and($license->expires_at->lessThan(now()))->toBeTrue();
});

it('upgraded state keeps ordered lifecycle timestamps', function () {
    $license = License::factory()->upgraded()->create();

    expect($license->status)->toBe(LicenseStatus::UPGRADED)
        ->and($license->used_by)->not->toBeNull()
        ->and($license->activated_at)->not->toBeNull()
        ->and($license->created_at->lessThanOrEqualTo($license->activated_at))->toBeTrue()
        ->and($license->activated_at->lessThanOrEqualTo($license->updated_at))->toBeTrue();
});

it('revoked state keeps ordered lifecycle timestamps', function () {
    $license = License::factory()->revoked()->create();

    expect($license->status)->toBe(LicenseStatus::REVOKED)
        ->and($license->used_by)->not->toBeNull()
        ->and($license->activated_at)->not->toBeNull()
        ->and($license->created_at->lessThanOrEqualTo($license->activated_at))->toBeTrue()
        ->and($license->activated_at->lessThanOrEqualTo($license->updated_at))->toBeTrue();
});
