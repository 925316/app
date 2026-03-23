<?php

use App\Models\Account;
use App\Models\PackageRelease;

beforeEach(function () {
    $this->admin = createAdmin();
    $this->userWithLicense = createUserWithLicense(1);
    $this->userWithLicense->forceFill(['email_verified_at' => now()])->save();

    $this->userWithoutLicense = Account::factory()->verified()->create();
});

// --- Package Index ---

it('user with license can view package list', function () {
    $this->actingAs($this->userWithLicense)
        ->get(route('packages.index'))
        ->assertSuccessful()
        ->assertViewIs('packages.index');
});

it('guest is redirected from package routes', function () {
    $release = PackageRelease::factory()->create([
        'version' => '1.0.0',
        'release_channel' => 'stable',
    ]);

    $this->get(route('packages.index'))->assertRedirect(route('login'));
    $this->get(route('packages.show', $release))->assertRedirect(route('login'));
    $this->get(route('packages.download', $release))->assertRedirect(route('login'));
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

it('package show returns not found for missing release model', function () {
    $this->actingAs($this->userWithLicense)
        ->get(route('packages.show', 999999))
        ->assertNotFound();
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

it('package download returns not found when stored url is unsafe', function () {
    $release = PackageRelease::factory()->create([
        'version' => '6.0.0',
        'release_channel' => 'stable',
        'download_url' => 'http://example.com/download/package-6.0.0.zip',
    ]);

    $this->actingAs($this->userWithLicense)
        ->get(route('packages.download', $release))
        ->assertNotFound();
});

it('package download returns not found for missing release model', function () {
    $this->actingAs($this->userWithLicense)
        ->get(route('packages.download', 999999))
        ->assertNotFound();
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

it('guest is redirected from package admin pages', function () {
    $this->get(route('packages.upload'))
        ->assertRedirect(route('login'));

    $this->get(route('packages.manage'))
        ->assertRedirect(route('login'));
});

it('guest is redirected from package admin mutation routes', function () {
    $release = PackageRelease::factory()->create(['version' => '7.7.7', 'release_channel' => 'stable']);

    $this->post(route('packages.store'), [
        'version' => '7.7.8',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/package-7.7.8.zip',
    ])->assertRedirect(route('login'));

    $this->post(route('packages.update-changelog', $release), [
        'changelog' => 'unauthorized',
    ])->assertRedirect(route('login'));

    $this->delete(route('packages.bulk-delete'), [
        'ids' => [$release->id],
    ])->assertRedirect(route('login'));

    $this->delete(route('packages.destroy', $release))
        ->assertRedirect(route('login'));
});

it('non-admin cannot access package upload form', function () {
    $this->actingAs($this->userWithLicense)
        ->get(route('packages.upload'))
        ->assertForbidden();
});

it('non-admin cannot store package upload', function () {
    $this->actingAs($this->userWithLicense)
        ->post(route('packages.store'), [
            'version' => '9.9.9',
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/download/package-9.9.9.zip',
        ])
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

it('upload rejects non-https download url', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '2.0.1',
            'release_channel' => 'stable',
            'download_url' => 'http://example.com/package.zip',
        ])
        ->assertSessionHasErrors('download_url');
});

it('upload rejects localhost download url', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '2.0.2',
            'release_channel' => 'stable',
            'download_url' => 'https://localhost/package.zip',
        ])
        ->assertSessionHasErrors('download_url');
});

it('upload rejects credential-bearing download url', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '2.0.3',
            'release_channel' => 'stable',
            'download_url' => 'https://user:pass@example.com/package.zip',
        ])
        ->assertSessionHasErrors('download_url');
});

it('upload rejects private network download url', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '2.0.31',
            'release_channel' => 'stable',
            'download_url' => 'https://192.168.1.10/package.zip',
        ])
        ->assertSessionHasErrors('download_url');
});

