<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;
use App\Models\License;
use App\Models\PackageRelease;

use function Pest\Laravel\getJson;

function seedUpdateCheckContext(): array
{
    $account = Account::factory()->create();

    AccountDevice::factory()->bound()->create([
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
    ]);

    return compact('account', 'session');
}

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

    $response = getJson('/api/update/check?session_token=update-check-session-token-001');

    $response->assertSuccessful()
        ->assertJsonPath('code', 200)
        ->assertJsonPath('error_code', null)
        ->assertJsonPath('message', 'OK')
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
        ->assertJsonPath('data.version', '2.1.0')
        ->assertJsonPath('data.release_channel', 'dev')
        ->assertJsonPath('data.current_version', null)
        ->assertJsonPath('data.update_available', null)
        ->assertJsonPath('data.reason', 'no_current_version');
});

it('returns auth required when session token is missing', function () {
    $response = getJson('/api/update/check');

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED');
});

it('returns auth required when session token does not exist', function () {
    $response = getJson('/api/update/check?session_token=unknown-session-token');

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED');
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

    $response = getJson('/api/update/check?session_token=update-check-session-token-no-license');

    $response->assertForbidden()
        ->assertJsonPath('error_code', 'LICENSE_INEFFECTIVE');
});

it('returns package not found when channel has no releases', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '1.0.0',
        'release_channel' => 'stable',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=dev');

    $response->assertNotFound()
        ->assertJsonPath('error_code', 'PACKAGE_NOT_FOUND');
});

it('returns update decision fields when current_version is provided and update is available', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '2.0.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/2.0.0.zip',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&current_version=1.9.9');

    $response->assertSuccessful()
        ->assertJsonPath('data.current_version', '1.9.9')
        ->assertJsonPath('data.version', '2.0.0')
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

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&current_version=3.0.0');

    $response->assertSuccessful()
        ->assertJsonPath('data.current_version', '3.0.0')
        ->assertJsonPath('data.update_available', false)
        ->assertJsonPath('data.reason', 'up_to_date');
});

it('returns invalid version when current_version is malformed', function () {
    seedUpdateCheckContext();

    PackageRelease::factory()->create([
        'version' => '1.0.0',
        'release_channel' => 'stable',
    ]);

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&current_version=bad-version');

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'INVALID_VERSION');
});

it('returns invalid channel when release_channel is unsupported', function () {
    seedUpdateCheckContext();

    $response = getJson('/api/update/check?session_token=update-check-session-token-001&release_channel=beta');

    $response->assertUnprocessable()
        ->assertJsonPath('error_code', 'INVALID_CHANNEL');
});

it('returns auth required when session token exceeds max length', function () {
    $tooLongSessionToken = str_repeat('a', 129);

    $response = getJson('/api/update/check?session_token='.$tooLongSessionToken);

    $response->assertUnauthorized()
        ->assertJsonPath('error_code', 'AUTH_REQUIRED');
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
        ->assertJsonPath('data.release_channel', 'dev')
        ->assertJsonPath('data.version', '4.0.0');
});
