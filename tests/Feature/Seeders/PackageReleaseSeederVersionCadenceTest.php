<?php

use App\Models\PackageRelease;
use Database\Seeders\PackageReleaseSeeder;

use function Pest\Laravel\seed;

it('ensures beta releases reuse the same sequence as their paired stable release', function (): void {
    seed([
        PackageReleaseSeeder::class,
    ]);

    $betaVersions = PackageRelease::query()
        ->where('release_channel', 'dev')
        ->pluck('version')
        ->all();

    expect($betaVersions)->not->toBeEmpty();

    foreach ($betaVersions as $betaVersion) {
        expect($betaVersion)->toEndWith('-beta');

        preg_match('/^(\d{2}\.\d{1,2}\.\d+)-beta$/', $betaVersion, $matches);

        expect($matches)->toHaveCount(2);

        $stableVersion = $matches[1];

        expect(PackageRelease::query()->where('version', $stableVersion)->where('release_channel', 'stable')->exists())->toBeTrue();
    }
});
