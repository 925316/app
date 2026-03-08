<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;
use App\Models\EventLog;
use App\Models\License;
use Illuminate\Support\Facades\Redis;

use function Pest\Laravel\postJson;

function apiUnbindPayload(array $overrides = []): array
{
    return array_merge([
        'session_token' => 'unbind-session-token-001',
        'license_key' => 'KLMNO-12ABC-ABCDE-ABCDE-ABCDE',
        'hwid' => 'HWID-UNBIND-12345',
        'nonce' => 'nonce-'.str()->random(16),
        'version' => '1.0.0',
        'timestamp' => now()->timestamp,
    ], $overrides);
}

function seedUnbindApiContext(): array
{
    $account = Account::factory()->create();

    $device = AccountDevice::factory()->bound()->create([
        'account_id' => $account->id,
        'hwid_hash' => hash('sha256', 'HWID-UNBIND-12345'),
    ]);

    $license = License::factory()->create([
        'key' => 'KLMNO-12ABC-ABCDE-ABCDE-ABCDE',
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::STANDARD->value,
        'used_by' => $account->id,
        'expires_at' => now()->addDays(30),
    ]);

    $session = ClientSession::factory()->create([
        'session_token' => 'unbind-session-token-001',
        'account_id' => $account->id,
        'device_id' => $device->id,
        'last_heartbeat_at' => now()->subMinutes(1),
    ]);

    return compact('account', 'device', 'license', 'session');
}

beforeEach(function () {
    Redis::shouldReceive('set')->andThrow(new RuntimeException('Redis unavailable'));
});

it('unbinds bound device and returns success payload', function () {
    $context = seedUnbindApiContext();

    $response = postJson('/api/license/unbind', apiUnbindPayload());

    $response->assertSuccessful()
        ->assertJsonPath('code', 200)
        ->assertJsonPath('error_code', null)
        ->assertJsonPath('message', 'OK')
        ->assertJsonPath('data.status', 'unbound')
        ->assertJsonPath('data.license_key', 'KLMNO-12ABC-ABCDE-ABCDE-ABCDE')
        ->assertJsonPath('data.device_id', $context['device']->id);

    expect($context['device']->fresh()->unbound_at)->not->toBeNull();
    expect(ClientSession::query()->where('session_token', 'unbind-session-token-001')->exists())->toBeFalse();

    expect(EventLog::query()
        ->where('event_type', 'device.unbound')
        ->where('account_id', $context['account']->id)
        ->where('license_id', $context['license']->id)
        ->exists())->toBeTrue();
});

it('returns auth required when session token is missing', function () {
    seedUnbindApiContext();

    $response = postJson('/api/license/unbind', apiUnbindPayload([
        'session_token' => null,
    ]));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['session_token']);
});

it('returns nonce replay for reused nonce', function () {
    seedUnbindApiContext();

    $payload = apiUnbindPayload(['nonce' => 'nonce-fixed-unbind']);

    postJson('/api/license/unbind', $payload)->assertSuccessful();

    postJson('/api/license/unbind', $payload)
        ->assertConflict()
        ->assertJsonPath('error_code', 'NONCE_REPLAY');
});

it('returns timestamp out of window for stale timestamp', function () {
    seedUnbindApiContext();

    $response = postJson('/api/license/unbind', apiUnbindPayload([
        'timestamp' => now()->subMinutes(10)->timestamp,
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'TIMESTAMP_OUT_OF_WINDOW');
});

it('returns device mismatch when hwid does not match bound device', function () {
    $context = seedUnbindApiContext();

    $response = postJson('/api/license/unbind', apiUnbindPayload([
        'hwid' => 'HWID-UNBIND-OTHER',
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'DEVICE_MISMATCH');

    expect($context['device']->fresh()->unbound_at)->toBeNull();
});

it('returns license ineffective when license is suspended', function () {
    seedUnbindApiContext();

    License::query()->where('key', 'KLMNO-12ABC-ABCDE-ABCDE-ABCDE')
        ->update(['status' => LicenseStatus::SUSPENDED->value]);

    $response = postJson('/api/license/unbind', apiUnbindPayload());

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE');
});

it('returns license invalid for bad license key format', function () {
    seedUnbindApiContext();

    $response = postJson('/api/license/unbind', apiUnbindPayload([
        'license_key' => 'BAD-KEY',
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'LICENSE_INVALID');
});

it('returns device not bound when session device is already unbound', function () {
    $context = seedUnbindApiContext();

    $context['device']->forceFill([
        'unbound_at' => now()->subMinute(),
    ])->save();

    $response = postJson('/api/license/unbind', apiUnbindPayload());

    $response->assertConflict()
        ->assertJsonPath('error_code', 'DEVICE_NOT_BOUND');
});

it('normalizes session token and hwid input values before unbind processing', function () {
    seedUnbindApiContext();

    $response = postJson('/api/license/unbind', apiUnbindPayload([
        'session_token' => '  unbind-session-token-001  ',
        'hwid' => '  HWID-UNBIND-12345  ',
        'version' => ' 1.0.0 ',
    ]));

    $response->assertSuccessful()
        ->assertJsonPath('code', 200)
        ->assertJsonPath('error_code', null)
        ->assertJsonPath('message', 'OK')
        ->assertJsonPath('data.status', 'unbound');
});
