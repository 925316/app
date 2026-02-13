<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Models\Account;
use App\Models\EventLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts.
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = Account::query();

        // Filter by account status (single selection)
        if ($request->filled('status') && $request->status !== '') {
            $status = $request->status;

            match ($status) {
                'active' => $query->active(),
                'suspended' => $query->suspended(),
                'verified' => $query->verified(),
                'unverified' => $query->unverified(),
                '2fa-enabled' => $query->hasTwoFactorEnabled(),
                default => null,
            };
        }

        // Filter by license count
        if ($request->filled('license_count') && $request->license_count !== '') {
            $licenseCount = $request->license_count;

            if ($licenseCount === 'none') {
                $query->whereDoesntHave('licenses');
            } elseif ($licenseCount === 'has') {
                $query->whereHas('licenses');
            }
        }

        // Filter by privilege level
        if ($request->has('privilege') && $request->privilege !== '') {
            $privilege = (int) $request->privilege;
            $query->whereHas('licenses', function ($q) use ($privilege) {
                $q->where('status', \App\Enums\LicenseStatus::ACTIVE->value)
                    ->where('privilege', $privilege)
                    ->where('expires_at', '>', now());
            });
        }

        // Search by username, email, or license key
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('licenses', function ($q) use ($search) {
                        $q->where('key', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortValue = $request->get('sort', 'created_at_desc');

        // Parse sort value (format: field_direction)
        if (str_contains($sortValue, '_')) {
            $parts = explode('_', $sortValue);
            $direction = array_pop($parts);
            $sort = implode('_', $parts);
        } else {
            $sort = $sortValue;
            $direction = 'desc';
        }

        $query->orderBy($sort, $direction);

        $accounts = $query->withCount('licenses', 'devices')
            ->paginate(25)
            ->appends($request->except('page'));

        // Get privilege options for filter
        $privilegeOptions = [
            '' => 'All Privileges',
            1 => 'Standard',
            2 => 'Upgrade',
            3 => 'Ultimate',
            6 => 'Tester',
            7 => 'Staff',
        ];

        return view('accounts.index', [
            'accounts' => $accounts,
            'statusOptions' => [
                '' => 'All Statuses',
                'active' => 'Active',
                'suspended' => 'Suspended',
                'verified' => 'Verified',
                'unverified' => 'Unverified',
                '2fa-enabled' => '2FA Enabled',
            ],
            'privilegeOptions' => $privilegeOptions,
            'currentFilters' => [
                'status' => $request->status,
                'privilege' => $request->privilege,
                'license_count' => $request->license_count,
                'search' => $request->search,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    /**
     * Show the form for creating a new account.
     */
    public function create()
    {
        $this->authorizeAdmin();

        return view('accounts.create');
    }

    /**
     * Store a newly created account in storage.
     */
    public function store(AccountRequest $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validated();

        // Create account
        $account = Account::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => $validated['email_verified'] ? now() : null,
        ]);

        // Log the event
        EventLog::create([
            'event_type' => 'account.created',
            'event_level' => 0, // info
            'account_id' => $account->id,
            'actor_id' => Auth::id(),
            'details' => [
                'username' => $account->username,
                'email' => $account->email,
                'email_verified' => $validated['email_verified'],
            ],
        ]);

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Account created successfully!');
    }

    /**
     * Display the specified account.
     */
    public function show(Account $account)
    {
        $this->authorizeAdmin();

        $account->load([
            'licenses' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'devices' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'sessions' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            },
            'eventLogs' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(20);
            },
        ]);

        $activeLicense = $account->licenses()->where('status', \App\Enums\LicenseStatus::ACTIVE->value)->first();
        $boundDevices = $account->devices()->whereNotNull('bound_at')->whereNull('unbound_at')->get();

        return view('accounts.show', [
            'account' => $account,
            'activeLicense' => $activeLicense,
            'boundDevices' => $boundDevices,
        ]);
    }

    /**
     * Show the form for editing the specified account.
     */
    public function edit(Account $account)
    {
        $this->authorizeAdmin();

        return view('accounts.edit', [
            'account' => $account,
        ]);
    }

    /**
     * Update the specified account in storage.
     */
    public function update(AccountRequest $request, Account $account)
    {
        $this->authorizeAdmin();

        $validated = $request->validated();

        // Update account
        $updateData = [
            'username' => $validated['username'],
            'email' => $validated['email'],
        ];

        if ($validated['password']) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $oldEmail = $account->email;
        $account->update($updateData);

        // Log the event
        EventLog::create([
            'event_type' => 'account.updated',
            'event_level' => 0, // info
            'account_id' => $account->id,
            'actor_id' => Auth::id(),
            'details' => [
                'username' => $account->username,
                'email_changed' => $oldEmail !== $account->email,
                'password_changed' => $validated['password'] !== null,
            ],
        ]);

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Account updated successfully!');
    }

    /**
     * Remove the specified account from storage.
     */
    public function destroy(Account $account)
    {
        $this->authorizeAdmin();

        // Log the event before deletion
        EventLog::create([
            'event_type' => 'account.deleted',
            'event_level' => 2, // error
            'account_id' => $account->id,
            'actor_id' => Auth::id(),
            'details' => [
                'username' => $account->username,
                'email' => $account->email,
            ],
        ]);

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Account deleted successfully!');
    }

    /**
     * Suspend an account.
     */
    public function suspend(Request $request, Account $account)
    {
        $this->authorizeAdmin();

        $request->validate([
            'reason' => 'nullable|string|max:255',
            'duration' => 'nullable|integer|min:1|max:365',
        ]);

        $reason = $request->reason;
        $duration = $request->duration;

        if ($duration) {
            $suspendedUntil = now()->addDays($duration);
        } else {
            $suspendedUntil = null;
        }

        $account->suspend($reason, $suspendedUntil);

        // Log the event
        EventLog::create([
            'event_type' => 'account.suspended',
            'event_level' => 1, // warn
            'account_id' => $account->id,
            'actor_id' => Auth::id(),
            'details' => [
                'reason' => $reason,
                'duration_days' => $duration,
                'suspended_until' => $suspendedUntil,
            ],
        ]);

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Account suspended successfully!');
    }

    /**
     * Unsuspend an account.
     */
    public function unsuspend(Account $account)
    {
        $this->authorizeAdmin();

        $account->unsuspend();

        // Log the event
        EventLog::create([
            'event_type' => 'account.unsuspended',
            'event_level' => 0, // info
            'account_id' => $account->id,
            'actor_id' => Auth::id(),
            'details' => [
                'username' => $account->username,
            ],
        ]);

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Account unsuspended successfully!');
    }

    /**
     * Reset HWID for an account.
     */
    public function resetHwid(Account $account)
    {
        $this->authorizeAdmin();

        if (! $account->canResetHwid()) {
            return back()->withErrors(['hwid_reset' => 'HWID can only be reset once every 72 hours.']);
        }

        // Reset HWID for all devices
        $account->devices()->update([
            'hwid_hash' => null,
            'bound_at' => null,
            'unbound_at' => now(),
        ]);

        $account->incrementHwidResetCount();

        // Log the event
        EventLog::create([
            'event_type' => 'account.hwid_reset',
            'event_level' => 1, // warn
            'account_id' => $account->id,
            'actor_id' => Auth::id(),
            'details' => [
                'username' => $account->username,
                'reset_count' => $account->hwid_reset_count,
            ],
        ]);

        return redirect()->route('accounts.show', $account)
            ->with('success', 'HWID reset successfully!');
    }

    /**
     * Verify email for an account.
     */
    public function verifyEmail(Account $account)
    {
        $this->authorizeAdmin();

        if ($account->email_verified_at) {
            return back()->withErrors(['email_verification' => 'Account email is already verified.']);
        }

        $account->email_verified_at = now();
        $account->save();

        // Log the event
        EventLog::create([
            'event_type' => 'account.email_verified',
            'event_level' => 0, // info
            'account_id' => $account->id,
            'actor_id' => Auth::id(),
            'details' => [
                'username' => $account->username,
                'email' => $account->email,
            ],
        ]);

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Email verified successfully!');
    }

    /**
     * Authorize admin access.
     */
    protected function authorizeAdmin()
    {
        $user = Auth::user();
        if ($user->getPrivilegeLevel() < 7) {
            abort(403, 'Unauthorized action. Admin privileges required.');
        }
    }
}
