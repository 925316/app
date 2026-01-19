<?php

namespace App\Http\Controllers;

use App\Http\Requests\PackageUploadRequest;
use App\Models\PackageRelease;
use App\Services\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    /**
     * Display a listing of package releases.
     */
    public function index(Request $request)
    {
        $query = PackageRelease::orderBy('version', 'desc');

        // Filter by channel
        if ($request->has('channel')) {
            $query->where('release_channel', $request->channel);
        }

        $releases = $query->paginate(15);

        $stats = PackageService::getPackageStatistics();

        return view('packages.index', [
            'releases' => $releases,
            'stats' => $stats,
            'isAdmin' => Auth::user()->hasPrivilege(5),
        ]);
    }

    /**
     * Show the form for uploading a new package.
     */
    public function upload()
    {
        $this->authorizeAdmin();

        return view('packages.upload');
    }

    /**
     * Store a newly uploaded package.
     */
    public function store(PackageUploadRequest $request)
    {
        $this->authorizeAdmin();

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

        // Check if user has a valid license to download
        $canDownload = $user->hasPrivilege(1); // At least basic privilege

        return view('packages.show', [
            'release' => $release,
            'canDownload' => $canDownload,
            'isAdmin' => $user->hasPrivilege(5),
        ]);
    }

    /**
     * Download a package release.
     */
    public function download(PackageRelease $release)
    {
        $user = Auth::user();

        // Check if user has a valid license to download
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to download packages.');
        }

        // Redirect to the actual download URL
        return redirect()->away($release->download_url);
    }

    /**
     * Show version management page.
     */
    public function versions()
    {
        $this->authorizeAdmin();

        $releases = PackageRelease::orderBy('version', 'desc')->paginate(20);

        return view('packages.version', [
            'releases' => $releases,
        ]);
    }

    /**
     * Delete a package release.
     */
    public function destroy(PackageRelease $release)
    {
        $this->authorizeAdmin();

        // Optionally delete the actual file
        // Storage::delete($release->download_url);

        $release->delete();

        return redirect()->route('packages.versions')
            ->with('success', 'Package release deleted successfully!');
    }

    /**
     * Update package release changelog.
     */
    public function updateChangelog(Request $request, PackageRelease $release)
    {
        $this->authorizeAdmin();

        $request->validate([
            'changelog' => 'required|string|max:65535',
        ]);

        PackageService::updateChangelog($release, $request->changelog);

        return back()->with('success', 'Changelog updated successfully!');
    }

    /**
     * Authorize admin access.
     */
    protected function authorizeAdmin()
    {
        $user = Auth::user();
        if (! $user->hasPrivilege(5)) {
            abort(403, 'Unauthorized action. Admin privileges required.');
        }
    }
}
