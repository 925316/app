<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ApiSigningKey;
use App\Models\ClientSession;
use App\Models\License;
use App\Models\PackageRelease;
use App\Services\CryptoService;

use function Pest\Laravel\getJson;

it('guest is redirected from signing key management', function () {
    $key = ApiSigningKey::factory()->create();

    $this->get(route('api-signing-keys.index'))->assertRedirect(route('login'));
    $this->post(route('api-signing-keys.rotate'), ['confirm_rotation' => 1])->assertRedirect(route('login'));
    $this->post(route('api-signing-keys.activate', $key))->assertRedirect(route('login'));
});

it('non-admin cannot manage signing keys', function () {
    $user = createUserWithLicense(1);
    $user->forceFill(['email_verified_at' => now()])->save();
    $key = ApiSigningKey::factory()->create();

    $this->actingAs($user)->get(route('api-signing-keys.index'))->assertForbidden();
    $this->actingAs($user)->post(route('api-signing-keys.rotate'), ['confirm_rotation' => 1])->assertForbidden();
    $this->actingAs($user)->post(route('api-signing-keys.activate', $key))->assertForbidden();
});

it('admin can view signing key metadata without private key contents', function () {
    $admin = createAdmin();
    ApiSigningKey::factory()->active()->create([
        'key_id' => 'api-visible',
        'public_key' => '-----BEGIN PUBLIC KEY----- visible -----END PUBLIC KEY-----',
        'private_key_path' => 'storage/app/keys/api-visible.pem',
    ]);

    $this->actingAs($admin)
        ->get(route('api-signing-keys.index'))
        ->assertSuccessful()
        ->assertSee('api-visible')
        ->assertSee('BEGIN PUBLIC KEY')
        ->assertDontSee('BEGIN PRIVATE KEY');
});

it('admin can rotate a signing key from the web UI', function () {
    $admin = createAdmin();
    config()->set('services.api_signing.key_directory', 'storage/app/testing-keys');

    $this->actingAs($admin)
        ->post(route('api-signing-keys.rotate'), ['confirm_rotation' => 1])
        ->assertRedirect();

    $key = ApiSigningKey::query()->active()->first();

    expect($key)->not->toBeNull()
        ->and($key->created_by)->toBe($admin->id)
        ->and(is_file(base_path($key->private_key_path)))->toBeTrue();
});

it('admin can activate a retained signing key', function () {
    $admin = createAdmin();
    $active = ApiSigningKey::factory()->active()->create();
    $retained = ApiSigningKey::factory()->retired()->create();

    $this->actingAs($admin)
        ->post(route('api-signing-keys.activate', $retained))
        ->assertRedirect();

    expect($retained->fresh()->is_active)->toBeTrue()
        ->and($active->fresh()->is_active)->toBeFalse();
});

it('signed API responses report the active database key id', function () {
    $cryptoService = new class extends CryptoService
    {
        public function signData(mixed $data): string
        {
            return 'test-signature';
        }
    };

    ApiSigningKey::factory()->active()->create([
        'key_id' => 'db-active-key',
    ]);

    app()->instance(CryptoService::class, $cryptoService);

    $account = Account::factory()->create();
    $device = AccountDevice::factory()->bound()->create([
        'account_id' => $account->id,
        'hwid_hash' => hash('sha256', 'HWID-ACTIVE-KEY-ID'),
    ]);
    License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::STANDARD->value,
        'used_by' => $account->id,
        'expires_at' => now()->addDays(30),
    ]);
    ClientSession::factory()->create([
        'session_token' => 'active-key-id-session-token',
        'account_id' => $account->id,
        'device_id' => $device->id,
    ]);
    PackageRelease::factory()->create([
        'version' => '1.3.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/1.3.0.zip',
    ]);

    getJson('/api/update/check?session_token=active-key-id-session-token&release_channel=stable')
        ->assertSuccessful()
        ->assertJsonPath('meta.signature.key_id', 'db-active-key');
});
