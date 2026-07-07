<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;
use App\Models\License;
use App\Models\PackageRelease;
use App\Services\CryptoService;

use function Pest\Laravel\getJson;

function seedUpdateCheckContext(): array
{
    $account = Account::factory()->create();

    $device = AccountDevice::factory()->bound()->create([
        'account_id' => $account->id,
        'hwid_hash' => hash('sha256', 'HWID-UPDATE-CHECK-001'),
    ]);

    License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::STANDARD->value,
        'used_by' => $account->id,
        'expires_at' => now()->addDays(30),
    ]);

    $session = ClientSession::factory()->create([
        'session_token' => 'update-check-session-token-001',
        'account_id' => $account->id,
        'device_id' => $device->id,
    ]);

    return compact('account', 'session');
}

beforeEach(function () {
    app()->instance(CryptoService::class, new class extends CryptoService
    {
        public function signData(mixed $data): string
        {
            return 'signed-update-data';
        }
    });
});

it('returns latest stable release payload with contract shape', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '1.2.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/1.2.0.zip',
        'changelog' => 'Older stable build',
        'virus_detection_url' => 'https://example.com/scan/1.2.0',
    ]);

    PackageRelease::factory()->create([
        'version' => '1.3.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/1.3.0.zip',
        'changelog' => 'Latest stable build',
        'virus_detection_url' => 'https://example.com/scan/1.3.0',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=stable');

    $response->assertSuccessful()
        ->assertJsonPath('code', 200)
        ->assertJsonPath('error_code', null)
        ->assertJsonPath('message', 'OK')
        ->assertJsonPath('signature', 'signed-update-data')
        ->assertJsonPath('meta.signature.algorithm', 'RSA-2048-SHA256')
        ->assertJsonPath('meta.signature.key_id', 'main-2026-01')
        ->assertJsonPath('data.current_version', null)
        ->assertJsonPath('data.version', '1.3.0')
        ->assertJsonPath('data.release_channel', 'stable')
        ->assertJsonPath('data.update_available', null)
        ->assertJsonPath('data.reason', 'no_current_version')
        ->assertJsonPath('data.download_url', 'https://example.com/download/1.3.0.zip')
        ->assertJsonPath('data.changelog', 'Latest stable build')
        ->assertJsonPath('data.virus_detection_url', 'https://example.com/scan/1.3.0');
});

it('returns latest dev release when release_channel is dev', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '2.0.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/2.0.0.zip',
    ]);

    PackageRelease::factory()->create([
        'version' => '2.1.0',
        'release_channel' => 'dev',
        'download_url' => 'https://example.com/download/2.1.0.zip',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=dev');

    $response->assertSuccessful()
        ->assertJsonPath('code', 200)
        ->assertJsonPath('signature', 'signed-update-data')
        ->assertJsonPath('data.version', '2.1.0')
        ->assertJsonPath('data.release_channel', 'dev')
        ->assertJsonPath('data.current_version', null)
        ->assertJsonPath('data.update_available', null)
        ->assertJsonPath('data.reason', 'no_current_version');
});

