<?php

use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\License;
use Database\Seeders\AccountDeviceSeeder;
use Database\Seeders\AccountSeeder;
use Database\Seeders\LicenseSeeder;
use Illuminate\Support\Facades\Validator;

use function Pest\Laravel\seed;

beforeEach(function () {
    seed([
        AccountSeeder::class,
        AccountDeviceSeeder::class,
        LicenseSeeder::class,
    ]);
});

it('seeds coherent 2fa and verification timelines', function () {
    $confirmedWithoutSecret = Account::query()
        ->whereNotNull('two_factor_confirmed_at')
        ->whereNull('two_factor_secret')
        ->count();

    $confirmedWithoutRecoveryCodes = Account::query()
        ->whereNotNull('two_factor_confirmed_at')
        ->whereNull('two_factor_recovery_codes')
        ->count();

    $futureVerification = Account::query()
        ->where('email_verified_at', '>', now())
        ->count();

    $futureTwoFactorConfirmation = Account::query()
        ->where('two_factor_confirmed_at', '>', now())
        ->count();

    expect($confirmedWithoutSecret)->toBe(0)
        ->and($confirmedWithoutRecoveryCodes)->toBe(0)
        ->and($futureVerification)->toBe(0)
        ->and($futureTwoFactorConfirmation)->toBe(0);
});

it('seeds coherent device timelines', function () {
    $lastBeforeFirst = AccountDevice::query()
        ->whereColumn('last_seen_at', '<', 'first_seen_at')
        ->count();

    $boundBeforeFirst = AccountDevice::query()
        ->whereNotNull('bound_at')
        ->whereColumn('bound_at', '<', 'first_seen_at')
        ->count();

    $unboundBeforeBound = AccountDevice::query()
        ->whereNotNull('bound_at')
        ->whereNotNull('unbound_at')
        ->whereColumn('unbound_at', '<', 'bound_at')
        ->count();

    $lastAfterUnbound = AccountDevice::query()
        ->whereNotNull('unbound_at')
        ->whereColumn('last_seen_at', '>', 'unbound_at')
        ->count();

    $multipleCurrentBoundPerAccount = AccountDevice::query()
        ->select('account_id')
        ->whereNotNull('bound_at')
        ->whereNull('unbound_at')
        ->groupBy('account_id')
        ->havingRaw('COUNT(*) > 1')
        ->count();

    expect($lastBeforeFirst)->toBe(0)
        ->and($boundBeforeFirst)->toBe(0)
        ->and($unboundBeforeBound)->toBe(0)
        ->and($lastAfterUnbound)->toBe(0)
        ->and($multipleCurrentBoundPerAccount)->toBe(0);
});

it('does not inject timeline-active licenses into @test.com personas', function () {
    $mutatedTestPersonaLicenses = License::query()
        ->where('notes', 'Seed: Active license generated for timeline')
        ->whereHas('account', function ($query): void {
            $query->where('email', 'like', '%@test.com');
        })
        ->count();

    expect($mutatedTestPersonaLicenses)->toBe(0);
});

it('does not create upgrade-chain timeline artifacts for @test.com personas', function () {
    $mutatedUpgradeChainLicenses = License::query()
        ->where('notes', 'Seed: Upgraded from this base license')
        ->whereHas('account', function ($query): void {
            $query->where('email', 'like', '%@test.com');
        })
        ->count();

    expect($mutatedUpgradeChainLicenses)->toBe(0);
});

it('seeds usernames as lowercase alphanumeric only and valid lowercase emails', function () {
    $invalidUsernames = Account::query()
        ->whereRaw("username NOT GLOB '[a-z0-9]*'")
        ->orWhereRaw('length(username) = 0')
        ->count();

    $invalidEmails = Account::query()
        ->get()
        ->filter(function (Account $account): bool {
            if (! is_string($account->email) || $account->email === '' || $account->email !== strtolower($account->email)) {
                return true;
            }

            return Validator::make(
                ['email' => $account->email],
                ['email' => ['required', 'email:rfc']]
            )->fails();
        })
        ->count();

    expect($invalidUsernames)->toBe(0)
        ->and($invalidEmails)->toBe(0);
});
