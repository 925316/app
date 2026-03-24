<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;
use App\Models\License;
use App\Services\CryptoService;

use function Pest\Laravel\postJson;

function apiActivatePayload(array $overrides = []): array
{
    return array_merge([
        'session_token' => 'activate-session-token-001',
        'license_key' => 'FGHIJ-12ABC-ABCDE-ABCDE-ABCDE',
        'hwid' => 'HWID-ACTIVATE-12345',
        'nonce' => 'nonce-'.str()->random(16),
        'version' => '26.0.0',
        'timestamp' => now()->timestamp,
    ], $overrides);
}

function seedActivateApiContext(array $licenseOverrides = []): array
{
    $account = Account::factory()->create();

    $device = AccountDevice::factory()->bound()->create([
        'account_id' => $account->id,
        'hwid_hash' => hash('sha256', 'HWID-ACTIVATE-12345'),
    ]);

    $license = License::factory()->create(array_merge([
        'key' => 'FGHIJ-12ABC-ABCDE-ABCDE-ABCDE',
        'status' => LicenseStatus::UNUSED->value,
        'privilege' => LicensePrivilege::STANDARD->value,
        'used_by' => null,
        'expires_at' => now()->addDays(30),
        'activated_at' => null,
    ], $licenseOverrides));

    $session = ClientSession::factory()->create([
        'session_token' => 'activate-session-token-001',
        'account_id' => $account->id,
        'device_id' => $device->id,
        'last_heartbeat_at' => now()->subMinutes(30),
    ]);

    return compact('account', 'device', 'license', 'session');
}

beforeEach(function () {
    app()->instance(CryptoService::class, new class extends CryptoService
    {
        public function signData(mixed $data): string
        {
            return 'signed-activate-data';
        }
    });

    mockRedisSetUnavailable();
});

it('activates an unused license and returns signed response', function () {
    $context = seedActivateApiContext();

    $response = postJson('/api/license/activate', apiActivatePayload());

    assertApiOk($response, [
        'data.status' => 'active',
        'data.plan_level' => LicensePrivilege::STANDARD->value,
        'data.username' => $context['account']->username,
        'signature' => 'signed-activate-data',
        'meta.signature.algorithm' => 'RSA-2048-SHA256',
        'meta.signature.key_id' => 'main-2026-01',
    ]);

    expect($context['license']->fresh()->status)->toBe(LicenseStatus::ACTIVE);
    expect($context['license']->fresh()->used_by)->toBe($context['account']->id);
});

it('validates required session token payload variants', function (?string $tokenValue, bool $omitField, ?string $expectedMessage) {
    seedActivateApiContext();

    $payload = apiActivatePayload();
    if ($omitField) {
        unset($payload['session_token']);
    } else {
        $payload['session_token'] = $tokenValue;
    }

    $response = postJson('/api/license/activate', $payload);

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'VALIDATION_FAILED')
        ->assertJsonPath('signature', 'signed-activate-data');

    if ($expectedMessage !== null) {
        $response->assertJsonPath('message', $expectedMessage);
    }
})->with([
    'null session token' => [null, false, null],
    'omitted session token' => [null, true, 'Session token is required.'],
]);

it('returns auth required when session token does not exist', function () {
    seedActivateApiContext();

    $response = postJson('/api/license/activate', apiActivatePayload([
        'session_token' => 'unknown-activate-session-token',
    ]));

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED');
});

it('returns nonce replay for reused nonce', function () {
    seedActivateApiContext();

    $payload = apiActivatePayload(['nonce' => 'nonce-fixed-activate']);

    $first = postJson('/api/license/activate', $payload);
    $first->assertSuccessful();

    $second = postJson('/api/license/activate', $payload);
    $second->assertConflict()
        ->assertJsonPath('error_code', 'NONCE_REPLAY');
});

