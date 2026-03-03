<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Throwable;

class NonceGuardService
{
    public function acquire(string $nonce, int $ttlSeconds = 300): bool
    {
        $cacheKey = 'api:nonce:'.hash('sha256', $nonce);

        try {
            $result = Redis::set($cacheKey, '1', 'EX', $ttlSeconds, 'NX');

            return $result === true || $result === 'OK';
        } catch (Throwable) {
            return Cache::add($cacheKey, '1', now()->addSeconds($ttlSeconds));
        }
    }
}
