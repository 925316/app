<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        // Generate release date between 2 years ago and now
        $releaseDate = $this->faker->dateTimeBetween('-2 years', 'now');

        return [
            'version' => $this->generateVersion(),
            'release_channel' => $this->faker->randomElement(['stable', 'dev']),
            'download_url' => $this->faker->url(),
            'checksum_sha256' => null, // No checksum for remote files
            'changelog' => $this->faker->boolean(80)
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
        $major = $this->faker->numberBetween(1, 5);
        $minor = $this->faker->numberBetween(0, 20);
        $patch = $this->faker->numberBetween(0, 50);

        $version = "{$major}.{$minor}.{$patch}";

        // Less frequently add pre-release tag
        if ($this->faker->boolean(15)) {
            $version .= '-'.$this->faker->randomElement(['alpha', 'beta', 'rc']);
            if ($this->faker->boolean(50)) {
                $version .= '.'.$this->faker->numberBetween(1, 10);
            }
        }

        // Rarely add build metadata
        if ($this->faker->boolean(5)) {
            $version .= '+'.Str::random(8);
        }

        return $version;
    }

    /**
     * Indicate that the release is stable.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function stable()
    {
        return $this->state(function (array $attributes) {
            return [
                'release_channel' => 'stable',
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
            return [
                'release_channel' => 'dev',
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
            $minor = $this->faker->numberBetween(0, 20);
            $patch = $this->faker->numberBetween(0, 100);

            return [
                'version' => "{$major}.{$minor}.{$patch}",
            ];
        });
    }

    /**
     * Generate realistic changelog content.
     */
    private function generateChangelog(): string
    {
        $features = $this->faker->sentences($this->faker->numberBetween(2, 5));
        $fixes = $this->faker->sentences($this->faker->numberBetween(1, 4));
        $improvements = $this->faker->sentences($this->faker->numberBetween(1, 3));

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
