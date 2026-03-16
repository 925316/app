<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;
use App\Models\License;
use App\Services\CryptoService;
use Illuminate\Support\Carbon;

use function Pest\Laravel\postJson;

function apiPayload(array $overrides = []): array
{
    return array_merge([
        'session_token' => 'session-token-001',
        'license_key' => 'ABCDE-12ABC-ABCDE-ABCDE-ABCDE',
        'hwid' => 'HWID-TEST-12345',
        'nonce' => 'nonce-'.str()->random(16),
        'version' => '1.0.0',
        'timestamp' => now()->timestamp,
    ], $overrides);
}

function seedValidApiContext(): array
{
    $account = Account::factory()->create();

    $device = AccountDevice::factory()->bound()->create([
        'account_id' => $account->id,
        'hwid_hash' => hash('sha256', 'HWID-TEST-12345'),
    ]);

    $license = License::factory()->create([
        'key' => 'ABCDE-12ABC-ABCDE-ABCDE-ABCDE',
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::ULTIMATE->value,
        'used_by' => $account->id,
        'expires_at' => now()->addDays(30),
        'activated_at' => now()->subDay(),
    ]);

    $session = ClientSession::factory()->create([
        'session_token' => 'session-token-001',
        'account_id' => $account->id,
        'device_id' => $device->id,
        'last_heartbeat_at' => now()->subMinutes(30),
    ]);

    return compact('account', 'device', 'license', 'session');
}

beforeEach(function () {
    app()->instance(CryptoService::class, new class extends CryptoService
    {
        public function signData(array $data): string
        {
            return 'signed-data';
        }
    });

    mockRedisSetUnavailable(); // fallback to Cache::add in tests
});

it('returns signed success payload and updates heartbeat for valid check', function () {
    $context = seedValidApiContext();
    $previousHeartbeat = Carbon::parse($context['session']->last_heartbeat_at);

    $response = postJson('/api/license/check', apiPayload());

    assertApiOk($response, [
        'data.status' => 'active',
        'data.plan_level' => LicensePrivilege::ULTIMATE->value,
        'data.username' => $context['account']->username,
        'signature' => 'signed-data',
        'meta.signature.algorithm' => 'RSA-2048-SHA256',
        'meta.signature.key_id' => 'main-2026-01',
    ]);

    $updatedHeartbeat = $context['session']->fresh()->last_heartbeat_at;

    expect($updatedHeartbeat)->not->toBeNull();
    expect($updatedHeartbeat?->gt($previousHeartbeat))->toBeTrue();
});

it('returns nonce replay error for reused nonce', function () {
    seedValidApiContext();

    $payload = apiPayload(['nonce' => 'nonce-fixed-value']);

    $first = postJson('/api/license/check', $payload);
    $first->assertSuccessful();

    $second = postJson('/api/license/check', $payload);
    $second->assertConflict()
        ->assertJsonPath('error_code', 'NONCE_REPLAY');
});

it('allows same nonce value across different endpoint scopes', function () {
    seedValidApiContext();

    $payload = apiPayload(['nonce' => 'nonce-same-cross-endpoint']);

    postJson('/api/license/check', $payload)
        ->assertSuccessful();

    postJson('/api/license/activate', $payload)
        ->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE');
});

it('returns timestamp out of window for stale timestamp', function () {
    seedValidApiContext();

    $response = postJson('/api/license/check', apiPayload([
        'timestamp' => now()->subMinutes(10)->timestamp,
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'TIMESTAMP_OUT_OF_WINDOW');
});

it('returns device mismatch when hwid does not match session bound device', function () {
    $context = seedValidApiContext();
    $previousHeartbeat = Carbon::parse($context['session']->last_heartbeat_at);

    $response = postJson('/api/license/check', apiPayload([
        'hwid' => 'HWID-OTHER-999',
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'DEVICE_MISMATCH');

    expect($context['session']->fresh()->last_heartbeat_at?->eq($previousHeartbeat))->toBeTrue();
});

it('validates required session token payload variants', function (?string $tokenValue, bool $omitField, ?string $expectedMessage) {
    seedValidApiContext();

    $payload = apiPayload();
    if ($omitField) {
        unset($payload['session_token']);
    } else {
        $payload['session_token'] = $tokenValue;
    }

    $response = postJson('/api/license/check', $payload);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['session_token']);

    if ($expectedMessage !== null) {
        $response->assertJsonPath('message', $expectedMessage);
    }
})->with([
    'null session token' => [null, false, null],
    'omitted session token' => [null, true, 'Session token is required.'],
]);

it('returns auth required when session token does not exist', function () {
    seedValidApiContext();

    $response = postJson('/api/license/check', apiPayload([
        'session_token' => 'unknown-session-token',
    ]));

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED');
});

it('returns license invalid when license key is well-formed but does not exist', function () {
    seedValidApiContext();

    $response = postJson('/api/license/check', apiPayload([
        'license_key' => 'ZZZZZ-12ABC-ABCDE-ABCDE-ABCDE',
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'LICENSE_INVALID');
});

it('returns auth required when license does not belong to session account', function () {
    seedValidApiContext();
    $otherAccount = Account::factory()->create();

    License::query()
        ->where('key', 'ABCDE-12ABC-ABCDE-ABCDE-ABCDE')
        ->update(['used_by' => $otherAccount->id]);

    $response = postJson('/api/license/check', apiPayload());

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED');
});

it('validates timestamp payload type and range in check endpoint', function (mixed $timestamp) {
    seedValidApiContext();

    $response = postJson('/api/license/check', apiPayload([
        'timestamp' => $timestamp,
    ]));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['timestamp']);
})->with([
    'string timestamp' => 'invalid',
    'negative timestamp' => -1,
    'float timestamp' => 1.5,
]);

it('returns license ineffective when license is not active', function () {
    seedValidApiContext();

    License::query()
        ->where('key', 'ABCDE-12ABC-ABCDE-ABCDE-ABCDE')
        ->update(['status' => LicenseStatus::SUSPENDED->value]);

    $response = postJson('/api/license/check', apiPayload());

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE');
});

it('validates license key format before check processing', function () {
    $context = seedValidApiContext();
    $previousHeartbeat = Carbon::parse($context['session']->last_heartbeat_at);

    $response = postJson('/api/license/check', apiPayload([
        'license_key' => 'INVALID-KEY',
    ]));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['license_key']);

    expect($context['session']->fresh()->last_heartbeat_at?->eq($previousHeartbeat))->toBeTrue();
});

it('normalizes session token and hwid input values before check processing', function () {
    seedValidApiContext();

    $response = postJson('/api/license/check', apiPayload([
        'session_token' => '  session-token-001  ',
        'hwid' => '  HWID-TEST-12345  ',
        'version' => ' 1.0.0 ',
    ]));

    assertApiOk($response);
});
