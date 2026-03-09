<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeviceRequest;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\EventLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    /**
     * Display a listing of devices for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized access.');
        }

        // Check if user has at least standard privilege to access devices
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to access devices.');
        }

        $isAdmin = $user->hasPrivilege(7); // Admin privilege level

        if ($isAdmin) {
            return $this->adminIndex($request);
        }

        $devices = $user->devices()
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $currentDevice = $user->devices()
            ->whereNotNull('bound_at')
            ->whereNull('unbound_at')
            ->first();

        return view('devices.index', [
            'devices' => $devices,
            'currentDevice' => $currentDevice,
        ]);
    }

    /**
     * Display a listing of all devices for admin users.
     */
    public function adminIndex(Request $request)
    {
        $query = AccountDevice::query()
            ->with('account')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        $this->applyDeviceFilters($query, $request);

        $devices = $query->paginate(10);

        // Calculate statistics
        $totalDevices = AccountDevice::count();
        $boundDevices = AccountDevice::whereNotNull('bound_at')->whereNull('unbound_at')->count();
        $activeDevices = AccountDevice::where('last_seen_at', '>=', now()->subDays(30))->count();
        $unboundDevices = AccountDevice::whereNotNull('unbound_at')->count();

        return view('devices.admin-index', [
            'devices' => $devices,
            'totalDevices' => $totalDevices,
            'boundDevices' => $boundDevices,
            'activeDevices' => $activeDevices,
            'unboundDevices' => $unboundDevices,
        ]);
    }

    /**
     * Show the device management page.
     */
    public function manage()
    {
        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized access.');
        }

        // Check if user has at least standard privilege to access device management
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to access device management.');
        }

        $currentDevice = $user->devices()
            ->whereNotNull('bound_at')
            ->whereNull('unbound_at')
            ->first();

        $canResetHwid = $user->canResetHwid();
        $hwidResetCount = $user->hwid_reset_count;
        $hwidLastReset = $user->hwid_last_reset_at;

        return view('devices.manage', [
            'currentDevice' => $currentDevice,
            'canResetHwid' => $canResetHwid,
            'hwidResetCount' => $hwidResetCount,
            'hwidLastReset' => $hwidLastReset,
        ]);
    }

    /**
     * Bind a device to the user's account.
     */
    public function bind(DeviceRequest $request)
    {
        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized access.');
        }

        // Check if user has at least standard privilege to bind devices
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to bind devices.');
        }

        // DeviceRequest already validates that the user has no bound device.
        // Wrap the operation in a transaction to prevent race conditions.
        $currentTime = now();
        $device = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $request, $currentTime) {
            // Re-check inside transaction with a lock to prevent concurrent binds
            $alreadyBound = AccountDevice::where('account_id', $user->id)
                ->whereNotNull('bound_at')
                ->whereNull('unbound_at')
                ->lockForUpdate()
                ->exists();

            if ($alreadyBound) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'hwid' => 'You can only bind one device at a time. Please unbind your current device first.',
                ]);
            }

            // Create or update device record — only set first_seen_at on initial creation
            $device = AccountDevice::firstOrCreate(
                [
                    'account_id' => $user->id,
                    'hwid_hash' => hash('sha256', (string) $request->hwid),
                ],
                [
                    'ip_address' => $request->ip_address,
                    'country_code' => $request->country_code,
                    'first_seen_at' => $currentTime,
                    'last_seen_at' => $currentTime,
                    'bound_at' => $currentTime,
                    'unbound_at' => null,
                ]
            );

            if (! $device->wasRecentlyCreated) {
                $device->update([
                    'ip_address' => $request->ip_address,
                    'country_code' => $request->country_code,
                    'last_seen_at' => $currentTime,
                    'bound_at' => $currentTime,
                    'unbound_at' => null,
                ]);
            }

            return $device;
        });

        // Log the event
        EventLog::create([
            'event_type' => 'device.bound',
            'event_level' => 0,
            'account_id' => $user->id,
            'ip_address' => $request->ip_address,
            'actor_id' => $user->id,
            'details' => [
                'hwid_hash' => $device->hwid_hash,
                'device_id' => $device->id,
            ],
        ]);

        return redirect()->route('devices.manage')
            ->with('success', 'Device bound successfully!');
    }

    /**
     * Unbind the current device.
     */
    public function unbind(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized access.');
        }

        // Check if user has at least standard privilege to unbind devices
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to unbind devices.');
        }

        $currentTime = now();
        $device = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $request, $currentTime) {
            $lockedDevice = AccountDevice::where('account_id', $user->id)
                ->whereNotNull('bound_at')
                ->whereNull('unbound_at')
                ->lockForUpdate()
                ->first();

            if (! $lockedDevice) {
                return null;
            }

            $lockedDevice->unbound_at = $currentTime;
            $lockedDevice->save();

            EventLog::create([
                'event_type' => 'device.unbound',
                'event_level' => 0,
                'account_id' => $user->id,
                'ip_address' => $request->ip(),
                'actor_id' => $user->id,
                'details' => [
                    'hwid_hash' => $lockedDevice->hwid_hash,
                    'device_id' => $lockedDevice->id,
                ],
            ]);

            return $lockedDevice;
        });

        if (! $device) {
            return back()->withErrors(['device' => 'No device is currently bound to your account.']);
        }

        return redirect()->route('devices.manage')
            ->with('success', 'Device unbound successfully!');
    }

    /**
     * Reset HWID (for testing purposes, with limits)
     */
    public function resetHwid(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof Account) {
            abort(403, 'Unauthorized access.');
        }

        // Check if user has at least standard privilege to reset HWID
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to reset HWID.');
        }

        if (! $user->canResetHwid()) {
            return back()->withErrors([
                'hwid_reset' => 'You can only reset HWID every 72 hours. Please wait until '.
                    $user->hwid_last_reset_at->addHours(72)->format('Y-m-d H:i:s'),
            ]);
        }

        $currentTime = now();

        // Unbind currently bound devices; preserve hwid_hash for audit history
        $user->devices()
            ->whereNotNull('bound_at')
            ->whereNull('unbound_at')
            ->update(['unbound_at' => $currentTime]);

        $user->incrementHwidResetCount();

        // Log the event
        EventLog::create([
            'event_type' => 'device.hwid_changed',
            'event_level' => 1, // Warning level
            'account_id' => $user->id,
            'ip_address' => $request->ip(),
            'actor_id' => $user->id,
            'details' => [
                'reset_count' => $user->hwid_reset_count,
                'last_reset_at' => $user->hwid_last_reset_at,
            ],
        ]);

        return redirect()->route('devices.manage')
            ->with('success', 'HWID reset successfully. You can now bind a new device.');
    }

    /**
     * Admin: Unbind a device for any user
     */
    public function adminUnbind(AccountDevice $device)
    {
        if (! $device->isBound()) {
            return back()->withErrors(['device' => 'This device is not currently bound.']);
        }

        $device->unbound_at = now();
        $device->save();

        // Log the event
        EventLog::create([
            'event_type' => 'device.admin_unbound',
            'event_level' => 1, // Warning level
            'account_id' => $device->account_id,
            'actor_id' => Auth::id(),
            'details' => [
                'hwid_hash' => $device->hwid_hash,
                'device_id' => $device->id,
                'account_id' => $device->account_id,
            ],
        ]);

        return redirect()->route('devices.index')
            ->with('success', 'Device unbound successfully!');
    }

    /**
     * Admin: Reset HWID for any user
     */
    public function adminResetHwid(Account $account)
    {
        if (! $account->canResetHwid()) {
            return back()->withErrors([
                'hwid_reset' => 'HWID can only be reset once every 72 hours. Last reset: '.
                    $account->hwid_last_reset_at->format('Y-m-d H:i:s'),
            ]);
        }

        $currentTime = now();

        // Unbind currently bound devices; preserve hwid_hash for audit history
        $account->devices()
            ->whereNotNull('bound_at')
            ->whereNull('unbound_at')
            ->update(['unbound_at' => $currentTime]);

        $account->incrementHwidResetCount();

        // Log the event
        EventLog::create([
            'event_type' => 'device.admin_hwid_reset',
            'event_level' => 1, // Warning level
            'account_id' => $account->id,
            'actor_id' => Auth::id(),
            'details' => [
                'username' => $account->username,
                'reset_count' => $account->hwid_reset_count,
            ],
        ]);

        return redirect()->route('devices.index')
            ->with('success', 'HWID reset successfully for user: '.$account->username);
    }

    /**
     * Admin: Bulk unbind devices
     */
    public function bulkUnbind(Request $request)
    {
        $request->validate([
            'device_ids' => 'required|array|min:1',
            'device_ids.*' => 'exists:account_devices,id',
        ]);

        $deviceIds = $request->input('device_ids');
        $devices = AccountDevice::whereIn('id', $deviceIds)
            ->whereNotNull('bound_at')
            ->whereNull('unbound_at')
            ->get();

        if ($devices->isEmpty()) {
            return back()->withErrors(['bulk_action' => 'No bound devices found to unbind.']);
        }

        $currentTime = now();
        $unboundCount = 0;
        foreach ($devices as $device) {
            $device->unbound_at = $currentTime;
            $device->save();
            $unboundCount++;

            // Log each unbind event
            EventLog::create([
                'event_type' => 'device.admin_unbound',
                'event_level' => 1, // Warning level
                'account_id' => $device->account_id,
                'actor_id' => Auth::id(),
                'details' => [
                    'hwid_hash' => $device->hwid_hash,
                    'device_id' => $device->id,
                    'account_id' => $device->account_id,
                    'bulk_action' => true,
                ],
            ]);
        }

        return redirect()->route('devices.index')
            ->with('success', 'Successfully unbound '.$unboundCount.' devices.');
    }

    /**
     * Admin: Bulk reset HWID for accounts
     */
    public function bulkResetHwid(Request $request)
    {
        $request->validate([
            'device_ids' => 'required|array|min:1',
            'device_ids.*' => 'exists:account_devices,id',
        ]);

        $deviceIds = $request->input('device_ids');
        $accounts = AccountDevice::whereIn('id', $deviceIds)
            ->with('account')
            ->get()
            ->pluck('account')
            ->unique('id');

        $currentTime = now();
        $resetCount = 0;
        foreach ($accounts as $account) {
            if ($account->canResetHwid()) {
                // Unbind currently bound devices; preserve hwid_hash for audit history
                $account->devices()
                    ->whereNotNull('bound_at')
                    ->whereNull('unbound_at')
                    ->update(['unbound_at' => $currentTime]);

                $account->incrementHwidResetCount();
                $resetCount++;

                // Log the reset event
                EventLog::create([
                    'event_type' => 'device.admin_hwid_reset',
                    'event_level' => 1, // Warning level
                    'account_id' => $account->id,
                    'actor_id' => Auth::id(),
                    'details' => [
                        'username' => $account->username,
                        'reset_count' => $account->hwid_reset_count,
                        'bulk_action' => true,
                    ],
                ]);
            }
        }

        if ($resetCount === 0) {
            return back()->withErrors(['bulk_action' => 'No accounts eligible for HWID reset (72-hour cooldown).']);
        }

        return redirect()->route('devices.index')
            ->with('success', 'Successfully reset HWID for '.$resetCount.' accounts.');
    }

    /**
     * Admin: Export device data
     */
    public function export(Request $request)
    {
        $query = AccountDevice::query()
            ->with('account')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        $this->applyDeviceFilters($query, $request);

        $devices = $query->get();

        // Generate CSV
        $filename = 'devices_export_'.now()->format('Y-m-d_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($devices) {
            $file = fopen('php://output', 'w');

            // Add CSV header
            fputcsv($file, [
                'ID',
                'Account ID',
                'Username',
                'Email',
                'HWID Hash',
                'IP Address',
                'Country Code',
                'Status',
                'Bound At',
                'Unbound At',
                'First Seen',
                'Last Seen',
                'Account Status',
                'HWID Reset Count',
                'Last HWID Reset',
            ]);

            // Add data rows
            foreach ($devices as $device) {
                fputcsv($file, [
                    $device->id,
                    $device->account_id,
                    $device->account->username,
                    $device->account->email,
                    $device->hwid_hash,
                    $device->ip_address,
                    $device->country_code ?? 'Unknown',
                    $device->isBound() ? 'Bound' : 'Unbound',
                    $device->bound_at?->format('Y-m-d H:i:s') ?? 'Never',
                    $device->unbound_at?->format('Y-m-d H:i:s') ?? 'Never',
                    $device->first_seen_at->format('Y-m-d H:i:s'),
                    $device->last_seen_at->format('Y-m-d H:i:s'),
                    $device->account->isSuspended() ? 'Suspended' : 'Active',
                    $device->account->hwid_reset_count,
                    $device->account->hwid_last_reset_at?->format('Y-m-d H:i:s') ?? 'Never',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Apply common device filters to a query builder instance.
     */
    private function applyDeviceFilters(Builder $query, Request $request): Builder
    {
        $currentTime = now();

        if ($request->filled('status')) {
            $status = $request->input('status');
            switch ($status) {
                case 'bound':
                    $query->whereNotNull('bound_at')->whereNull('unbound_at');
                    break;
                case 'unbound':
                    $query->whereNotNull('unbound_at');
                    break;
                case 'active':
                    $query->where('last_seen_at', '>=', $currentTime->copy()->subDays(30));
                    break;
            }
        }

        if ($request->filled('date_range')) {
            $dateRange = $request->input('date_range');
            switch ($dateRange) {
                case '24h':
                    $query->where('created_at', '>=', $currentTime->copy()->subHours(24));
                    break;
                case '7d':
                    $query->where('created_at', '>=', $currentTime->copy()->subDays(7));
                    break;
                case '30d':
                    $query->where('created_at', '>=', $currentTime->copy()->subDays(30));
                    break;
                case '90d':
                    $query->where('created_at', '>=', $currentTime->copy()->subDays(90));
                    break;
            }
        }

        if ($request->filled('country_code')) {
            $query->where('country_code', strtoupper($request->input('country_code')));
        }

        if ($request->filled('min_reset_count')) {
            $minResetCount = (int) $request->input('min_reset_count');
            $query->whereHas('account', function ($accountQuery) use ($minResetCount) {
                $accountQuery->where('hwid_reset_count', '>=', $minResetCount);
            });
        }

        if ($request->filled('account_status')) {
            $accountStatus = $request->input('account_status');
            switch ($accountStatus) {
                case 'active':
                    $query->whereHas('account', function ($accountQuery) use ($currentTime) {
                        $accountQuery->where('is_suspended', false)
                            ->orWhere(function ($q) use ($currentTime) {
                                $q->where('is_suspended', true)
                                    ->where('suspended_until', '<', $currentTime);
                            });
                    });
                    break;
                case 'suspended':
                    $query->whereHas('account', function ($accountQuery) use ($currentTime) {
                        $accountQuery->where('is_suspended', true)
                            ->where(function ($q) use ($currentTime) {
                                $q->whereNull('suspended_until')
                                    ->orWhere('suspended_until', '>', $currentTime);
                            });
                    });
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('hwid_hash', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('account', function ($accountQuery) use ($search) {
                        $accountQuery->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }
}
