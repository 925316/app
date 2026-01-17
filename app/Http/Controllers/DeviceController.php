<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeviceRequest;
use App\Models\AccountDevice;
use App\Models\EventLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    /**
     * Display a listing of devices for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();

        $devices = $user->devices()
            ->orderBy('created_at', 'desc')
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
     * Show the device management page.
     */
    public function manage()
    {
        $user = Auth::user();

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

        // Check if user already has a bound device
        if ($user->getBoundDeviceCount() >= 1) {
            return back()->withErrors(['hwid_hash' => 'You can only bind one device at a time. Please unbind your current device first.']);
        }

        // Create or update device record
        $device = AccountDevice::updateOrCreate(
            [
                'account_id' => $user->id,
                'hwid_hash' => $request->hwid_hash,
            ],
            [
                'ip_address' => $request->ip_address,
                'country_code' => $request->country_code,
                'first_seen_at' => now(),
                'last_seen_at' => now(),
                'bound_at' => now(),
                'unbound_at' => null,
            ]
        );

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

        $device = $user->devices()
            ->whereNotNull('bound_at')
            ->whereNull('unbound_at')
            ->first();

        if (! $device) {
            return back()->withErrors(['device' => 'No device is currently bound to your account.']);
        }

        $device->unbound_at = now();
        $device->save();

        // Log the event
        EventLog::create([
            'event_type' => 'device.unbound',
            'event_level' => 0,
            'account_id' => $user->id,
            'ip_address' => $request->ip(),
            'actor_id' => $user->id,
            'details' => [
                'hwid_hash' => $device->hwid_hash,
                'device_id' => $device->id,
            ],
        ]);

        return redirect()->route('devices.manage')
            ->with('success', 'Device unbound successfully!');
    }

    /**
     * Reset HWID (for testing purposes, with limits)
     */
    public function resetHwid(Request $request)
    {
        $user = Auth::user();

        if (! $user->canResetHwid()) {
            return back()->withErrors([
                'hwid_reset' => 'You can only reset HWID every 72 hours. Please wait until '.
                    $user->hwid_last_reset_at->addHours(72)->format('Y-m-d H:i:s'),
            ]);
        }

        // Increment reset count
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
            ->with('success', 'HWID reset count incremented. You can now bind a new device.');
    }
}