it('upload validates virus detection url format', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '2.0.4',
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/package.zip',
            'virus_detection_url' => 'not-a-url',
        ])
        ->assertSessionHasErrors('virus_detection_url');
});

it('upload rejects non-https virus detection url', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '2.0.5',
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/package.zip',
            'virus_detection_url' => 'http://example.com/scan',
        ])
        ->assertSessionHasErrors('virus_detection_url');
});

it('upload rejects localhost virus detection url', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '2.0.6',
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/package.zip',
            'virus_detection_url' => 'https://localhost/scan',
        ])
        ->assertSessionHasErrors('virus_detection_url');
});

it('upload rejects private network virus detection url', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '2.0.61',
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/package.zip',
            'virus_detection_url' => 'https://10.0.0.9/scan',
        ])
        ->assertSessionHasErrors('virus_detection_url');
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

it('non-admin cannot update package changelog', function () {
    $release = PackageRelease::factory()->create(['version' => '1.0.0', 'release_channel' => 'stable']);

    $this->actingAs($this->userWithLicense)
        ->post(route('packages.update-changelog', $release), [
            'changelog' => 'blocked',
        ])
        ->assertForbidden();
});

it('non-admin cannot bulk delete package releases', function () {
    $release = PackageRelease::factory()->create(['version' => '1.0.0', 'release_channel' => 'stable']);

    $this->actingAs($this->userWithLicense)
        ->delete(route('packages.bulk-delete'), [
            'ids' => [$release->id],
        ])
        ->assertForbidden();
});

it('non-admin cannot delete package release', function () {
    $release = PackageRelease::factory()->create(['version' => '1.0.0', 'release_channel' => 'stable']);

    $this->actingAs($this->userWithLicense)
        ->delete(route('packages.destroy', $release))
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

it('update changelog returns not found for missing release model', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.update-changelog', 999999), [
            'changelog' => 'x',
        ])
        ->assertNotFound();
});

it('destroy package returns not found for missing release model', function () {
    $this->actingAs($this->admin)
        ->delete(route('packages.destroy', 999999))
        ->assertNotFound();
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

it('bulk delete validates ids must be an array', function () {
    $this->actingAs($this->admin)
        ->delete(route('packages.bulk-delete'), [
            'ids' => 'not-array',
        ])
        ->assertSessionHasErrors('ids');
});

it('bulk delete validates each id exists', function () {
    $this->actingAs($this->admin)
        ->delete(route('packages.bulk-delete'), [
            'ids' => [999999],
        ])
        ->assertSessionHasErrors('ids.0');
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

it('update changelog validates max length', function () {
    $release = PackageRelease::factory()->create(['version' => '1.0.0', 'release_channel' => 'stable']);

    $this->actingAs($this->admin)
        ->post(route('packages.update-changelog', $release), [
            'changelog' => str_repeat('a', 65536),
        ])
        ->assertSessionHasErrors('changelog');
});

it('upload validates version max length', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => str_repeat('1', 51),
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/package.zip',
        ])
        ->assertSessionHasErrors('version');
});

it('upload validates download url max length', function () {
    $longUrl = 'https://example.com/'.str_repeat('a', 240);

    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '4.0.0',
            'release_channel' => 'stable',
            'download_url' => $longUrl,
        ])
        ->assertSessionHasErrors('download_url');
});

it('upload validates virus detection url max length', function () {
    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => '4.1.0',
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/package.zip',
            'virus_detection_url' => str_repeat('x', 2001),
        ])
        ->assertSessionHasErrors('virus_detection_url');
});

it('upload trims version before uniqueness validation', function () {
    PackageRelease::factory()->create(['version' => '1.0.0', 'release_channel' => 'stable']);

    $this->actingAs($this->admin)
        ->post(route('packages.store'), [
            'version' => ' 1.0.0 ',
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/package.zip',
        ])
        ->assertSessionHasErrors('version');
});
