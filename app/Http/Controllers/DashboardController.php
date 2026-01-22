<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get statistics based on user type
        if ($user->hasPrivilege(7)) { // Admin
            $stats = StatisticsService::getSystemHealth();
            $recentActivity = StatisticsService::getRecentActivity();

            return view('dashboard.index', [
                'stats' => $stats,
                'recentActivity' => $recentActivity,
                'isAdmin' => true,
            ]);
        } else { // Regular user
            $userStats = StatisticsService::getUserStatistics($user->id);
            $activeLicense = \App\Services\LicenseService::getActiveLicenseForAccount($user->id);
            $boundDevices = $user->devices()->whereNotNull('bound_at')->whereNull('unbound_at')->count();

            // Format usage time
            $usageTimeFormatted = isset($userStats['usage_hours'])
                ? StatisticsService::formatUsageTime($userStats['usage_hours'])
                : '0h';

            return view('dashboard.index', [
                'userStats' => $userStats,
                'activeLicense' => $activeLicense,
                'boundDevices' => $boundDevices,
                'usageTimeFormatted' => $usageTimeFormatted,
                'isAdmin' => false,
            ]);
        }
    }

    /**
     * Show admin panel (partial view)
     */
    public function adminPanel()
    {
        if (! Auth::user()->hasPrivilege(7)) {
            abort(403, 'Unauthorized action. Admin privileges required.');
        }

        $stats = StatisticsService::getSystemHealth();
        $recentActivity = StatisticsService::getRecentActivity();

        return view('dashboard.admin-panel', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * Show user panel (partial view)
     */
    public function userPanel()
    {
        $user = Auth::user();
        $userStats = StatisticsService::getUserStatistics($user->id);
        $activeLicense = \App\Services\LicenseService::getActiveLicenseForAccount($user->id);
        $boundDevices = $user->devices()->whereNotNull('bound_at')->whereNull('unbound_at')->count();

        // Format usage time
        $usageTimeFormatted = isset($userStats['usage_hours'])
            ? StatisticsService::formatUsageTime($userStats['usage_hours'])
            : '0h';

        return view('dashboard.user-panel', [
            'userStats' => $userStats,
            'activeLicense' => $activeLicense,
            'boundDevices' => $boundDevices,
            'usageTimeFormatted' => $usageTimeFormatted,
        ]);
    }
}
