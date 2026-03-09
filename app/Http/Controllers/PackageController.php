<?php

namespace App\Http\Controllers;

use App\Http\Requests\PackageUploadRequest;
use App\Models\Account;
use App\Models\PackageRelease;
use App\Services\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    /**
     * Display a listing of package releases.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized access.');
        }

        // Check if user has at least standard privilege to access packages
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to access packages.');
        }

        $query = PackageRelease::orderBy('version', 'desc')
            ->orderBy('id', 'desc');

        // Filter by channel
        if ($request->filled('channel')) {
            $query->where('release_channel', $request->channel);
        }

        $releases = $query->paginate(15);

        $stats = PackageService::getPackageStatistics();

        return view('packages.index', [
            'releases' => $releases,
            'stats' => $stats,
            'isAdmin' => $user->hasPrivilege(7),
            'canDownload' => $user->hasPrivilege(1),
        ]);
    }

    /**
     * Show the form for uploading a new package.
     */
    public function upload()
    {
        return view('packages.upload');
    }

    /**
     * Store a newly uploaded package.
     */
    public function store(PackageUploadRequest $request)
    {
        $validated = $request->validated();

        $release = PackageService::uploadPackage(
            $validated['version'],
            $validated['release_channel'],
            $validated['download_url'],
            $validated['changelog'] ?? null,
            $validated['virus_detection_url'] ?? null
        );

        // Log the event
        event(new \App\Events\PackageUploaded($release));

        return redirect()->route('packages.index')
            ->with('success', 'Package added successfully!');
    }

    /**
     * Display the specified package release.
     */
    public function show(PackageRelease $release)
    {
        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized access.');
        }

        // Check if user has at least standard privilege to view packages
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to view packages.');
        }

        // Check if user has a valid license to download
        $canDownload = $user->hasPrivilege(1); // At least basic privilege

        return view('packages.show', [
            'release' => $release,
            'canDownload' => $canDownload,
            'isAdmin' => $user->hasPrivilege(7),
        ]);
    }

    /**
     * Download a package release.
     */
    public function download(PackageRelease $release)
    {
        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized access.');
        }

        // Check if user has a valid license to download
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to download packages.');
        }

        // Redirect to the actual download URL
        return redirect()->away($release->download_url);
    }

    /**
     * Show package management page with inline actions.
     */
    public function manage()
    {
        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized access.');
        }

        // Check if user has at least standard privilege to access package management
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to access package management.');
        }

        $isAdmin = $user->hasPrivilege(7);

        $query = PackageRelease::orderBy('version', 'desc')
            ->orderBy('id', 'desc');

        // Filter by channel
        if (request()->filled('channel')) {
            $query->where('release_channel', request()->channel);
        }

        $releases = $query->paginate(20);

        $stats = PackageService::getPackageStatistics();

        return view('packages.manage', [
            'releases' => $releases,
            'stats' => $stats,
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Delete a package release.
     */
    public function destroy(PackageRelease $release)
    {
        $release->delete();

        return redirect()->route('packages.manage')
            ->with('success', 'Package release deleted successfully!');
    }

    /**
     * Bulk delete package releases.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:package_releases,id',
        ]);

        $deletedCount = PackageRelease::whereIn('id', $request->ids)->delete();

        return redirect()->route('packages.manage')
            ->with('success', "Successfully deleted {$deletedCount} package(s)!");
    }

    /**
     * Update package release changelog.
     */
    public function updateChangelog(Request $request, PackageRelease $release)
    {
        $request->validate([
            'changelog' => 'required|string|max:65535',
        ]);

        PackageService::updateChangelog($release, $request->changelog);

        return back()->with('success', 'Changelog updated successfully!');
    }
}
