<?php

use Illuminate\Testing\TestResponse;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Shared Helpers
|--------------------------------------------------------------------------
|
| Global helper functions to reduce code duplication across test files.
|
*/

function createAdmin(): App\Models\Account
{
    $admin = App\Models\Account::factory()->active()->verified()->create();
    App\Models\License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    return $admin;
}

function createUserWithLicense(int $privilege = 1): App\Models\Account
{
    $user = App\Models\Account::factory()->active()->create([
        'hwid_last_reset_at' => null,
    ]);
    App\Models\License::factory()->active()->privilege($privilege)->create([
        'used_by' => $user->id,
        'expires_at' => now()->addYear(),
    ]);

    return $user;
}

function assertApiOk(TestResponse $response, array $jsonPaths = []): void
{
    $response->assertSuccessful()
        ->assertJsonPath('code', 200)
        ->assertJsonPath('error_code', null)
        ->assertJsonPath('message', 'OK');

    foreach ($jsonPaths as $path => $expected) {
        $response->assertJsonPath($path, $expected);
    }
}

function mockRedisSetUnavailable(): void
{
    Illuminate\Support\Facades\Redis::shouldReceive('set')
        ->andThrow(new RuntimeException('Redis unavailable'));
}
