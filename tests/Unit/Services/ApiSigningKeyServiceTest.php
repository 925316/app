<?php

use App\Models\ApiSigningKey;
use App\Services\ApiSigningKeyService;

it('falls back to configured signing key metadata when no database key is active', function () {
    config()->set('services.api_signing.key_id', 'config-key');
    config()->set('services.api_signing.private_key_path', 'storage/app/keys/config.pem');

    $service = app(ApiSigningKeyService::class);

    expect($service->keyId())->toBe('config-key')
        ->and($service->privateKeyPath())->toBe('storage/app/keys/config.pem');
});

it('uses active database signing key metadata before config fallback', function () {
    ApiSigningKey::factory()->active()->create([
        'key_id' => 'db-key',
        'private_key_path' => 'storage/app/keys/db.pem',
        'algorithm' => 'RSA-2048-SHA256',
    ]);

    $service = app(ApiSigningKeyService::class);

    expect($service->keyId())->toBe('db-key')
        ->and($service->privateKeyPath())->toBe('storage/app/keys/db.pem')
        ->and($service->algorithm())->toBe('RSA-2048-SHA256');
});

it('rotates a managed signing key without storing private key contents in the database', function () {
    $admin = createAdmin();
    config()->set('services.api_signing.key_directory', 'storage/app/testing-keys');

    $key = app(ApiSigningKeyService::class)->rotate($admin);

    expect($key->is_active)->toBeTrue()
        ->and($key->public_key)->toContain('BEGIN PUBLIC KEY')
        ->and($key->private_key_path)->toStartWith('storage/app/testing-keys/')
        ->and($key->getAttributes())->not->toHaveKey('private_key');

    $privateKeyPath = base_path($key->private_key_path);
    expect(is_file($privateKeyPath))->toBeTrue()
        ->and(file_get_contents($privateKeyPath))->toContain('BEGIN PRIVATE KEY');
});

it('activates one retained signing key at a time', function () {
    $admin = createAdmin();
    $active = ApiSigningKey::factory()->active()->create();
    $retained = ApiSigningKey::factory()->retired()->create();

    $activated = app(ApiSigningKeyService::class)->activate($retained, $admin);

    expect($activated->is_active)->toBeTrue()
        ->and($activated->retired_at)->toBeNull()
        ->and($active->fresh()->is_active)->toBeFalse()
        ->and($active->fresh()->retired_at)->not->toBeNull();
});

it('cleans only old retired signing key metadata', function () {
    ApiSigningKey::factory()->active()->create();
    $oldRetired = ApiSigningKey::factory()->retired(400)->create();
    $recentRetired = ApiSigningKey::factory()->retired(2)->create();

    $deleted = app(ApiSigningKeyService::class)->cleanupRetiredMetadata(365);

    expect($deleted)->toBe(1)
        ->and(ApiSigningKey::query()->whereKey($oldRetired)->exists())->toBeFalse()
        ->and(ApiSigningKey::query()->whereKey($recentRetired)->exists())->toBeTrue()
        ->and(ApiSigningKey::query()->active()->count())->toBe(1);
});
