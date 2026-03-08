<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;
use App\Models\License;
use App\Services\CryptoService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;

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

    Redis::shouldReceive('set')->andThrow(new \RuntimeException('Redis unavailable')); // fallback to Cache::add in tests
});

it('returns signed success payload and updates heartbeat for valid check', function () {
    $context = seedValidApiContext();

    $response = postJson('/api/license/check', apiPayload());

    $response->assertSuccessful()
        ->assertJsonPath('code', 200)
        ->assertJsonPath('error_code', null)
        ->assertJsonPath('message', 'OK')
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.plan_level', LicensePrivilege::ULTIMATE->value)
        ->assertJsonPath('data.username', $context['account']->username)
        ->assertJsonPath('signature', 'signed-data')
        ->assertJsonPath('meta.signature.algorithm', 'RSA-2048-SHA256')
        ->assertJsonPath('meta.signature.key_id', 'main-2026-01');

    expect($context['session']->fresh()->last_heartbeat_at)->not->toBeNull();
    expect($context['session']->fresh()->last_heartbeat_at->gt(now()->subSeconds(10)))->toBeTrue();
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

it('returns auth required when session token is missing', function () {
    seedValidApiContext();

    $response = postJson('/api/license/check', apiPayload([
        'session_token' => null,
    ]));

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED');
});

it('returns auth required with stable message when session token field is omitted', function () {
    seedValidApiContext();

    $response = postJson('/api/license/check', apiPayload([
        'session_token' => null,
    ]));

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED')
        ->assertJsonPath('message', 'Authentication required.');
});

it('returns license ineffective when license is not active', function () {
    seedValidApiContext();

    License::query()
        ->where('key', 'ABCDE-12ABC-ABCDE-ABCDE-ABCDE')
        ->update(['status' => LicenseStatus::SUSPENDED->value]);

    $response = postJson('/api/license/check', apiPayload());

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE');
});

it('returns license invalid and does not update heartbeat when license key format is invalid', function () {
    $context = seedValidApiContext();
    $previousHeartbeat = Carbon::parse($context['session']->last_heartbeat_at);

    $response = postJson('/api/license/check', apiPayload([
        'license_key' => 'INVALID-KEY',
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'LICENSE_INVALID');

    expect($context['session']->fresh()->last_heartbeat_at?->eq($previousHeartbeat))->toBeTrue();
});

it('normalizes session token and hwid input values before check processing', function () {
    seedValidApiContext();

    $response = postJson('/api/license/check', apiPayload([
        'session_token' => '  session-token-001  ',
        'hwid' => '  HWID-TEST-12345  ',
        'version' => ' 1.0.0 ',
    ]));

    $response->assertSuccessful()
        ->assertJsonPath('code', 200)
        ->assertJsonPath('error_code', null)
        ->assertJsonPath('message', 'OK');
});
