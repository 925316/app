<?php

use App\Models\PackageRelease;

beforeEach(function () {
    $this->stableRelease = PackageRelease::factory()->create([
        'version' => '2.1.3',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/package-2.1.3.zip',
    ]);

    $this->devRelease = PackageRelease::factory()->create([
        'version' => '3.0.0-beta',
        'release_channel' => 'dev',
        'download_url' => 'https://example.com/package-3.0.0-beta.zip',
    ]);
});

// --- Scopes ---

it('stable scope returns only stable channel releases', function () {
    $stableReleases = PackageRelease::stable()->get();

    foreach ($stableReleases as $release) {
        expect($release->release_channel)->toBe('stable');
    }
    expect($stableReleases->pluck('id')->contains($this->devRelease->id))->toBeFalse();
});

it('dev scope returns only dev channel releases', function () {
    $devReleases = PackageRelease::dev()->get();

    foreach ($devReleases as $release) {
        expect($release->release_channel)->toBe('dev');
    }
    expect($devReleases->pluck('id')->contains($this->stableRelease->id))->toBeFalse();
});

// --- is_stable / is_dev Attributes ---

it('is_stable attribute returns true for stable releases', function () {
    expect($this->stableRelease->is_stable)->toBeTrue();
    expect($this->stableRelease->is_dev)->toBeFalse();
});

it('is_dev attribute returns true for dev releases', function () {
    expect($this->devRelease->is_dev)->toBeTrue();
    expect($this->devRelease->is_stable)->toBeFalse();
});

// --- Version Parsing Attributes ---

it('major_version attribute returns major part of version', function () {
    expect($this->stableRelease->major_version)->toBe('2');
});

it('minor_version attribute returns minor part of version', function () {
    expect($this->stableRelease->minor_version)->toBe('1');
});

it('patch_version attribute returns patch part of version', function () {
    expect($this->stableRelease->patch_version)->toBe('3');
});

it('returns null for major version when format is invalid', function () {
    $release = PackageRelease::factory()->create([
        'version' => 'not-a-version',
        'release_channel' => 'dev',
        'download_url' => 'https://example.com/package.zip',
    ]);

    expect($release->major_version)->toBeNull();
    expect($release->minor_version)->toBeNull();
    expect($release->patch_version)->toBeNull();
});

// --- RELEASE_CHANNELS Constant ---

it('has stable and dev as available release channels', function () {
    expect(PackageRelease::RELEASE_CHANNELS)->toContain('stable');
    expect(PackageRelease::RELEASE_CHANNELS)->toContain('dev');
    expect(PackageRelease::RELEASE_CHANNELS)->toHaveCount(2);
});

// --- fillable ---

it('can be created with fillable attributes', function () {
    $release = PackageRelease::factory()->create([
        'version' => '5.0.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/package-5.0.0.zip',
        'virus_detection_url' => 'https://virustotal.com/scan/abc',
        'changelog' => 'Major release',
    ]);

    expect($release->version)->toBe('5.0.0');
    expect($release->release_channel)->toBe('stable');
    expect($release->download_url)->toBe('https://example.com/package-5.0.0.zip');
    expect($release->virus_detection_url)->toBe('https://virustotal.com/scan/abc');
    expect($release->changelog)->toBe('Major release');
});
