<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PackageRelease;

class PackageReleaseFactory extends Factory
{
    protected $model = PackageRelease::class;

    public function definition(): array
    {
        static $versions = [];

        do {
            $major = $this->faker->numberBetween(1, 3);
            $minor = $this->faker->numberBetween(0, 10);
            $patch = $this->faker->numberBetween(0, 50);

            if ($this->faker->boolean(10)) {
                $prerelease = $this->faker->randomElement(['alpha', 'beta', 'rc']);
                $prereleaseNumber = $this->faker->numberBetween(1, 3);
                $version = "{$major}.{$minor}.{$patch}-{$prerelease}.{$prereleaseNumber}";
            } else {
                $version = "{$major}.{$minor}.{$patch}";
            }
        } while (in_array($version, $versions));

        $versions[] = $version;

        $createdAt = now()->subDays($this->faker->numberBetween(0, 730)); // 0-2 years

        return [
            'version' => $version,
            'release_channel' => $this->faker->randomElement(['stable', 'dev']),
            'download_url' => $this->generateDownloadUrl($version),
            'checksum_sha256' => hash('sha256', $this->faker->text(100) . time()),
            'changelog' => $this->generateChangelog(),
            'download_count' => $this->faker->numberBetween(0, 50000),
            'created_at' => $createdAt,
            'updated_at' => (clone $createdAt)->addDays($this->faker->numberBetween(0, 30)),
        ];
    }

    private function generateDownloadUrl(string $version): string
    {
        $baseUrl = $this->faker->randomElement([
            'https://downloads.example.com',
            'https://releases.company.com',
            'https://cdn.software.org',
        ]);
        
        $encodedVersion = urlencode($version);
        return "{$baseUrl}/package-{$encodedVersion}.zip";
    }

    private function generateChangelog(): string
    {
        $changes = [];
        $changeTypes = ['Added', 'Fixed', 'Changed', 'Removed', 'Security'];

        for ($i = 0; $i < $this->faker->numberBetween(3, 10); $i++) {
            $changeType = $this->faker->randomElement($changeTypes);
            $changes[] = "- [{$changeType}] " . $this->faker->sentence();
        }

        return implode("\n", $changes);
    }

    public function stable(): static
    {
        return $this->state([
            'release_channel' => 'stable',
            'download_count' => $this->faker->numberBetween(1000, 50000),
        ]);
    }

    public function dev(): static
    {
        return $this->state([
            'release_channel' => 'dev',
            'download_count' => $this->faker->numberBetween(0, 1000),
        ]);
    }

    public function popular(): static
    {
        return $this->state([
            'download_count' => $this->faker->numberBetween(10000, 100000),
        ]);
    }

    public function recent(): static
    {
        return $this->state([
            'created_at' => now()->subDays($this->faker->numberBetween(0, 7)),
        ]);
    }
}
