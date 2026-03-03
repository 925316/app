<?php

namespace App\Services;

use RuntimeException;

class CryptoService
{
    public function canonicalize(array $payload): string
    {
        $normalized = $this->normalizeForCanonicalJson($payload);

        $json = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException('Failed to encode canonical JSON payload.');
        }

        return $json;
    }

    public function signData(array $data): string
    {
        $privateKeyPath = config('services.api_signing.private_key_path');

        if (! is_string($privateKeyPath) || $privateKeyPath === '') {
            throw new RuntimeException('API signing private key path is not configured.');
        }

        $resolvedPath = str_starts_with($privateKeyPath, DIRECTORY_SEPARATOR)
            ? $privateKeyPath
            : base_path($privateKeyPath);

        if (! is_file($resolvedPath)) {
            throw new RuntimeException('API signing private key file was not found.');
        }

        $privateKeyContents = file_get_contents($resolvedPath);

        if (! is_string($privateKeyContents) || $privateKeyContents === '') {
            throw new RuntimeException('API signing private key file is empty or unreadable.');
        }

        $privateKey = openssl_pkey_get_private($privateKeyContents);

        if ($privateKey === false) {
            throw new RuntimeException('Failed to parse API signing private key.');
        }

        $canonicalPayload = $this->canonicalize($data);

        $signature = '';
        $ok = openssl_sign($canonicalPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_pkey_free($privateKey);

        if (! $ok) {
            throw new RuntimeException('Failed to sign API payload.');
        }

        return base64_encode($signature);
    }

    private function normalizeForCanonicalJson(mixed $value): mixed
    {
        if (is_array($value)) {
            if ($this->isAssociativeArray($value)) {
                ksort($value, SORT_STRING);
            }

            foreach ($value as $key => $item) {
                $value[$key] = $this->normalizeForCanonicalJson($item);
            }

            return $value;
        }

        return $value;
    }

    /**
     * @param  array<int|string, mixed>  $value
     */
    private function isAssociativeArray(array $value): bool
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }
}
