@php use App\Enums\LicensePrivilege; @endphp
<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Account Details') }}
    </x-slot>

    <div
        class="bg-white/80 dark:bg-cool-800/80 backdrop-blur-sm rounded-xl shadow-sm border border-cool-200/50 dark:border-cool-700/50 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $account->username }}</h1>
                <p class="text-gray-500 dark:text-gray-400">Account ID: {{ $account->id }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('accounts.edit', $account) }}" class="btn btn-indigo btn-sm">
                    Edit Account
                </a>
                <a href="{{ route('accounts.index') }}" class="btn btn-secondary btn-sm">
                    Back to Accounts
                </a>
            </div>
        </div>

        <!-- Account Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Account Information</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Username</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $account->username }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Email</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $account->email }}</p>
                        @if ($account->email_verified_at)
                            <x-status-badge status="verified" text="Verified" />
                        @else
                            <x-status-badge status="unverified" text="Unverified" />
                        @endif
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                        @if ($account->isCurrentlySuspended)
                            <x-status-badge status="suspended" />
                        @else
                            <x-status-badge status="active" />
                        @endif
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Privilege Level</span>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $account->getPrivilegeLevel() ? ucfirst(strtolower(LicensePrivilege::tryFrom($account->getPrivilegeLevel())?->getLabel() ?? 'Unknown')) : 'None' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Login Information</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Last Login</span>
                        <p class="font-medium text-gray-900 dark:text-white">
                            @if ($account->last_login_at)
                                {{ $account->last_login_at->diffForHumans() }}
                                <br>
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400">{{ $account->last_login_at->format('Y-m-d H:i:s') }}</span>
                            @else
                                <span class="text-gray-500 dark:text-gray-400">Never</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Last IP Address</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $account->last_ip_address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Registration Date</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $account->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Device Information</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Devices</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $account->devices_count }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Bound Devices</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $boundDevices->count() }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">HWID Resets</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $account->hwid_reset_count }}</p>
                        @if ($account->hwid_last_reset_at)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Last
                                reset: {{ $account->hwid_last_reset_at->diffForHumans() }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg mb-8">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Account Actions</h3>
            <div class="flex flex-wrap gap-3">
                @if ($account->isCurrentlySuspended)
                    <form action="{{ route('accounts.unsuspend', $account) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-green btn-sm">
                            Unsuspend Account
                        </button>
                    </form>
                @else
                    <button onclick="openSuspendModal({{ $account->id }})" class="btn btn-danger btn-sm">
                        Suspend Account
                    </button>
                @endif

                @if (!$account->email_verified_at)
                    <form action="{{ route('accounts.verify-email', $account) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-blue btn-sm">
                            Verify Email
                        </button>
                    </form>
                @endif

                <button onclick="openResetHwidModal({{ $account->id }})" class="btn btn-yellow btn-sm">
                    Reset HWID
                </button>

                <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        Delete Account
                    </button>
                </form>
            </div>
        </div>

        <!-- Licenses -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
            <div class="p-6 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Licenses</h3>
                @if ($account->licenses->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">No licenses found for this account.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        License Key
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Privilege
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Expires At
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($account->licenses as $license)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $license->key }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <x-status-badge :status="strtolower($license->privilege?->getLabel() ?? 'default')" :text="$license->privilege?->getLabel() ?? 'Unknown'" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-status-badge :status="strtolower($license->status?->getLabel() ?? 'default')" :text="$license->status?->getLabel() ?? 'Unknown'" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $license->expires_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('licenses.show', $license) }}"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
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
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Devices</h3>
                @if ($account->devices->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">No devices found for this account.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Device ID
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        HWID Hash
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        First Seen
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Last Seen
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Bound At
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($account->devices as $device)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $device->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $device->hwid_hash ? substr($device->hwid_hash, 0, 16) . '...' : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($device->bound_at && !$device->unbound_at)
                                                <x-status-badge status="active" text="Bound" />
                                            @elseif($device->unbound_at)
                                                <x-status-badge status="suspended" text="Unbound" />
                                            @else
                                                <x-status-badge status="default" text="Not Bound" />
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
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Recent Activity</h3>
                @if ($account->eventLogs->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">No activity found for this account.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($account->eventLogs as $log)
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                            {{ $log->event_type }}
                                        </span>
                                        <span
                                            class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">ID:
                                        {{ $log->id }}</span>
                                </div>
                                <div class="text-sm text-gray-900 dark:text-white">
                                    <pre class="bg-gray-100 dark:bg-gray-900 p-3 rounded text-xs overflow-x-auto">{{ json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            </div>
                        @endforeach
                    </div>
            </div>
            @endif
        </div>

        <!-- Suspend Modal -->
        <x-modal name="suspend-modal">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white text-center mb-2">Suspend Account</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-4">Enter suspension details for this
                    account.</p>
                <form id="suspendForm" method="POST" class="mt-5">
                    @csrf
                    <div class="mb-4">
                        <label for="suspend_reason"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason</label>
                        <input type="text" name="reason" id="suspend_reason"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    </div>
                    <div class="mb-4">
                        <label for="suspend_duration"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration (days) -
                            Optional</label>
                        <input type="number" name="duration" id="suspend_duration" min="1"
                            max="365"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="show = false"
                            class="btn btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            Suspend
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>

        <!-- Reset HWID Modal -->
        <x-modal name="reset-hwid-modal">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white text-center mb-2">Reset HWID</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-2">This will unbind all devices and reset
                    the HWID for this account.</p>
                <p class="text-sm text-red-600 dark:text-red-400 text-center mb-4">Warning: This action cannot be
                    undone.</p>
                <form id="resetHwidForm" method="POST" class="mt-5">
                    @csrf
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="show = false"
                            class="btn btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-yellow">
                            Reset HWID
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>

        <script>
            let suspendAccountId = null;
            let resetHwidAccountId = null;

            function openSuspendModal(accountId) {
                suspendAccountId = accountId;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'suspend-modal' }));
                document.getElementById('suspendForm').action = `/accounts/${accountId}/suspend`;
            }

            function openResetHwidModal(accountId) {
                resetHwidAccountId = accountId;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reset-hwid-modal' }));
                document.getElementById('resetHwidForm').action = `/accounts/${accountId}/reset-hwid`;
            }
        </script>
    </div>

</x-app-sidebar-layout>
