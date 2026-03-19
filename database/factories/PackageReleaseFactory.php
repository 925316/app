<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PackageReleaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $releaseDate = fake()->dateTimeBetween('-365 days', 'now');
        $version = $this->generateVersion();
        $downloadUrl = $this->generateDownloadUrl($version);
        $virusToken = hash('sha256', $version.'|'.$downloadUrl);

        return [
            'version' => $version,
            'release_channel' => str_contains($version, '-') ? 'dev' : 'stable',
            'download_url' => $downloadUrl,
            'virus_detection_url' => fake()->boolean(70)
                ? "https://www.virustotal.com/gui/file/{$virusToken}"
                : null,
            'changelog' => fake()->boolean(80)
                ? $this->generateChangelog()
                : null,
            'created_at' => $releaseDate,
            'updated_at' => $releaseDate,
        ];
    }

    /**
     * Generate a semantic version number.
     */
    private function generateVersion(): string
    {
        $major = fake()->randomElement([1, 2, 3]);
        $minor = fake()->numberBetween(0, 6);
        $patch = fake()->numberBetween(0, 12);

        $version = "{$major}.{$minor}.{$patch}";

        if (fake()->boolean(20)) {
            $version .= '-'.fake()->randomElement(['alpha', 'beta', 'rc']).'.'.fake()->numberBetween(1, 3);
        }

        return $version;
    }

    private function generateDownloadUrl(string $version): string
    {
        $channel = str_contains($version, '-') ? 'dev' : 'stable';
        $platform = fake()->randomElement(['win-x64', 'linux-x64', 'macos-universal']);

        return "https://downloads.demo-license.local/releases/{$channel}/{$version}/acme-client-{$version}-{$platform}.zip";
    }

    /**
     * Indicate that the release is stable.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function stable()
    {
        return $this->state(function (array $attributes) {
            $version = $attributes['version'] ?? $this->generateVersion();

            if (is_string($version) && str_contains($version, '-')) {
                $version = preg_replace('/-.+$/', '', $version) ?: '2.0.0';
            }

            $downloadUrl = $this->generateDownloadUrl((string) $version);
            $virusToken = hash('sha256', $version.'|'.$downloadUrl);

            return [
                'version' => $version,
                'release_channel' => 'stable',
                'download_url' => $downloadUrl,
                'virus_detection_url' => "https://www.virustotal.com/gui/file/{$virusToken}",
            ];
        });
    }

    /**
     * Indicate that the release is a development release.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function dev()
    {
        return $this->state(function (array $attributes) {
            $baseVersion = $attributes['version'] ?? $this->generateVersion();
            $version = is_string($baseVersion) && str_contains($baseVersion, '-')
                ? $baseVersion
                : ((string) $baseVersion).'-beta.1';

            $downloadUrl = $this->generateDownloadUrl($version);
            $virusToken = hash('sha256', $version.'|'.$downloadUrl);

            return [
                'version' => $version,
                'release_channel' => 'dev',
                'download_url' => $downloadUrl,
                'virus_detection_url' => fake()->boolean(50)
                    ? "https://www.virustotal.com/gui/file/{$virusToken}"
                    : null,
            ];
        });
    }

    /**
     * Indicate a specific major version.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function majorVersion(int $major)
    {
        return $this->state(function (array $attributes) use ($major) {
            $minor = fake()->numberBetween(0, 6);
            $patch = fake()->numberBetween(0, 12);
            $version = "{$major}.{$minor}.{$patch}";
            $downloadUrl = $this->generateDownloadUrl($version);
            $virusToken = hash('sha256', $version.'|'.$downloadUrl);

            return [
                'version' => $version,
                'download_url' => $downloadUrl,
                'virus_detection_url' => "https://www.virustotal.com/gui/file/{$virusToken}",
            ];
        });
    }

    /**
     * Generate realistic changelog content.
     */
    private function generateChangelog(): string
    {
        $features = fake()->sentences(fake()->numberBetween(2, 5));
        $fixes = fake()->sentences(fake()->numberBetween(1, 4));
        $improvements = fake()->sentences(fake()->numberBetween(1, 3));

        $changelog = "## Changelog\n\n";

        if (! empty($features)) {
            $changelog .= "### New Features\n";
            foreach ($features as $feature) {
                $changelog .= "- {$feature}\n";
            }
            $changelog .= "\n";
        }

        if (! empty($fixes)) {
            $changelog .= "### Bug Fixes\n";
            foreach ($fixes as $fix) {
                $changelog .= "- {$fix}\n";
            }
            $changelog .= "\n";
        }

        if (! empty($improvements)) {
            $changelog .= "### Improvements\n";
            foreach ($improvements as $improvement) {
                $changelog .= "- {$improvement}\n";
            }
            $changelog .= "\n";
        }

        return $changelog;
    }
}
