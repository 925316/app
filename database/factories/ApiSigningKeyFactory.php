<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\ApiSigningKey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiSigningKey>
 */
class ApiSigningKeyFactory extends Factory
{
    protected $model = ApiSigningKey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $publicKey = "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode(fake()->sha256()), 64, "\n")."-----END PUBLIC KEY-----\n";

        return [
            'key_id' => 'api-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(6)),
            'algorithm' => 'RSA-2048-SHA256',
            'public_key' => $publicKey,
            'public_key_fingerprint' => hash('sha256', $publicKey),
            'private_key_path' => 'storage/app/keys/api-private-'.Str::lower(Str::random(8)).'.pem',
            'is_active' => false,
            'activated_at' => null,
            'rotated_at' => null,
            'retired_at' => null,
            'created_by' => Account::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
            'activated_at' => now(),
            'retired_at' => null,
        ]);
    }

    public function retired(int $daysAgo = 1): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
            'rotated_at' => now()->subDays($daysAgo),
            'retired_at' => now()->subDays($daysAgo),
        ]);
    }
}
