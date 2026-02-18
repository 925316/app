<?php

use App\Models\Account;
use App\Models\PackageRelease;

beforeEach(function () {
    $this->admin = createAdmin();
    $this->userWithLicense = createUserWithLicense(1);
    $this->userWithoutLicense = Account::factory()->create();
});

// --- Package Index ---

it('user with license can view package list', function () {
    $this->actingAs($this->userWithLicense)
        ->get(route('packages.index'))
        ->assertSuccessful()
        ->assertViewIs('packages.index');
});

it('user without license cannot view package list', function () {
    $this->actingAs($this->userWithoutLicense)
        ->get(route('packages.index'))
        ->assertForbidden();
});

it('admin can view package list', function () {
    $this->actingAs($this->admin)
        ->get(route('packages.index'))
        ->assertSuccessful()
        ->assertViewHasAll(['releases', 'stats', 'isAdmin', 'canDownload']);
});

it('package list can be filtered by channel', function () {
    PackageRelease::factory()->create(['version' => '1.0.0', 'release_channel' => 'stable']);
    PackageRelease::factory()->create(['version' => '2.0.0-dev', 'release_channel' => 'dev']);

    $response = $this->actingAs($this->userWithLicense)
        ->get(route('packages.index', ['channel' => 'stable']));

    $response->assertSuccessful();
    $releases = $response->viewData('releases');
    expect($releases->total())->toBe(1);
    expect($releases->first()->release_channel)->toBe('stable');
});

// --- Package Show ---

it('user with license can view package details', function () {
    $release = PackageRelease::factory()->create(['version' => '1.0.0', 'release_channel' => 'stable']);

    $this->actingAs($this->userWithLicense)
        ->get(route('packages.show', $release))
        ->assertSuccessful()
        ->assertViewIs('packages.show')
        ->assertViewHasAll(['release', 'canDownload', 'isAdmin']);
});

it('user without license cannot view package details', function () {
    $release = PackageRelease::factory()->create(['version' => '1.0.0', 'release_channel' => 'stable']);

    $this->actingAs($this->userWithoutLicense)
        ->get(route('packages.show', $release))
        ->assertForbidden();
});

// --- Package Download ---

it('user with license can download a package', function () {
    $release = PackageRelease::factory()->create([
        'version' => '1.0.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/package-1.0.0.zip',
    ]);

    $this->actingAs($this->userWithLicense)
        ->get(route('packages.download', $release))
        ->assertRedirect('https://example.com/download/package-1.0.0.zip');
});

it('user without license cannot download a package', function () {
    $release = PackageRelease::factory()->create([
        'version' => '1.0.0',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/package-1.0.0.zip',
    ]);

    $this->actingAs($this->userWithoutLicense)
        ->get(route('packages.download', $release))
        ->assertForbidden();
});

// --- Admin Upload (store) ---

it('admin can view package upload form', function () {
    $this->actingAs($this->admin)
        ->get(route('packages.upload'))
        ->assertSuccessful();
});

it('non-admin cannot access package upload form', function () {
    $this->actingAs($this->userWithLicense)
        ->get(route('packages.upload'))
        ->assertForbidden();
});

it('admin can upload a new package', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '3.0.0',
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/download/package-3.0.0.zip',
            'changelog' => 'New release',
        ])
        ->assertRedirect(route('packages.index'))
        ->assertSessionHas('success');

    expect(PackageRelease::where('version', '3.0.0')->exists())->toBeTrue();
});

it('upload validates version format', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => 'bad-version',
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/package.zip',
        ])
        ->assertSessionHasErrors('version');
});

it('upload validates duplicate version', function () {
    PackageRelease::factory()->create(['version' => '1.0.0']);

    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '1.0.0',
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/package.zip',
        ])
        ->assertSessionHasErrors('version');
});

it('upload validates download url', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '2.0.0',
            'release_channel' => 'stable',
            'download_url' => 'not-a-url',
        ])
        ->assertSessionHasErrors('download_url');
});

it('upload validates release channel', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '2.0.0',
            'release_channel' => 'invalid-channel',
            'download_url' => 'https://example.com/package.zip',
        ])
        ->assertSessionHasErrors('release_channel');
});

// --- Admin Manage ---

it('admin can view package manage page', function () {
    $this->actingAs($this->admin)
        ->get(route('packages.manage'))
        ->assertSuccessful()
        ->assertViewIs('packages.manage');
});

it('non-admin cannot access package manage page', function () {
    $this->actingAs($this->userWithLicense)
        ->get(route('packages.manage'))
        ->assertForbidden();
});

// --- Admin Delete ---

it('admin can delete a package release', function () {
    $release = PackageRelease::factory()->create(['version' => '1.0.0', 'release_channel' => 'stable']);

    $this->actingAs($this->admin)
        ->delete(route('packages.destroy', $release))
        ->assertRedirect(route('packages.manage'))
        ->assertSessionHas('success');

    expect(PackageRelease::find($release->id))->toBeNull();
});

// --- Admin Bulk Delete ---

it('admin can bulk delete package releases', function () {
    $release1 = PackageRelease::factory()->create(['version' => '1.0.0', 'release_channel' => 'stable']);
    $release2 = PackageRelease::factory()->create(['version' => '2.0.0', 'release_channel' => 'dev']);

    $this->actingAs($this->admin)
        ->delete(route('packages.bulk-delete'), [
            'ids' => [$release1->id, $release2->id],
        ])
        ->assertRedirect(route('packages.manage'))
        ->assertSessionHas('success');

    expect(PackageRelease::whereIn('id', [$release1->id, $release2->id])->count())->toBe(0);
});

it('bulk delete requires at least one id', function () {
    $this->actingAs($this->admin)
        ->delete(route('packages.bulk-delete'), [
            'ids' => [],
        ])
        ->assertSessionHasErrors('ids');
});

// --- Admin Update Changelog ---

it('admin can update package changelog', function () {
    $release = PackageRelease::factory()->create(['version' => '1.0.0', 'release_channel' => 'stable']);

    $this->actingAs($this->admin)
        ->post(route('packages.update-changelog', $release), [
            'changelog' => 'Updated changelog content',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($release->fresh()->changelog)->toBe('Updated changelog content');
});

it('update changelog validates required field', function () {
    $release = PackageRelease::factory()->create(['version' => '1.0.0', 'release_channel' => 'stable']);

    $this->actingAs($this->admin)
        ->post(route('packages.update-changelog', $release), [
            'changelog' => '',
        ])
        ->assertSessionHasErrors('changelog');
});