it('returns timestamp out of window for stale timestamp', function () {
    seedActivateApiContext();

    $response = postJson('/api/license/activate', apiActivatePayload([
        'timestamp' => now()->subMinutes(10)->timestamp,
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'TIMESTAMP_OUT_OF_WINDOW');
});

it('returns timestamp out of window for future timestamp', function () {
    seedActivateApiContext();

    $response = postJson('/api/license/activate', apiActivatePayload([
        'timestamp' => now()->addMinutes(10)->timestamp,
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'TIMESTAMP_OUT_OF_WINDOW');
});

it('returns device mismatch when hwid does not match bound device', function () {
    seedActivateApiContext();

    $response = postJson('/api/license/activate', apiActivatePayload([
        'hwid' => 'HWID-ACTIVATE-OTHER',
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'DEVICE_MISMATCH');
});

it('returns license invalid when license does not exist', function () {
    seedActivateApiContext();

    $response = postJson('/api/license/activate', apiActivatePayload([
        'license_key' => 'ZZZZZ-12ABC-ABCDE-ABCDE-ABCDE',
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'LICENSE_INVALID');
});

it('returns license ineffective when trying to activate suspended license', function () {
    seedActivateApiContext([
        'status' => LicenseStatus::SUSPENDED->value,
        'used_by' => null,
    ]);

    $response = postJson('/api/license/activate', apiActivatePayload());

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE');
});

it('returns license ineffective when activating the same license again with a new nonce', function () {
    seedActivateApiContext();

    postJson('/api/license/activate', apiActivatePayload([
        'nonce' => 'nonce-first-activate',
    ]))->assertSuccessful();

    $response = postJson('/api/license/activate', apiActivatePayload([
        'nonce' => 'nonce-second-activate',
    ]));

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE');
});

it('returns license ineffective when account already has another active effective license', function () {
    $context = seedActivateApiContext();

    License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::ULTIMATE->value,
        'used_by' => $context['account']->id,
        'expires_at' => now()->addDays(90),
    ]);

    $response = postJson('/api/license/activate', apiActivatePayload());

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE');
});

it('returns license ineffective when target license is expired', function () {
    seedActivateApiContext([
        'status' => LicenseStatus::UNUSED->value,
        'expires_at' => now()->subDay(),
    ]);

    $response = postJson('/api/license/activate', apiActivatePayload());

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE');
});

it('returns license ineffective when target license is revoked', function () {
    seedActivateApiContext([
        'status' => LicenseStatus::REVOKED->value,
    ]);

    $response = postJson('/api/license/activate', apiActivatePayload());

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE');
});

it('returns license ineffective when target license cannot be activated by privilege rules', function () {
    seedActivateApiContext([
        'status' => LicenseStatus::UNUSED->value,
        'privilege' => LicensePrivilege::UPGRADE->value,
        'used_by' => null,
    ]);

    $response = postJson('/api/license/activate', apiActivatePayload());

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE')
        ->assertJsonPath('signature', 'signed-activate-data')
        ->assertJsonPath('meta.signature.algorithm', 'RSA-2048-SHA256')
        ->assertJsonPath('meta.signature.key_id', 'main-2026-01');
});

it('validates timestamp payload type and range in activate endpoint', function (mixed $timestamp) {
    seedActivateApiContext();

    $response = postJson('/api/license/activate', apiActivatePayload([
        'timestamp' => $timestamp,
    ]));

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'VALIDATION_FAILED')
        ->assertJsonPath('signature', 'signed-activate-data');
})->with([
    'string timestamp' => 'invalid',
    'negative timestamp' => -1,
    'float timestamp' => 1.5,
]);

it('normalizes session token and hwid input values before activate processing', function () {
    seedActivateApiContext();

    $response = postJson('/api/license/activate', apiActivatePayload([
        'session_token' => '  activate-session-token-001  ',
        'hwid' => '  HWID-ACTIVATE-12345  ',
        'version' => ' 26.0.0 ',
    ]));

    assertApiOk($response, [
        'data.status' => 'active',
    ]);
});
