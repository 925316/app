<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;
use App\Models\EventLog;
use App\Models\License;

use function Pest\Laravel\postJson;

function apiLoginPayload(array $overrides = []): array
{
    return array_merge([
        'email' => 'api-login@example.com',
        'password' => 'password',
        'hwid' => 'HWID-LOGIN-12345',
        'nonce' => 'nonce-'.str()->random(16),
        'timestamp' => now()->timestamp,
        'version' => '1.2.3',
        'country_code' => 'US',
    ], $overrides);
}

function seedLoginApiContext(array $accountOverrides = []): array
{
    $account = Account::factory()->active()->create(array_merge([
        'email' => 'api-login@example.com',
        'password' => bcrypt('password'),
        'is_suspended' => false,
        'suspended_until' => null,
    ], $accountOverrides));

    $device = AccountDevice::factory()->bound()->create([
        'account_id' => $account->id,
        'hwid_hash' => hash('sha256', 'HWID-LOGIN-12345'),
    ]);

    $license = License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::STANDARD->value,
        'used_by' => $account->id,
        'expires_at' => now()->addDays(30),
    ]);

    return compact('account', 'device', 'license');
}

beforeEach(function () {
    mockRedisSetUnavailable();
});

it('logs in and returns session token with account and license summary', function () {
    $context = seedLoginApiContext();

    $response = postJson('/api/account/login', apiLoginPayload());

    assertApiOk($response, [
        'data.account.id' => $context['account']->id,
        'data.account.email' => 'api-login@example.com',
        'data.license.status' => 'active',
        'data.license.plan_level' => LicensePrivilege::STANDARD->value,
    ]);

    $sessionToken = $response->json('data.session_token');
    expect(is_string($sessionToken))->toBeTrue();
    expect($sessionToken)->not->toBe('');

    expect(ClientSession::query()->where('session_token', $sessionToken)->exists())->toBeTrue();
    expect(EventLog::query()->where('event_type', 'account.login')->where('account_id', $context['account']->id)->exists())->toBeTrue();
});

it('returns auth required when credentials are missing', function () {
    seedLoginApiContext();

    $response = postJson('/api/account/login', apiLoginPayload([
        'email' => null,
        'password' => null,
    ]));

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED');
});

it('returns auth required when password is wrong', function () {
    seedLoginApiContext();

    $response = postJson('/api/account/login', apiLoginPayload([
        'password' => 'wrong-password',
    ]));

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED');
});

it('returns nonce replay for reused nonce', function () {
    seedLoginApiContext();

    $nonce = 'nonce-fixed-login-'.str()->random(12);
    $payload = apiLoginPayload(['nonce' => $nonce]);

    postJson('/api/account/login', $payload)->assertSuccessful();

    postJson('/api/account/login', $payload)
        ->assertConflict()
        ->assertJsonPath('error_code', 'NONCE_REPLAY');
});

it('returns timestamp out of window for stale timestamp', function () {
    seedLoginApiContext();

    $response = postJson('/api/account/login', apiLoginPayload([
        'timestamp' => now()->subMinutes(10)->timestamp,
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'TIMESTAMP_OUT_OF_WINDOW');
});

it('returns device mismatch when hwid does not match currently bound device', function () {
    seedLoginApiContext();

    $response = postJson('/api/account/login', apiLoginPayload([
        'hwid' => 'HWID-LOGIN-OTHER',
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'DEVICE_MISMATCH');
});

it('returns device mismatch when account has no bound device', function () {
    $context = seedLoginApiContext();

    $context['device']->forceFill([
        'unbound_at' => now()->subMinute(),
    ])->save();

    $response = postJson('/api/account/login', apiLoginPayload());

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'DEVICE_MISMATCH');
});

it('returns license ineffective when account has no effective license', function () {
    $context = seedLoginApiContext();

    $context['license']->forceFill([
        'status' => LicenseStatus::SUSPENDED,
    ])->save();

    $response = postJson('/api/account/login', apiLoginPayload());

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE');
});

it('returns license ineffective when account is suspended', function () {
    seedLoginApiContext([
        'is_suspended' => true,
    ]);

    $response = postJson('/api/account/login', apiLoginPayload());

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE');
});

it('normalizes hwid and version input values before login processing', function () {
    seedLoginApiContext();

    $response = postJson('/api/account/login', apiLoginPayload([
        'hwid' => '  HWID-LOGIN-12345  ',
        'version' => ' 1.2.3 ',
    ]));

    assertApiOk($response);
});

it('replaces existing session on repeated successful login for same account and device', function () {
    $context = seedLoginApiContext();

    $first = postJson('/api/account/login', apiLoginPayload([
        'nonce' => 'nonce-first-login-replace',
    ]));
    $first->assertSuccessful();
    $firstToken = $first->json('data.session_token');

    $second = postJson('/api/account/login', apiLoginPayload([
        'nonce' => 'nonce-second-login-replace',
    ]));
    $second->assertSuccessful();
    $secondToken = $second->json('data.session_token');

    expect($secondToken)->not->toBe($firstToken);
    expect(ClientSession::query()->where('session_token', $firstToken)->exists())->toBeFalse();
    expect(ClientSession::query()->where('session_token', $secondToken)->exists())->toBeTrue();
    expect(ClientSession::query()->where('account_id', $context['account']->id)->where('device_id', $context['device']->id)->count())->toBe(1);
});

it('validates timestamp payload type and range in login endpoint', function (mixed $timestamp) {
    seedLoginApiContext();

    $response = postJson('/api/account/login', apiLoginPayload([
        'timestamp' => $timestamp,
    ]));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['timestamp']);
})->with([
    'string timestamp' => 'invalid',
    'negative timestamp' => -1,
    'float timestamp' => 1.5,
]);
