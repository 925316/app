<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\EventLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    /**
     * Display a listing of event logs.
     */
    public function index(Request $request)
    {
        if (! Auth::user()->hasPrivilege(7)) {
            abort(403, 'Unauthorized access to system logs.');
        }

        $query = EventLog::with(['account', 'license'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        // Filter by event type
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        // Filter by event level
        if ($request->filled('event_level')) {
            $query->where('event_level', $request->event_level);
        }

        // Filter by account
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date.' 23:59:59');
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('account', function ($q) use ($search) {
                        $q->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->paginate(25)
            ->appends($request->except('page'));

        // Get overall statistics (not filtered by search/pagination) - optimized single query
        $stats = EventLog::selectRaw('COUNT(*) as total, SUM(CASE WHEN event_level = 0 THEN 1 ELSE 0 END) as info, SUM(CASE WHEN event_level = 1 THEN 1 ELSE 0 END) as warning, SUM(CASE WHEN event_level = 2 THEN 1 ELSE 0 END) as error')
            ->first();

        $statistics = [
            'total' => $stats->total ?? 0,
            'info' => $stats->info ?? 0,
            'warning' => $stats->warning ?? 0,
            'error' => $stats->error ?? 0,
        ];

        // Get filter options
        $eventTypes = EventLog::distinct()->pluck('event_type');
        $eventTypes = EventLog::select('event_type')->distinct()->pluck('event_type');
        $eventLevels = [
            0 => 'Info',
            1 => 'Warning',
            2 => 'Error',
        ];

        $accounts = Account::query()
            ->select(['id', 'username'])
            ->orderBy('username')
            ->limit(500)
            ->get();

        return view('logs.index', [
            'logs' => $logs,
            'statistics' => $statistics,
            'eventTypes' => $eventTypes,
            'eventLevels' => $eventLevels,
            'accounts' => $accounts,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show the details of a specific log entry.
     */
    public function show(EventLog $log)
    {
        if (! Auth::user()->hasPrivilege(7)) {
            abort(403, 'Unauthorized access to system logs.');
        }

        return view('logs.show', [
            'log' => $log,
        ]);
    }

    /**
     * Clear old log entries.
     */
    public function clear(Request $request)
    {
        if (! Auth::user()->hasPrivilege(7)) {
            abort(403, 'Unauthorized access to system logs.');
        }

        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $days = $request->days;
        $deleted = EventLog::where('created_at', '<=', now()->subDays($days))->delete();

        return back()->with('success', "Deleted {$deleted} log entries older than {$days} days.");
    }
}
