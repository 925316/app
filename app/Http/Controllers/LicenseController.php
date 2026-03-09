<?php

namespace App\Http\Controllers;

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Http\Requests\LicenseRequest;
use App\Models\Account;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LicenseController extends Controller
{
    /**
     * Display a listing of licenses.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->getPrivilegeLevel() >= 7) { // Admin - can see all licenses
            $query = License::query();

            // Filter by status (supports both enum labels and integer values)
            if ($request->filled('status')) {
                $statusValue = array_search(strtolower($request->status), LicenseStatus::options());

                if ($statusValue === false) {
                    $statusValue = (int) $request->status;
                }

                $status = LicenseStatus::tryFrom($statusValue);
                if ($status) {
                    $query->where('status', $status->value);
                }
            }

            // Filter by privilege (supports both labels and integer values)
            if ($request->filled('privilege')) {
                $privilegeValue = array_search(strtolower($request->privilege), LicensePrivilege::options());

                if ($privilegeValue === false) {
                    $privilegeValue = (int) $request->privilege;
                }

                $privilege = LicensePrivilege::tryFrom($privilegeValue);
                if ($privilege) {
                    $query->where('privilege', $privilege->value);
                }
            }

            // Search by key or account
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('key', 'like', "%{$search}%")
                        ->orWhereHas('account', function ($q) use ($search) {
                            $q->where('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            $licenses = $query->with('account')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(25);

            // Get overall statistics (not filtered by search/pagination)
            $statistics = [
                'total' => License::count(),
                'active' => License::active()->count(),
                'expired' => License::expired()->count(),
                'unassigned' => License::whereNull('used_by')->count(),
            ];

            $statusOptions = LicenseStatus::options();
            $typeOptions = [];
            $privilegeOptions = LicensePrivilege::options();

            return view('licenses.index', [
                'licenses' => $licenses,
                'statistics' => $statistics,
                'statusOptions' => $statusOptions,
                'typeOptions' => $typeOptions,
                'privilegeOptions' => $privilegeOptions,
                'isAdmin' => true,
            ]);
        } else { // Regular user - can only see their own licenses
            $licenses = License::where('used_by', $user->id)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(10);

            return view('licenses.index', [
                'licenses' => $licenses,
                'isAdmin' => false,
            ]);
        }
    }

    /**
     * Show the form for creating a new license.
     */
    public function create()
    {
        $accounts = Account::orderBy('username')->get();
        $statusOptions = LicenseStatus::options();
        $privilegeOptions = LicensePrivilege::options();

        return view('licenses.create', [
            'accounts' => $accounts,
            'statusOptions' => $statusOptions,
            'privilegeOptions' => $privilegeOptions,
        ]);
    }

    /**
     * Store a newly created license in storage.
     */
    public function store(LicenseRequest $request)
    {
        $validated = $request->validated();

        $license = LicenseService::createLicense(
            $validated['privilege'],
            $validated['used_by'] ?? null,
            $validated['key'] ?? null,
            $validated['expires_at'],
            $validated['notes'] ?? null
        );

        // Log the event
        event(new \App\Events\LicenseCreated($license));

        return redirect()->route('licenses.show', $license)
            ->with('success', 'License created successfully!');
    }

    /**
     * Display the specified license.
     */
    public function show(License $license)
    {
        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized action.');
        }

        // Regular users can only view their own licenses
        if ($user->getPrivilegeLevel() < 7 && $license->used_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $statusHistory = LicenseService::getLicenseStatusHistory($license);
        $account = $license->account;

        return view('licenses.show', [
            'license' => $license,
            'statusHistory' => $statusHistory,
            'account' => $account,
            'isAdmin' => $user->getPrivilegeLevel() >= 7,
        ]);
    }

    /**
     * Show the form for editing the specified license.
     */
    public function edit(License $license)
    {
        $accounts = Account::orderBy('username')->get();
        $statusOptions = LicenseStatus::options();
        $privilegeOptions = LicensePrivilege::options();

        return view('licenses.edit', [
            'license' => $license,
            'accounts' => $accounts,
            'statusOptions' => $statusOptions,
            'privilegeOptions' => $privilegeOptions,
        ]);
    }

    /**
     * Update the specified license in storage.
     */
    public function update(LicenseRequest $request, License $license)
    {
        $validated = $request->validated();

        // Only allow certain fields to be updated.
        // Status is intentionally excluded — use the dedicated endpoints
        // (suspend, reactivate, revoke, upgrade) to change license status.
        $updateData = [
            'privilege' => $validated['privilege'],
            'expires_at' => $validated['expires_at'],
            'notes' => $validated['notes'],
        ];

        // Only allow used_by to be changed if license is unused
        if ($license->status === LicenseStatus::UNUSED) {
            $updateData['used_by'] = $validated['used_by'];
        }

        $license->update($updateData);

        return redirect()->route('licenses.show', $license)
            ->with('success', 'License updated successfully!');
    }

    /**
     * Remove the specified license from storage.
     */
    public function destroy(License $license)
    {
        $license->delete();

        return redirect()->route('licenses.index')
            ->with('success', 'License deleted successfully!');
    }

    /**
     * Activate a license by key for the current user.
     */
    public function activateByKey(Request $request)
    {
        $request->validate([
            'license_key' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || ! LicenseService::validateLicenseKeyFormat($value)) {
                        $fail('The license key format is invalid.');
                    }
                },
            ],
        ]);

        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized action.');
        }
        $licenseKey = strtoupper($request->license_key);

        try {
            $license = LicenseService::getLicenseByKey($licenseKey);

            if (! $license) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'license_key' => 'Invalid license key. Please check your license key and try again.',
                ]);
            }

            // Check if license can be activated
            if ($license->isExpired()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'license_key' => 'License has expired.',
                ]);
            }

            if (! $license->canActivate()) {
                $reason = 'License cannot be activated.';
                if ($license->status->value === LicenseStatus::REVOKED->value) {
                    $reason = 'License has been revoked.';
                } elseif ($license->status->value === LicenseStatus::SUSPENDED->value) {
                    $reason = 'License has been suspended.';
                } elseif ($license->status->value === LicenseStatus::ACTIVE->value) {
                    $reason = 'License is already active and in use by another account.';
                } elseif ($license->status->value === LicenseStatus::UPGRADED->value) {
                    $reason = 'License has been upgraded and cannot be reactivated.';
                }

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'license_key' => $reason,
                ]);
            }

            // Check license upgrade/activation rules
            $currentPrivilege = $user->getPrivilegeLevel();

            if ($currentPrivilege > 0 && $license->privilege->value <= $currentPrivilege) {
                // User has equal or higher privilege - prevent activation
                $currentLevelName = LicensePrivilege::tryFrom($currentPrivilege)?->getLabel() ?? 'unknown';
                $newLevelName = $license->privilege?->getLabel() ?? 'unknown';

                $errorMessage = $license->privilege->value == $currentPrivilege
                    ? "You already have an active {$currentLevelName} license. You cannot activate another license of the same level."
                    : "You already have an active {$currentLevelName} license. You cannot downgrade to {$newLevelName}.";

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'license_key' => $errorMessage,
                ]);
            }

            // Check if license can be activated based on privilege level
            if (! $license->canActivateByPrivilege()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'license_key' => 'License upgrade cannot be activated alone. It must be used to upgrade a standard license.',
                ]);
            }

            // Allow activation if:
            // 1. User has no active license (currentPrivilege = 0), OR
            // 2. New license has higher privilege than current license

            LicenseService::activateLicense($license, $user, $request->ip());

            // Log the event
            event(new \App\Events\LicenseActivated($license, $user));

            return redirect()->route('licenses.show', $license)
                ->with('success', 'License activated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Activate a license for the current user.
     */
    public function activate(Request $request, License $license)
    {
        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Apply the same privilege-level rules as activateByKey
            $currentPrivilege = $user->getPrivilegeLevel();

            if ($currentPrivilege > 0 && $license->privilege->value <= $currentPrivilege) {
                $currentLevelName = LicensePrivilege::tryFrom($currentPrivilege)?->getLabel() ?? 'unknown';
                $newLevelName = $license->privilege?->getLabel() ?? 'unknown';

                $errorMessage = $license->privilege->value == $currentPrivilege
                    ? "You already have an active {$currentLevelName} license. You cannot activate another license of the same level."
                    : "You already have an active {$currentLevelName} license. You cannot downgrade to {$newLevelName}.";

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'license' => $errorMessage,
                ]);
            }

            if (! $license->canActivateByPrivilege()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'license' => 'License upgrade cannot be activated alone. It must be used to upgrade a standard license.',
                ]);
            }

            LicenseService::activateLicense($license, $user, $request->ip());

            // Log the event
            event(new \App\Events\LicenseActivated($license, $user));

            return redirect()->route('licenses.show', $license)
                ->with('success', 'License activated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Suspend a license.
     */
    public function suspend(Request $request, License $license)
    {
        $request->validate([
            'suspension_reason' => 'nullable|string|max:255',
        ]);

        try {
            LicenseService::suspendLicense($license, $request->suspension_reason);

            // Log the event
            event(new \App\Events\LicenseSuspended($license));

            return redirect()->route('licenses.show', $license)
                ->with('success', 'License suspended successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Reactivate a suspended license.
     */
    public function reactivate(License $license)
    {
        try {
            LicenseService::reactivateLicense($license);

            // Log the event
            event(new \App\Events\LicenseReactivated($license));

            return redirect()->route('licenses.show', $license)
                ->with('success', 'License reactivated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Revoke a license.
     */
    public function revoke(Request $request, License $license)
    {
        $request->validate([
            'revocation_reason' => 'nullable|string|max:255',
        ]);

        try {
            LicenseService::revokeLicense($license, $request->revocation_reason);

            // Log the event
            event(new \App\Events\LicenseRevoked($license));

            return redirect()->route('licenses.show', $license)
                ->with('success', 'License revoked successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Upgrade a license.
     */
    public function upgrade(Request $request, License $license)
    {
        $request->validate([
            'new_privilege' => 'required|integer|min:1|max:7',
            'upgrade_notes' => 'nullable|string|max:255',
        ]);

        try {
            LicenseService::upgradeLicense($license, $request->new_privilege, $request->upgrade_notes);

            // Log the event
            event(new \App\Events\LicenseUpgraded($license));

            return redirect()->route('licenses.show', $license)
                ->with('success', 'License upgraded successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Extend license expiration.
     */
    public function extend(Request $request, License $license)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        try {
            LicenseService::extendLicenseExpiration($license, $request->days);

            return redirect()->route('licenses.show', $license)
                ->with('success', 'License expiration extended successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }
}