it('returns auth required when session token is missing', function () {
    $response = getJson('/api/update/check');

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('returns auth required when session token does not exist', function () {
    $response = getJson('/api/update/check?session_token=unknown-session-token&release_channel=stable');

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('returns auth required when update check session account is missing', function () {
    $account = Account::factory()->create();
    $device = AccountDevice::factory()->bound()->create([
        'account_id' => $account->id,
    ]);

    $session = ClientSession::factory()->create([
        'session_token' => 'update-check-orphan-account-session',
        'account_id' => $account->id,
        'device_id' => $device->id,
    ]);

    $account->delete();

    $response = getJson('/api/update/check?session_token=update-check-orphan-account-session&release_channel=stable');

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED')
        ->assertJsonPath('signature', 'signed-update-data')
        ->assertJsonPath('meta.signature.algorithm', 'RSA-2048-SHA256')
        ->assertJsonPath('meta.signature.key_id', 'main-2026-01');

    expect(ClientSession::query()->whereKey($session->id)->exists())->toBeFalse();
});

it('returns auth required when update check session device is missing', function () {
    $account = Account::factory()->create();

    License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::STANDARD->value,
        'used_by' => $account->id,
        'expires_at' => now()->addDays(30),
    ]);

    $device = AccountDevice::factory()->bound()->create([
        'account_id' => $account->id,
    ]);

    $session = ClientSession::factory()->create([
        'session_token' => 'update-check-missing-device-session',
        'account_id' => $account->id,
        'device_id' => $device->id,
    ]);

    $device->delete();

    $response = getJson('/api/update/check?session_token=update-check-missing-device-session&release_channel=stable');

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED')
        ->assertJsonPath('signature', 'signed-update-data');

    expect(ClientSession::query()->whereKey($session->id)->exists())->toBeFalse();
});

it('returns auth required when update check device is never bound', function () {
    $account = Account::factory()->create();

    $device = AccountDevice::factory()->neverBound()->create([
        'account_id' => $account->id,
    ]);

    License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::STANDARD->value,
        'used_by' => $account->id,
        'expires_at' => now()->addDays(30),
    ]);

    ClientSession::factory()->create([
        'session_token' => 'update-check-never-bound-session',
        'account_id' => $account->id,
        'device_id' => $device->id,
    ]);

    $response = getJson('/api/update/check?session_token=update-check-never-bound-session&release_channel=stable');

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('returns license ineffective when account does not have effective privilege', function () {
    $account = Account::factory()->create();
    $device = AccountDevice::factory()->bound()->create([
        'account_id' => $account->id,
    ]);

    ClientSession::factory()->create([
        'session_token' => 'update-check-session-token-no-license',
        'account_id' => $account->id,
        'device_id' => $device->id,
    ]);

    PackageRelease::factory()->create([
        'version' => '1.0.0',
        'release_channel' => 'stable',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-no-license&release_channel=stable');

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('returns package not found when channel has no releases', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '1.0.0',
        'release_channel' => 'stable',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=dev');

    $response->assertNotFound()
        ->assertJsonPath('error_code', 'PACKAGE_NOT_FOUND')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('returns update decision fields when current_version is provided and update is available', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '2.0.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/2.0.0.zip',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=stable&current_version=1.9.9');

    $response->assertSuccessful()
        ->assertJsonPath('data.current_version', '1.9.9')
        ->assertJsonPath('data.version', '2.0.0')
        ->assertJsonPath('signature', 'signed-update-data')
        ->assertJsonPath('data.update_available', true)
        ->assertJsonPath('data.reason', 'newer_available');
});

it('returns update decision fields when current_version is provided and already up to date', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '3.0.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/3.0.0.zip',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=stable&current_version=3.0.0');

    $response->assertSuccessful()
        ->assertJsonPath('data.current_version', '3.0.0')
        ->assertJsonPath('signature', 'signed-update-data')
        ->assertJsonPath('data.update_available', false)
        ->assertJsonPath('data.reason', 'up_to_date');
});

it('returns invalid version when current_version is malformed', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '1.0.0',
        'release_channel' => 'stable',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=stable&current_version=bad-version');

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'INVALID_VERSION')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('trims current_version before semantic validation and comparison', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '2.0.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/2.0.0.zip',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=stable&current_version=%201.9.9%20');

    $response->assertSuccessful()
        ->assertJsonPath('data.current_version', '1.9.9')
        ->assertJsonPath('data.update_available', true)
        ->assertJsonPath('data.reason', 'newer_available');
});

it('returns invalid version when current_version exceeds max length', function () {
    seedUpdateCheckContext();

    $longVersion = str_repeat('1', 51);
    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=stable&current_version='.$longVersion);

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'INVALID_VERSION')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('returns invalid channel when release_channel is unsupported', function () {
    seedUpdateCheckContext();

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=beta');

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'INVALID_CHANNEL')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('returns validation failed when session_token is not a string', function () {
    $response = getJson('/api/update/check?session_token[]=abc');

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'VALIDATION_FAILED')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('returns validation failed when release_channel is not a string', function () {
    $response = getJson('/api/update/check?release_channel[]=stable');

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'VALIDATION_FAILED')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('returns validation failed when current_version is not a string', function () {
    $response = getJson('/api/update/check?current_version[]=1.0.0');

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'VALIDATION_FAILED')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('returns auth required when session token exceeds max length', function () {
    $tooLongSessionToken = str_repeat('a', 129);

    $response = getJson('/api/update/check?session_token='.$tooLongSessionToken);

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('normalizes release_channel and session_token query values before validation', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '4.0.0',
        'release_channel' => 'dev',
        'download_url' => 'https://example.com/download/4.0.0-dev.zip',
    ]);

    $response = getJson('/api/update/check?session_token=%20update-check-session-token-001%20&release_channel=%20DEV%20');

    $response->assertSuccessful()
        ->assertJsonPath('signature', 'signed-update-data')
        ->assertJsonPath('data.release_channel', 'dev')
        ->assertJsonPath('data.version', '4.0.0');
});

it('rejects update check after device is unbound in web flow', function () {
    $context = seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '4.2.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/4.2.0.zip',
    ]);

    $this->actingAs($context['account'])
        ->post(route('devices.unbind'))
        ->assertRedirect(route('devices.manage'));

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=stable');

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('returns package not found when latest release has non-https download url', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '5.0.0',
        'release_channel' => 'stable',
        'download_url' => 'http://example.com/download/5.0.0.zip',
        'virus_detection_url' => 'https://example.com/scan/5.0.0',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=stable');

    $response->assertNotFound()
        ->assertJsonPath('error_code', 'PACKAGE_NOT_FOUND')
        ->assertJsonPath('signature', 'signed-update-data');
});

it('returns package not found when latest release has unsafe virus detection url', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '5.1.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/5.1.0.zip',
        'virus_detection_url' => 'https://localhost/scan/5.1.0',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=stable');

    $response->assertNotFound()
        ->assertJsonPath('error_code', 'PACKAGE_NOT_FOUND')
        ->assertJsonPath('signature', 'signed-update-data');
});
