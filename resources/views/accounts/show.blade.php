<x-app-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Account Details') }}
        </h2>
    </x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ $account->username }}</h1>
                        <p class="text-gray-500 dark:text-gray-400">Account ID: {{ $account->id }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('accounts.edit', $account) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit Account
                        </a>
                        <a href="{{ route('accounts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Back to Accounts
                        </a>
                    </div>
                </div>

                <!-- Account Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Account Information</h3>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Username</span>
                                <p class="font-medium">{{ $account->username }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Email</span>
                                <p class="font-medium">{{ $account->email }}</p>
                                @if($account->email_verified_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 mt-1">
                                        Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 mt-1">
                                        Unverified
                                    </span>
                                @endif
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                                @if($account->isCurrentlySuspended)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                        Suspended
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        Active
                                    </span>
                                @endif
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Privilege Level</span>
                                <p class="font-medium">{{ $account->getPrivilegeLevel() ? ucfirst(strtolower(\App\Enums\LicensePrivilege::tryFrom($account->getPrivilegeLevel())?->getLabel() ?? 'Unknown')) : 'None' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Login Information</h3>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Last Login</span>
                                <p class="font-medium">
                                    @if($account->last_login_at)
                                        {{ $account->last_login_at->diffForHumans() }}
                                        <br>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $account->last_login_at->format('Y-m-d H:i:s') }}</span>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">Never</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Last IP Address</span>
                                <p class="font-medium">{{ $account->last_ip_address ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Registration Date</span>
                                <p class="font-medium">{{ $account->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Device Information</h3>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Total Devices</span>
                                <p class="font-medium">{{ $account->devices_count }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Bound Devices</span>
                                <p class="font-medium">{{ $boundDevices->count() }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">HWID Resets</span>
                                <p class="font-medium">{{ $account->hwid_reset_count }}</p>
                                @if($account->hwid_last_reset_at)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Last reset: {{ $account->hwid_last_reset_at->diffForHumans() }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg mb-8">
                    <h3 class="text-lg font-semibold mb-4">Account Actions</h3>
                    <div class="flex flex-wrap gap-3">
                        @if($account->isCurrentlySuspended)
                            <form action="{{ route('accounts.unsuspend', $account) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Unsuspend Account
                                </button>
                            </form>
                        @else
                            <button onclick="openSuspendModal({{ $account->id }})" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Suspend Account
                            </button>
                        @endif

                        @if(! $account->email_verified_at)
                            <form action="{{ route('accounts.verify-email', $account) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Verify Email
                                </button>
                            </form>
                        @endif

                        <button onclick="openResetHwidModal({{ $account->id }})" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Reset HWID
                        </button>

                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this account? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Delete Account
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Licenses -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold mb-4">Licenses</h3>
                        @if($account->licenses->isEmpty())
                            <p class="text-gray-500 dark:text-gray-400">No licenses found for this account.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">License Key</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Privilege</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Expires At</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                        @foreach($account->licenses as $license)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $license->key }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                        {{ $license->privilege?->getLabel() ?? 'Unknown' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $statusColor = match($license->status->value) {
                                                            0 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                            1 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                            2 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                                            3 => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                                                            4 => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                                            5 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                                        {{ $license->status?->getLabel() ?? 'Unknown' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ $license->expires_at->format('Y-m-d H:i:s') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('licenses.show', $license) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Devices -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold mb-4">Devices</h3>
                        @if($account->devices->isEmpty())
                            <p class="text-gray-500 dark:text-gray-400">No devices found for this account.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Device ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">HWID Hash</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">First Seen</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Seen</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bound At</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                        @foreach($account->devices as $device)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $device->id }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ $device->hwid_hash ? substr($device->hwid_hash, 0, 16) . '...' : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($device->bound_at && ! $device->unbound_at)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                            Bound
                                                        </span>
                                                    @elseif($device->unbound_at)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                            Unbound
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                            Not Bound
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ $device->first_seen_at->format('Y-m-d H:i:s') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ $device->last_seen_at->format('Y-m-d H:i:s') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ $device->bound_at ? $device->bound_at->format('Y-m-d H:i:s') : 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                        @if($account->eventLogs->isEmpty())
                            <p class="text-gray-500 dark:text-gray-400">No activity found for this account.</p>
                        @else
                            <div class="space-y-4">
                                @foreach($account->eventLogs as $log)
                                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                    {{ $log->event_type }}
                                                </span>
                                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                                            </div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $log->id }}</span>
                                        </div>
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ json_encode($log->details, JSON_PRETTY_PRINT) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div id="suspendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 9999;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-center mx-auto h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <div class="mt-2 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Suspend Account</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Enter suspension details for this account.</p>
            </div>
            <form id="suspendForm" method="POST" class="mt-5">
                @csrf
                <div class="mb-4">
                    <label for="suspend_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason</label>
                    <input type="text" name="reason" id="suspend_reason" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div>
                <div class="mb-4">
                    <label for="suspend_duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration (days) - Optional</label>
                    <input type="number" name="duration" id="suspend_duration" min="1" max="365" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeSuspendModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Suspend
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset HWID Modal -->
<div id="resetHwidModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 9999;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-center mx-auto h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900">
                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
            <div class="mt-2 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Reset HWID</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This will unbind all devices and reset the HWID for this account.</p>
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">Warning: This action cannot be undone.</p>
            </div>
            <form id="resetHwidForm" method="POST" class="mt-5">
                @csrf
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeResetHwidModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-yellow-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-300">
                        Reset HWID
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let suspendAccountId = null;
    let resetHwidAccountId = null;

    function openSuspendModal(accountId) {
        suspendAccountId = accountId;
        document.getElementById('suspendModal').classList.remove('hidden');
        document.getElementById('suspendForm').action = `/accounts/${accountId}/suspend`;
    }

    function closeSuspendModal() {
        document.getElementById('suspendModal').classList.add('hidden');
        suspendAccountId = null;
    }

    function openResetHwidModal(accountId) {
        resetHwidAccountId = accountId;
        document.getElementById('resetHwidModal').classList.remove('hidden');
        document.getElementById('resetHwidForm').action = `/accounts/${accountId}/reset-hwid`;
    }

    function closeResetHwidModal() {
        document.getElementById('resetHwidModal').classList.add('hidden');
        resetHwidAccountId = null;
    }

    // Close modals when clicking outside
    document.getElementById('suspendModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSuspendModal();
        }
    });

    document.getElementById('resetHwidModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeResetHwidModal();
        }
    });

    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSuspendModal();
            closeResetHwidModal();
        }
    });
</script>
</x-app-layout>
