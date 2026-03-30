<?php

namespace Database\Factories;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    private static int $usernameSequence = 0;

    private const EMAIL_LOCAL_POOL = [
        'hello',
        'contact',
        'support',
        'service',
        'notify',
        'updates',
        'team',
        'ops',
        'client',
        'member',
        'account',
        'portal',
        'desk',
        'secure',
        'access',
        'user',
        'profile',
        'community',
        'help',
        'welcome',
    ];

    private const EMAIL_DOMAIN_POOL = [
        'gmail.com',
        'outlook.com',
        'yahoo.com',
        'icloud.com',
        'proton.me',
        'hotmail.com',
        'live.com',
        'qq.com',
    ];

    private function toCarbon(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value);
        }

        return null;
    }

    private function randomDateBetween(Carbon $start, Carbon $end): Carbon
    {
        if ($start->greaterThan($end)) {
            return $end->copy();
        }

        return Carbon::instance(fake()->dateTimeBetween($start, $end));
    }

    private function randomOptionalDateBetween(Carbon $start, Carbon $end, float $weight): ?Carbon
    {
        if ($start->greaterThan($end)) {
            return null;
        }

        if (! fake()->boolean((int) round($weight * 100))) {
            return null;
        }

        return $this->randomDateBetween($start, $end);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $now = now();
        $createdAt = Carbon::instance(fake()->dateTimeBetween($now->copy()->subDays(365), $now));
        $suspended = fake()->boolean(5); // 5% chance of being suspended
        $suspendedUntil = $suspended
            ? $this->randomOptionalDateBetween($now, $now->copy()->addDays(30), 0.5)
            : null;
        $verificationUpperBound = $now->copy();
        if ($suspendedUntil instanceof Carbon && $suspendedUntil->lessThan($verificationUpperBound)) {
            $verificationUpperBound = $suspendedUntil->copy();
        }
        if ($verificationUpperBound->lessThan($createdAt)) {
            $verificationUpperBound = $createdAt->copy();
        }
        $username = $this->generateUsername();
        $email = $this->generateEmail($username);
        $emailVerifiedAt = $this->randomDateBetween($createdAt, $verificationUpperBound);
        $hasTwoFactor = fake()->boolean(20);

        $twoFactorSecret = null;
        $twoFactorRecoveryCodes = null;
        $twoFactorConfirmedAt = null;

        if ($hasTwoFactor) {
            $twoFactorSecret = encrypt(Str::random(32));
            $twoFactorRecoveryCodes = encrypt(json_encode([Str::random(10), Str::random(10)]));

            if ($emailVerifiedAt && fake()->boolean(80)) {
                $twoFactorFloor = $emailVerifiedAt->copy();
                if ($twoFactorFloor->greaterThan($verificationUpperBound)) {
                    $twoFactorFloor = $verificationUpperBound->copy();
                }

                $twoFactorConfirmedAt = $this->randomDateBetween($twoFactorFloor, $verificationUpperBound);
            }
        }

        $lastLoginAt = $this->randomOptionalDateBetween($createdAt, $now, 0.7);
        $hwidLastResetAt = $this->randomOptionalDateBetween($createdAt, $now, 0.3);
        $updatedAt = $this->randomDateBetween($createdAt, $now);

        return [
            'username' => $username,
            'email' => $email,
            'password' => Hash::make('password'), // Default test password
            'last_login_at' => $lastLoginAt,
            'last_ip_address' => fake()->boolean(70) ? $this->generateValidIpv4() : null,
            'last_user_agent' => fake()->optional()->userAgent(),
            'hwid_reset_count' => fake()->numberBetween(0, 5),
            'hwid_last_reset_at' => $hwidLastResetAt,
            'is_suspended' => $suspended,
            'suspension_reason' => $suspended ? fake()->randomElement([
                'Violation of terms of service',
                'Suspicious activity detected',
                'Payment issue',
                'Manual suspension by admin',
                'Multiple failed login attempts',
            ]) : null,
            'suspended_until' => $suspendedUntil,
            'email_verified_at' => $emailVerifiedAt,
            'two_factor_secret' => $twoFactorSecret,
            'two_factor_recovery_codes' => $twoFactorRecoveryCodes,
            'two_factor_confirmed_at' => $twoFactorConfirmedAt,
            'remember_token' => Str::random(10),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    private function generateUsername(): string
    {
        self::$usernameSequence++;

        $rawUsername = strtolower(fake()->userName());
        $normalized = preg_replace('/[^a-z0-9]/', '', $rawUsername);

        if (! is_string($normalized) || $normalized === '') {
            $normalized = 'user';
        }

        $base = Str::limit($normalized, 24, '');
        $suffix = base_convert((string) self::$usernameSequence, 10, 36);

        return Str::limit($base.$suffix, 32, '');
    }

    private function generateEmail(string $username): string
    {
        $poolIndex = max(self::$usernameSequence - 1, 0);
        $localPrefix = self::EMAIL_LOCAL_POOL[$poolIndex % count(self::EMAIL_LOCAL_POOL)];
        $domain = self::EMAIL_DOMAIN_POOL[$poolIndex % count(self::EMAIL_DOMAIN_POOL)];
        $fakeLocal = strtolower((string) fake()->unique()->userName());
        $normalizedLocal = preg_replace('/[^a-z0-9]/', '', $fakeLocal);

        if (! is_string($normalizedLocal) || $normalizedLocal === '') {
            $normalizedLocal = 'mail'.base_convert((string) self::$usernameSequence, 10, 36);
        }

        $localPart = Str::limit($localPrefix.$normalizedLocal, 48, '');

        return strtolower($localPart.'@'.$domain);
    }

    /**
     * Generate a valid IPv4 address string.
     */
    private function generateValidIpv4(): string
    {
        return sprintf('%d.%d.%d.%d',
            fake()->numberBetween(1, 255),
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255)
        );
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be verified.
     */
    public function verified(): static
    {
        return $this->state(function (array $attributes): array {
            $createdAt = $this->toCarbon($attributes['created_at'] ?? null) ?? now()->subYear();
            $now = now();

            if ($createdAt->greaterThan($now)) {
                $createdAt = $now->copy();
            }

            return [
                'email_verified_at' => $this->randomDateBetween($createdAt, $now),
            ];
        });
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(function (array $attributes) {
            $createdAt = $this->toCarbon($attributes['created_at'] ?? null) ?? now()->subDays(30);
            $now = now();

            if ($createdAt->greaterThan($now)) {
                $createdAt = $now->copy();
            }

            $verificationFloor = $this->toCarbon($attributes['email_verified_at'] ?? null) ?? $createdAt->copy();
            if ($verificationFloor->lessThan($createdAt)) {
                $verificationFloor = $createdAt->copy();
            }
            if ($verificationFloor->greaterThan($now)) {
                $verificationFloor = $now->copy();
            }

            $emailVerifiedAt = $this->toCarbon($attributes['email_verified_at'] ?? null)
                ?? $this->randomDateBetween($createdAt, $now);
            if ($emailVerifiedAt->lessThan($createdAt)) {
                $emailVerifiedAt = $createdAt->copy();
            }
            if ($emailVerifiedAt->greaterThan($now)) {
                $emailVerifiedAt = $now->copy();
            }

            $twoFactorFloor = $verificationFloor->greaterThan($emailVerifiedAt)
                ? $verificationFloor->copy()
                : $emailVerifiedAt->copy();
            if ($twoFactorFloor->greaterThan($now)) {
                $twoFactorFloor = $now->copy();
            }

            return [
                'email_verified_at' => $emailVerifiedAt,
                'two_factor_secret' => encrypt('secret'),
                'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
                'two_factor_confirmed_at' => $this->randomDateBetween($twoFactorFloor, $now),
            ];
        });
    }

    /**
     * Indicate that the account is suspended.
     */
    public function suspended(?string $reason = null): static
    {
        return $this->state(function (array $attributes) use ($reason): array {
            $now = now();

            return [
                'is_suspended' => true,
                'suspension_reason' => $reason ?? fake()->randomElement([
                    'Violation of terms of service',
                    'Suspicious activity detected',
                ]),
                'suspended_until' => $this->randomOptionalDateBetween($now, $now->copy()->addDays(30), 0.7),
            ];
        });
    }

    /**
     * Indicate that the account is active (not suspended).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_suspended' => false,
            'suspension_reason' => null,
            'suspended_until' => null,
        ]);
    }

    /**
     * Indicate that the account has recently logged in.
     */
    public function recentlyActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
            'last_ip_address' => $this->generateValidIpv4(),
            'last_user_agent' => fake()->userAgent(),
        ]);
    }

    /**
     * Indicate that the account has reset HWID multiple times.
     */
    public function withHwidResets(int $count = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'hwid_reset_count' => $count,
            'hwid_last_reset_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}
