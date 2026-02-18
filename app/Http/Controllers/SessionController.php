<?php

namespace App\Http\Controllers;

use App\Models\ClientSession;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Display a listing of sessions.
     */
    public function index(Request $request)
    {
        $query = ClientSession::query()->with(['account', 'device']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->expired();
            }
        }

        // Search by account username, device name, or session token
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('session_token', 'like', "%{$search}%")
                    ->orWhereHas('account', function ($q) use ($search) {
                        $q->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('device', function ($q) use ($search) {
                        $q->where('device_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sort = $request->get('sort', 'last_heartbeat_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        $sessions = $query->paginate(25)
            ->appends($request->except('page'));

        // Get statistics
        $totalSessions = ClientSession::count();
        $activeSessions = ClientSession::active()->count();
        $expiredSessions = ClientSession::expired()->count();
        $uniqueAccounts = ClientSession::distinct('account_id')->count();
        $uniqueDevices = ClientSession::distinct('device_id')->count();

        return view('sessions.index', [
            'sessions' => $sessions,
            'statusOptions' => [
                '' => 'All Statuses',
                'active' => 'Active',
                'expired' => 'Expired',
            ],
            'currentFilters' => [
                'status' => $request->status,
                'search' => $request->search,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'statistics' => [
                'total' => $totalSessions,
                'active' => $activeSessions,
                'expired' => $expiredSessions,
                'unique_accounts' => $uniqueAccounts,
                'unique_devices' => $uniqueDevices,
            ],
        ]);
    }

    /**
     * Display the specified session.
     */
    public function show(ClientSession $session)
    {
        $session->load(['account', 'device']);

        return view('sessions.show', [
            'session' => $session,
        ]);
    }

    /**
     * Remove the specified session from storage.
     */
    public function destroy(ClientSession $session)
    {
        $accountUsername = $session->account ? $session->account->username : 'Unknown';

        // Delete the session - this will force the client to disconnect
        // on next heartbeat check
        $session->delete();

        return redirect()->route('sessions.index')
            ->with('success', "Session for account '{$accountUsername}' has been terminated. The client will be disconnected on next heartbeat check.");
    }
}
