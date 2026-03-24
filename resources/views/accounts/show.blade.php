@php use App\Enums\LicensePrivilege; @endphp
<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Account Details') }}
    </x-slot>

    <div class="mx-auto max-w-7xl">
        <div
            class="bg-white/80 dark:bg-zinc-800/80 backdrop-blur-sm rounded-2xl shadow-sm border border-zinc-200/50 dark:border-zinc-700/50 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $account->username }}</h1>
                <p class="text-zinc-500 dark:text-zinc-400">{{ __('Account ID:') }} {{ $account->id }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('accounts.edit', $account) }}" class="btn btn-secondary btn-sm">
                    {{ __('Edit Account') }}
                </a>
                <a href="{{ route('accounts.index') }}" class="btn btn-secondary btn-sm">
                    {{ __('Back to Accounts') }}
                </a>
            </div>
        </div>

        <!-- Account Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-zinc-50 dark:bg-zinc-800 p-6 rounded-xl">
                <h3 class="text-lg font-semibold mb-4 text-zinc-900 dark:text-zinc-100">{{ __('Account Information') }}</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Username') }}</span>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $account->username }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</span>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $account->email }}</p>
                        @if ($account->email_verified_at)
                            <x-status-badge status="verified" :text="__('Verified')" />
                        @else
                            <x-status-badge status="unverified" :text="__('Unverified')" />
                        @endif
                    </div>
                    <div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</span>
                        @if ($account->isCurrentlySuspended)
                            <x-status-badge status="suspended" />
                        @else
                            <x-status-badge status="active" />
                        @endif
                    </div>
                    <div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Privilege Level') }}</span>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $account->getPrivilegeLevel() ? ucfirst(strtolower(LicensePrivilege::tryFrom($account->getPrivilegeLevel())?->getLabel() ?? __('Unknown'))) : __('None') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-zinc-50 dark:bg-zinc-800 p-6 rounded-xl">
                <h3 class="text-lg font-semibold mb-4 text-zinc-900 dark:text-zinc-100">{{ __('Login Information') }}</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Last Login') }}</span>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">
                            @if ($account->last_login_at)
                                {{ $account->last_login_at->diffForHumans() }}
                                <br>
                                <span
                                    class="text-xs text-zinc-500 dark:text-zinc-400">{{ $account->last_login_at->format('Y-m-d H:i:s') }}</span>
                            @else
                                <span class="text-zinc-500 dark:text-zinc-400">{{ __('Never') }}</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Last IP Address') }}</span>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $account->last_ip_address ?? __('N/A') }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Registration Date') }}</span>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $account->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-zinc-50 dark:bg-zinc-800 p-6 rounded-xl">
                <h3 class="text-lg font-semibold mb-4 text-zinc-900 dark:text-zinc-100">{{ __('Device Information') }}</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Devices') }}</span>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $account->devices_count }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Bound Devices') }}</span>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $boundDevices->count() }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('HWID Resets') }}</span>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $account->hwid_reset_count }}</p>
                        @if ($account->hwid_last_reset_at)
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Last reset:') }}
                                {{ $account->hwid_last_reset_at->diffForHumans() }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-zinc-50 dark:bg-zinc-800 p-6 rounded-xl mb-8">
            <h3 class="text-lg font-semibold mb-4 text-zinc-900 dark:text-zinc-100">{{ __('Account Actions') }}</h3>
            <div class="flex flex-wrap gap-3">
                @if ($account->isCurrentlySuspended)
                    <form action="{{ route('accounts.unsuspend', $account) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">
                            {{ __('Unsuspend Account') }}
                        </button>
                    </form>
                @else
                    <button onclick="openSuspendModal('{{ $account->id }}')" class="btn btn-danger btn-sm">
                        {{ __('Suspend Account') }}
                    </button>
                @endif

                @if (!$account->email_verified_at)
                    <form action="{{ route('accounts.verify-email', $account) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">
                            {{ __('Verify Email') }}
                        </button>
                    </form>
                @endif

                <button onclick="openResetHwidModal('{{ $account->id }}')" class="btn btn-secondary btn-sm">
                    {{ __('Reset HWID') }}
                </button>

                <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        {{ __('Delete Account') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Licenses -->
        <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-xl mb-8">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-semibold mb-4 text-zinc-900 dark:text-zinc-100">{{ __('Licenses') }}</h3>
                @if ($account->licenses->isEmpty())
                    <p class="text-zinc-500 dark:text-zinc-400">{{ __('No licenses found for this account.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                        {{ __('License Key') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Privilege') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Expires At') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($account->licenses as $license)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $license->key }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                            <x-status-badge :status="strtolower($license->privilege?->getLabel() ?? 'default')" :text="$license->privilege?->getLabel() ?? __('Unknown')" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-status-badge :status="strtolower($license->status?->getLabel() ?? 'default')" :text="$license->status?->getLabel() ?? __('Unknown')" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $license->expires_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('licenses.show', $license) }}"
                                                class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100">
                                                {{ __('View') }}
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
        <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-xl mb-8">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-semibold mb-4 text-zinc-900 dark:text-zinc-100">{{ __('Devices') }}</h3>
                @if ($account->devices->isEmpty())
                    <p class="text-zinc-500 dark:text-zinc-400">{{ __('No devices found for this account.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                        {{ __('Device ID') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('HWID Hash') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('First Seen') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Last Seen') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Bound At') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($account->devices as $device)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $device->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $device->hwid_hash ? substr($device->hwid_hash, 0, 16) . '...' : __('N/A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($device->bound_at && !$device->unbound_at)
                                                <x-status-badge status="active" :text="__('Bound')" />
                                            @elseif($device->unbound_at)
                                                <x-status-badge status="suspended" :text="__('Unbound')" />
                                            @else
                                                <x-status-badge status="default" :text="__('Not Bound')" />
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $device->first_seen_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $device->last_seen_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $device->bound_at ? $device->bound_at->format('Y-m-d H:i:s') : __('N/A') }}
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
        <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-xl">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-semibold mb-4 text-zinc-900 dark:text-zinc-100">{{ __('Recent Activity') }}</h3>
                @if ($account->eventLogs->isEmpty())
                    <p class="text-zinc-500 dark:text-zinc-400">{{ __('No activity found for this account.') }}</p>
                @else
                    <div class="space-y-4">
                        @foreach ($account->eventLogs as $log)
                            <div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-xl">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                            {{ $log->event_type }}
                                        </span>
                                        <span
                                            class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('ID:') }}
                                        {{ $log->id }}</span>
                                </div>
                                <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                    <pre class="bg-zinc-100 dark:bg-zinc-900 p-3 rounded-lg text-xs overflow-x-auto">{{ json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
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
                <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-zinc-100 text-center mb-2">{{ __('Suspend Account') }}</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center mb-4">{{ __('Enter suspension details for this account.') }}</p>
                <form id="suspendForm" method="POST" class="mt-5">
                    @csrf
                    <div class="mb-4">
                        <label for="suspend_reason"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Reason') }}</label>
                        <input type="text" name="reason" id="suspend_reason"
                            class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-zinc-500 focus:ring-white/30 dark:bg-zinc-700 dark:border-zinc-500 dark:text-zinc-100">
                    </div>
                    <div class="mb-4">
                        <label for="suspend_duration"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Duration (days) - Optional') }}</label>
                        <input type="number" name="duration" id="suspend_duration" min="1"
                            max="365"
                            class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-zinc-500 focus:ring-white/30 dark:bg-zinc-700 dark:border-zinc-500 dark:text-zinc-100">
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="show = false"
                            class="btn btn-secondary">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                            {{ __('Suspend') }}
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
                <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-zinc-100 text-center mb-2">{{ __('Reset HWID') }}</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center mb-2">{{ __('This will unbind all devices and reset the HWID for this account.') }}</p>
                <p class="text-sm text-red-600 dark:text-red-400 text-center mb-4">{{ __('Warning: This action cannot be undone.') }}</p>
                <form id="resetHwidForm" method="POST" class="mt-5">
                    @csrf
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="show = false"
                            class="btn btn-secondary">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-secondary">
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
    </div>

</x-app-sidebar-layout>
