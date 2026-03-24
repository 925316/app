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
    expect(PackageService::isValidSemanticVersion('26.3.12'))->toBeTrue();
    expect(PackageService::isValidSemanticVersion('26.3.12-beta'))->toBeTrue();
    expect(PackageService::isValidSemanticVersion('1.0'))->toBeFalse();
    expect(PackageService::isValidSemanticVersion('1'))->toBeFalse();
    expect(PackageService::isValidSemanticVersion('v1.0.0'))->toBeFalse();
    expect(PackageService::isValidSemanticVersion('invalid'))->toBeFalse();
});

it('validates timeline release version for package uploads', function () {
    expect(PackageService::isValidTimelineVersion('26.3.12'))->toBeTrue();
    expect(PackageService::isValidTimelineVersion('26.3.12-beta'))->toBeTrue();
    expect(PackageService::isValidTimelineVersion('1.0.0'))->toBeFalse();
    expect(PackageService::isValidTimelineVersion('26.3'))->toBeFalse();
});

it('safe public https url helper rejects unsafe urls', function () {
    expect(PackageService::isSafePublicHttpsUrl('https://example.com/file.zip'))->toBeTrue();
    expect(PackageService::isSafePublicHttpsUrl('http://example.com/file.zip'))->toBeFalse();
    expect(PackageService::isSafePublicHttpsUrl('https://localhost/file.zip'))->toBeFalse();
    expect(PackageService::isSafePublicHttpsUrl('https://user:pass@example.com/file.zip'))->toBeFalse();
    expect(PackageService::isSafePublicHttpsUrl('https://192.168.1.1/file.zip'))->toBeFalse();
    expect(PackageService::isSafePublicHttpsUrl('not-a-url'))->toBeFalse();
});

it('can upload package', function () {
    $package = PackageService::uploadPackage(
        '26.3.12',
        'stable',
        'https://example.com/download/package-26.3.12.zip',
        'Initial release',
        'https://virustotal.com/scan/123'
    );

    expect($package)->toBeInstanceOf(PackageRelease::class);
    expect($package->version)->toBe('26.3.12');
    expect($package->release_channel)->toBe('stable');
    expect($package->download_url)->toBe('https://example.com/download/package-26.3.12.zip');
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
    PackageService::uploadPackage('26.3.12', 'stable', 'https://example.com/package.zip');

    expect(fn () => PackageService::uploadPackage(
        '26.3.12',
        'stable',
        'https://example.com/package2.zip'
    ))->toThrow(ValidationException::class);
});

it('throws validation error for invalid download URL', function () {
    expect(fn () => PackageService::uploadPackage(
        '26.3.12',
        'stable',
        'invalid-url'
    ))->toThrow(ValidationException::class);
});

it('throws validation error for private network download URL', function () {
    expect(fn () => PackageService::uploadPackage(
        '26.3.13',
        'stable',
        'https://10.0.0.1/private.zip'
    ))->toThrow(ValidationException::class);
});

it('throws validation error for unsafe virus detection URL', function () {
    expect(fn () => PackageService::uploadPackage(
        '26.3.14',
        'stable',
        'https://example.com/download.zip',
        null,
        'https://127.0.0.1/scan'
    ))->toThrow(ValidationException::class);
});

it('can get latest release', function () {
    PackageService::uploadPackage('26.3.11', 'stable', 'https://example.com/package-26.3.11.zip');
    PackageService::uploadPackage('26.3.12', 'stable', 'https://example.com/package-26.3.12.zip');
    PackageService::uploadPackage('26.3.12-beta', 'dev', 'https://example.com/package-26.3.12-beta.zip');

    $latestStable = PackageService::getLatestRelease('stable');
    $latestDev = PackageService::getLatestRelease('dev');

    expect($latestStable->version)->toBe('26.3.12');
    expect($latestDev->version)->toBe('26.3.12-beta');
});

it('returns null when no release found', function () {
    $release = PackageService::getLatestRelease('nonexistent');

    expect($release)->toBeNull();
});

