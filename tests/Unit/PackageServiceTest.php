<?php

use App\Models\PackageRelease;
use App\Services\PackageService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    PackageRelease::query()->delete();
});

it('can validate semantic version', function () {
    expect(PackageService::isValidSemanticVersion('1.0.0'))->toBeTrue();
    expect(PackageService::isValidSemanticVersion('2.1.3'))->toBeTrue();
    expect(PackageService::isValidSemanticVersion('1.0.0-alpha'))->toBeTrue();
    expect(PackageService::isValidSemanticVersion('1.0.0-beta.1'))->toBeTrue();
    expect(PackageService::isValidSemanticVersion('1.0.0+build.123'))->toBeTrue();
    expect(PackageService::isValidSemanticVersion('1.0'))->toBeFalse();
    expect(PackageService::isValidSemanticVersion('1'))->toBeFalse();
    expect(PackageService::isValidSemanticVersion('v1.0.0'))->toBeFalse();
    expect(PackageService::isValidSemanticVersion('invalid'))->toBeFalse();
});

it('can upload package', function () {
    $package = PackageService::uploadPackage(
        '1.0.0',
        'stable',
        'https://example.com/download/package-1.0.0.zip',
        'Initial release',
        'https://virustotal.com/scan/123'
    );

    expect($package)->toBeInstanceOf(PackageRelease::class);
    expect($package->version)->toBe('1.0.0');
    expect($package->release_channel)->toBe('stable');
    expect($package->download_url)->toBe('https://example.com/download/package-1.0.0.zip');
    expect($package->changelog)->toBe('Initial release');
});

it('throws validation error for invalid version format', function () {
    expect(fn () => PackageService::uploadPackage(
        'invalid-version',
        'stable',
        'https://example.com/download/package.zip'
    ))->toThrow(ValidationException::class);
});

it('throws validation error for duplicate version', function () {
    PackageService::uploadPackage('1.0.0', 'stable', 'https://example.com/package.zip');

    expect(fn () => PackageService::uploadPackage(
        '1.0.0',
        'stable',
        'https://example.com/package2.zip'
    ))->toThrow(ValidationException::class);
});

it('throws validation error for invalid download URL', function () {
    expect(fn () => PackageService::uploadPackage(
        '1.0.0',
        'stable',
        'invalid-url'
    ))->toThrow(ValidationException::class);
});

it('can get latest release', function () {
    PackageService::uploadPackage('1.0.0', 'stable', 'https://example.com/package-1.0.0.zip');
    PackageService::uploadPackage('2.0.0', 'stable', 'https://example.com/package-2.0.0.zip');
    PackageService::uploadPackage('1.5.0', 'dev', 'https://example.com/package-1.5.0-dev.zip');

    $latestStable = PackageService::getLatestRelease('stable');
    $latestDev = PackageService::getLatestRelease('dev');

    expect($latestStable->version)->toBe('2.0.0');
    expect($latestDev->version)->toBe('1.5.0');
});

it('returns null when no release found', function () {
    $release = PackageService::getLatestRelease('nonexistent');

    expect($release)->toBeNull();
});

it('can get all releases', function () {
    PackageService::uploadPackage('1.0.0', 'stable', 'https://example.com/package-1.0.0.zip');
    PackageService::uploadPackage('2.0.0', 'stable', 'https://example.com/package-2.0.0.zip');
    PackageService::uploadPackage('1.5.0', 'dev', 'https://example.com/package-1.5.0-dev.zip');

    $allReleases = PackageService::getAllReleases();
    $stableReleases = PackageService::getAllReleases('stable');

    expect($allReleases)->toHaveCount(3);
    expect($stableReleases)->toHaveCount(2);
});

it('can get release by version', function () {
    PackageService::uploadPackage('1.0.0', 'stable', 'https://example.com/package.zip');

    $release = PackageService::getReleaseByVersion('1.0.0');

    expect($release)->not->toBeNull();
    expect($release->version)->toBe('1.0.0');
});

it('returns null for non-existent version', function () {
    $release = PackageService::getReleaseByVersion('99.99.99');

    expect($release)->toBeNull();
});

it('can delete release', function () {
    $package = PackageService::uploadPackage('1.0.0', 'stable', 'https://example.com/package.zip');

    $result = PackageService::deleteRelease($package);

    expect($result)->toBeTrue();
    expect(PackageRelease::find($package->id))->toBeNull();
});

it('can update changelog', function () {
    $package = PackageService::uploadPackage('1.0.0', 'stable', 'https://example.com/package.zip');

    $result = PackageService::updateChangelog($package, 'Updated changelog');

    expect($result)->toBeTrue();
    expect($package->fresh()->changelog)->toBe('Updated changelog');
});

it('can get download URL', function () {
    $package = PackageService::uploadPackage('1.0.0', 'stable', 'https://example.com/package.zip');

    $url = PackageService::getDownloadUrl($package);

    expect($url)->toBe('https://example.com/package.zip');
});

it('can format file size', function () {
    expect(PackageService::formatFileSize(500))->toBe('500 bytes');
    expect(PackageService::formatFileSize(1024))->toBe('1.00 KB');
    expect(PackageService::formatFileSize(1048576))->toBe('1.00 MB');
    expect(PackageService::formatFileSize(1073741824))->toBe('1.00 GB');
});

it('can get package statistics', function () {
    PackageService::uploadPackage('1.0.0', 'stable', 'https://example.com/package-1.0.0.zip');
    PackageService::uploadPackage('2.0.0', 'stable', 'https://example.com/package-2.0.0.zip');
    PackageService::uploadPackage('1.5.0-dev', 'dev', 'https://example.com/package-1.5.0-dev.zip');

    $stats = PackageService::getPackageStatistics();

    expect($stats['total_releases'])->toBe(3);
    expect($stats['stable_releases'])->toBe(2);
    expect($stats['dev_releases'])->toBe(1);
    expect($stats['latest_stable'])->not->toBeNull();
    expect($stats['latest_dev'])->not->toBeNull();
});
