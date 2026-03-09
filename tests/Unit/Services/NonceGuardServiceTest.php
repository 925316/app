<?php

use App\Services\NonceGuardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

it('returns true when redis nx set succeeds', function () {
    Redis::shouldReceive('set')
        ->once()
        ->andReturn(true);

    $service = app(NonceGuardService::class);

    expect($service->acquire('license.check|session-1', 'nonce-1'))->toBeTrue();
});

it('falls back to cache add when redis is unavailable', function () {
    Redis::shouldReceive('set')
        ->once()
        ->andThrow(new RuntimeException('Redis unavailable'));

    Cache::shouldReceive('add')
        ->once()
        ->andReturn(true);

    $service = app(NonceGuardService::class);

    expect($service->acquire('license.check|session-1', 'nonce-1'))->toBeTrue();
});

it('isolates nonce keys by scope for cache fallback', function () {
    Redis::shouldReceive('set')
        ->times(3)
        ->andThrow(new RuntimeException('Redis unavailable'));

    Cache::shouldReceive('add')
        ->times(3)
        ->andReturnUsing(function (string $cacheKey) {
            static $seen = [];

            if (isset($seen[$cacheKey])) {
                return false;
            }

            $seen[$cacheKey] = true;

            return true;
        });

    $service = app(NonceGuardService::class);

    expect($service->acquire('license.check|session-1', 'nonce-fixed'))->toBeTrue();
    expect($service->acquire('license.activate|session-1', 'nonce-fixed'))->toBeTrue();
    expect($service->acquire('license.check|session-1', 'nonce-fixed'))->toBeFalse();
});