it('can get all releases', function () {
    PackageService::uploadPackage('26.3.10', 'stable', 'https://example.com/package-26.3.10.zip');
    PackageService::uploadPackage('26.3.11', 'stable', 'https://example.com/package-26.3.11.zip');
    PackageService::uploadPackage('26.3.11-beta', 'dev', 'https://example.com/package-26.3.11-beta.zip');

    $allReleases = PackageService::getAllReleases();
    $stableReleases = PackageService::getAllReleases('stable');

    expect($allReleases)->toHaveCount(3);
    expect($stableReleases)->toHaveCount(2);
    expect($allReleases->pluck('version')->values()->all())->toBe([
        '26.3.11',
        '26.3.11-beta',
        '26.3.10',
    ]);
});

it('can get release by version', function () {
    PackageService::uploadPackage('26.3.12', 'stable', 'https://example.com/package.zip');

    $release = PackageService::getReleaseByVersion('26.3.12');

    expect($release)->not->toBeNull();
    expect($release->version)->toBe('26.3.12');
});

it('returns null for non-existent version', function () {
    $release = PackageService::getReleaseByVersion('99.99.99');

    expect($release)->toBeNull();
});

it('can delete release', function () {
    $package = PackageService::uploadPackage('26.3.12', 'stable', 'https://example.com/package.zip');

    $result = PackageService::deleteRelease($package);

    expect($result)->toBeTrue();
    expect(PackageRelease::find($package->id))->toBeNull();
});

it('can update changelog', function () {
    $package = PackageService::uploadPackage('26.3.12', 'stable', 'https://example.com/package.zip');

    $result = PackageService::updateChangelog($package, 'Updated changelog');

    expect($result)->toBeTrue();
    expect($package->fresh()->changelog)->toBe('Updated changelog');
});

it('can get download URL', function () {
    $package = PackageService::uploadPackage('26.3.12', 'stable', 'https://example.com/package.zip');

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
    PackageService::uploadPackage('26.3.10', 'stable', 'https://example.com/package-26.3.10.zip');
    PackageService::uploadPackage('26.3.11', 'stable', 'https://example.com/package-26.3.11.zip');
    PackageService::uploadPackage('26.3.11-beta', 'dev', 'https://example.com/package-26.3.11-beta.zip');

    $stats = PackageService::getPackageStatistics();

    expect($stats['total_releases'])->toBe(3);
    expect($stats['stable_releases'])->toBe(2);
    expect($stats['dev_releases'])->toBe(1);
    expect($stats['latest_stable'])->not->toBeNull();
    expect($stats['latest_dev'])->not->toBeNull();
});

it('compares timeline versions with beta precedence', function () {
    expect(PackageService::compareReleaseVersions('26.3.12', '26.3.11'))->toBeGreaterThan(0);
    expect(PackageService::compareReleaseVersions('26.3.12-beta', '26.3.12'))->toBeLessThan(0);
    expect(PackageService::compareReleaseVersions('26.4.0', '26.3.99'))->toBeGreaterThan(0);
});

it('sorts releases by numeric version magnitude instead of string order', function () {
    PackageService::uploadPackage('26.10.2', 'stable', 'https://example.com/package-26.10.2.zip');
    PackageService::uploadPackage('26.9.20', 'stable', 'https://example.com/package-26.9.20.zip');
    PackageService::uploadPackage('26.10.12', 'stable', 'https://example.com/package-26.10.12.zip');
    PackageService::uploadPackage('26.10.12-beta', 'stable', 'https://example.com/package-26.10.12-beta.zip');

    $sorted = PackageService::getAllReleases('stable')->pluck('version')->values()->all();

    expect($sorted)->toBe([
        '26.10.12',
        '26.10.12-beta',
        '26.10.2',
        '26.9.20',
    ]);
});

it('paginates releases using numeric version ordering', function () {
    PackageService::uploadPackage('26.10.2', 'stable', 'https://example.com/package-26.10.2.zip');
    PackageService::uploadPackage('26.10.1', 'stable', 'https://example.com/package-26.10.1.zip');
    PackageService::uploadPackage('26.9.99', 'stable', 'https://example.com/package-26.9.99.zip');

    $pageOne = PackageService::getPaginatedReleases('stable', 2, 1);
    $pageTwo = PackageService::getPaginatedReleases('stable', 2, 2);

    expect($pageOne->items()[0]->version)->toBe('26.10.2')
        ->and($pageOne->items()[1]->version)->toBe('26.10.1')
        ->and($pageTwo->items()[0]->version)->toBe('26.9.99');
});
