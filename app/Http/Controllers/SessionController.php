<?php

namespace App\Http\Controllers;

use App\Models\ClientSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    /**
     * Display a listing of sessions.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user has at least standard privilege
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to access sessions.');
        }

        $isAdmin = $user->hasPrivilege(7);

        if ($isAdmin) {
            return $this->adminIndex($request);
        }

        // Regular user - only see their own sessions
        $query = ClientSession::query()
            ->with(['device'])
            ->where('account_id', $user->id);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->expired();
            }
        }

        // Search by device name or session token
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('session_token', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'last_heartbeat_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        $sessions = $query->paginate(25)
            ->appends($request->except('page'));

        return view('sessions.index', [
            'sessions' => $sessions,
            'isAdmin' => false,
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
            'statistics' => null,
        ]);
    }

    /**
     * Display a listing of all sessions for admin.
     */
    private function adminIndex(Request $request)
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
        $uniqueAccounts = ClientSession::distinct('account_id')->count('account_id');
        $uniqueDevices = ClientSession::distinct('device_id')->count('device_id');
        $uniqueDevices = ClientSession::distinct('device_id')->count();

        return view('sessions.index', [
            'sessions' => $sessions,
            'isAdmin' => true,
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
        $user = Auth::user();

        // Check if user has at least standard privilege
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to access sessions.');
        }

        $isAdmin = $user->hasPrivilege(7);

        // Regular users can only view their own sessions
        if (! $isAdmin && $session->account_id !== $user->id) {
            abort(403, 'Unauthorized access to this session.');
        }

        $session->load(['account', 'device']);

        return view('sessions.show', [
            'session' => $session,
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Remove the specified session from storage.
     */
    public function destroy(ClientSession $session)
    {
        $user = Auth::user();

        // Check if user has at least standard privilege
        if (! $user->hasPrivilege(1)) {
            abort(403, 'You need a valid license to access sessions.');
        }

        $isAdmin = $user->hasPrivilege(7);

        // Regular users can only delete their own sessions
        if (! $isAdmin && $session->account_id !== $user->id) {
            abort(403, 'Unauthorized access to this session.');
        }

        $accountUsername = $session->account ? $session->account->username : 'Unknown';

        // Delete the session - this will force the client to disconnect
        // on next heartbeat check
        $session->delete();

        return redirect()->route('sessions.index')
            ->with('success', "Session for account '{$accountUsername}' has been terminated. The client will be disconnected on next heartbeat check.");
    }
}
