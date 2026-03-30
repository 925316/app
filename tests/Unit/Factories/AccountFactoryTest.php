<?php

use App\Models\Account;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-03-22 12:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('creates valid ordered timestamps when test now is fixed', function () {
    $account = Account::factory()->withTwoFactor()->suspended()->create();

    expect($account->created_at)->not->toBeNull()
        ->and($account->updated_at)->not->toBeNull()
        ->and($account->created_at->lessThanOrEqualTo($account->updated_at))->toBeTrue()
        ->and($account->email_verified_at)->not->toBeNull()
        ->and($account->created_at->lessThanOrEqualTo($account->email_verified_at))->toBeTrue()
        ->and($account->two_factor_confirmed_at)->not->toBeNull()
        ->and($account->email_verified_at->lessThanOrEqualTo($account->two_factor_confirmed_at))->toBeTrue()
        ->and($account->two_factor_confirmed_at->lessThanOrEqualTo(now()))->toBeTrue();

    expect($account->is_suspended)->toBeTrue();

    if ($account->suspended_until !== null) {
        expect($account->suspended_until->greaterThanOrEqualTo(now()))->toBeTrue();
    }
});
