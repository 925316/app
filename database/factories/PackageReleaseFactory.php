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
        $major = $this->faker->numberBetween(1, 10);
        $minor = $this->faker->numberBetween(0, 20);
        $patch = $this->faker->numberBetween(0, 100);

        $version = "{$major}.{$minor}.{$patch}";

        // Randomly add pre-release tag
        if ($this->faker->boolean(20)) {
            $version .= '-'.$this->faker->randomElement(['alpha', 'beta', 'rc']);
        }

        // Randomly add build metadata
        if ($this->faker->boolean(10)) {
            $version .= '+'.Str::random(8);
        }

        return [
            'version' => $version,
            'release_channel' => $this->faker->randomElement(['stable', 'dev']),
            'download_url' => $this->faker->url(),
            'checksum_sha256' => $this->faker->boolean(80)
                ? hash('sha256', $this->faker->text(50))
                : null,
            'changelog' => $this->faker->boolean(70)
                ? $this->generateChangelog()
                : null,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
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
