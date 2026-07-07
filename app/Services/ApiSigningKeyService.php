<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ApiSigningKey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ApiSigningKeyService
{
    public function activeKey(): ?ApiSigningKey
    {
        return ApiSigningKey::query()->active()->latest('activated_at')->latest('id')->first();
    }

    public function keyId(): string
    {
        return $this->activeKey()?->key_id ?? (string) config('services.api_signing.key_id', 'main-2026-01');
    }

    public function algorithm(): string
    {
        return $this->activeKey()?->algorithm ?? (string) config('services.api_signing.algorithm', 'RSA-2048-SHA256');
    }

    public function privateKeyPath(): string
    {
        $configuredPath = $this->activeKey()?->private_key_path ?? config('services.api_signing.private_key_path');

        if (! is_string($configuredPath) || $configuredPath === '') {
            throw new RuntimeException('API signing private key path is not configured.');
        }

        return $configuredPath;
    }

    public function rotate(Account $actor): ApiSigningKey
    {
        $keyPair = $this->generateKeyPair();
        $keyId = 'api-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(6));
        $privateKeyPath = $this->writePrivateKey($keyId, $keyPair['private_key']);
        $publicKey = $keyPair['public_key'];
        $fingerprint = hash('sha256', $publicKey);

        return DB::transaction(function () use ($actor, $keyId, $privateKeyPath, $publicKey, $fingerprint): ApiSigningKey {
            ApiSigningKey::query()
                ->active()
                ->update([
                    'is_active' => false,
                    'rotated_at' => now(),
                    'retired_at' => now(),
                ]);

            return ApiSigningKey::query()->create([
                'key_id' => $keyId,
                'algorithm' => (string) config('services.api_signing.algorithm', 'RSA-2048-SHA256'),
                'public_key' => $publicKey,
                'public_key_fingerprint' => $fingerprint,
                'private_key_path' => $privateKeyPath,
                'is_active' => true,
                'activated_at' => now(),
                'created_by' => $actor->id,
            ]);
        });
    }

    public function activate(ApiSigningKey $apiSigningKey, Account $actor): ApiSigningKey
    {
        return DB::transaction(function () use ($apiSigningKey, $actor): ApiSigningKey {
            ApiSigningKey::query()
                ->whereKeyNot($apiSigningKey->getKey())
                ->active()
                ->update([
                    'is_active' => false,
                    'rotated_at' => now(),
                    'retired_at' => now(),
                ]);

            $apiSigningKey->forceFill([
                'is_active' => true,
                'activated_at' => now(),
                'retired_at' => null,
                'created_by' => $apiSigningKey->created_by ?? $actor->id,
            ])->save();

            return $apiSigningKey->refresh();
        });
    }

    public function cleanupRetiredMetadata(int $days): int
    {
        return ApiSigningKey::query()
            ->where('is_active', false)
            ->whereNotNull('retired_at')
            ->where('retired_at', '<=', now()->subDays($days))
            ->delete();
    }

    /**
     * @return array{private_key: string, public_key: string}
     */
    private function generateKeyPair(): array
    {
        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'config' => $this->opensslConfigPath(),
        ]);

        if ($resource === false) {
            throw new RuntimeException('Failed to generate API signing key pair.');
        }

        $privateKey = '';
        if (! openssl_pkey_export($resource, $privateKey, null, ['config' => $this->opensslConfigPath()]) || $privateKey === '') {
            throw new RuntimeException('Failed to export API signing private key.');
        }

        $details = openssl_pkey_get_details($resource);
        $publicKey = is_array($details) && isset($details['key']) && is_string($details['key']) ? $details['key'] : '';

        if ($publicKey === '') {
            throw new RuntimeException('Failed to derive API signing public key.');
        }

        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey,
        ];
    }

    private function writePrivateKey(string $keyId, string $privateKey): string
    {
        $directory = (string) config('services.api_signing.key_directory', 'storage/app/keys');
        $resolvedDirectory = $this->resolvePath($directory);

        if (! is_dir($resolvedDirectory) && ! mkdir($resolvedDirectory, 0700, true) && ! is_dir($resolvedDirectory)) {
            throw new RuntimeException('Failed to create API signing key directory.');
        }

        $relativePath = rtrim($directory, '/\\').DIRECTORY_SEPARATOR.$keyId.'.pem';
        $resolvedPath = $this->resolvePath($relativePath);

        if (file_put_contents($resolvedPath, $privateKey, LOCK_EX) === false) {
            throw new RuntimeException('Failed to write API signing private key file.');
        }

        @chmod($resolvedPath, 0600);

        return str_replace('\\', '/', $relativePath);
    }

    private function resolvePath(string $path): string
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return base_path($path);
    }

    private function isAbsolutePath(string $path): bool
    {
        if (Str::startsWith($path, ['/', '\\'])) {
            return true;
        }

        return strlen($path) >= 3
            && ctype_alpha($path[0])
            && $path[1] === ':'
            && in_array($path[2], ['\\', '/'], true);
    }

    private function opensslConfigPath(): string
    {
        $path = (string) config('services.api_signing.openssl_config_path', 'config/openssl.cnf');

        return $this->resolvePath($path);
    }
}
