@php use App\Enums\LicensePrivilege; @endphp
<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Account Details') }}
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6" data-page="accounts-show">
        <div class="card-shell">
            <div class="app-toolbar mb-0">
                <div>
                    <p class="section-kicker">{{ __('Account') }}</p>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $account->username }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('ID:') }} #{{ $account->id }}</p>
                </div>
                <div class="flex gap-2">
                    <x-primary-button tag="a" href="{{ route('accounts.edit', $account) }}">
                        {{ __('Edit Account') }}
                    </x-primary-button>
                    <x-secondary-button tag="a" href="{{ route('accounts.index') }}">
                        {{ __('Back to Accounts') }}
                    </x-secondary-button>
                </div>
            </div>

        {{-- Account Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card-shell-muted">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Account Information') }}</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Username') }}</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $account->username }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Email') }}</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $account->email }}</p>
                        @if ($account->email_verified_at)
                            <x-status-badge status="verified" :text="__('Verified')" />
                        @else
                            <x-status-badge status="unverified" :text="__('Unverified')" />
                        @endif
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Status') }}</span>
                        @if ($account->isCurrentlySuspended)
                            <x-status-badge status="suspended" />
                        @else
                            <x-status-badge status="active" />
                        @endif
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Privilege Level') }}</span>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $account->getPrivilegeLevel() ? ucfirst(strtolower(LicensePrivilege::tryFrom($account->getPrivilegeLevel())?->getLabel() ?? __('Unknown'))) : __('None') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="card-shell-muted">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Login Information') }}</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Last Login') }}</span>
                        <p class="font-medium text-gray-900 dark:text-white">
                            @if ($account->last_login_at)
                                {{ $account->last_login_at->diffForHumans() }}
                                <br>
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400">{{ $account->last_login_at->format('Y-m-d H:i:s') }}</span>
                            @else
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Never') }}</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Last IP Address') }}</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $account->last_ip_address ?? __('N/A') }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Registration Date') }}</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $account->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>

            <div class="card-shell-muted">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Device Information') }}</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Devices') }}</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $account->devices_count }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Bound Devices') }}</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $boundDevices->count() }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('HWID Resets') }}</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $account->hwid_reset_count }}</p>
                        @if ($account->hwid_last_reset_at)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Last reset:') }}
                                {{ $account->hwid_last_reset_at->diffForHumans() }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="card-shell-muted mb-8">
            <p class="section-kicker mb-3">{{ __('Account Actions') }}</p>
            <div class="flex flex-wrap gap-3">
                @if ($account->isCurrentlySuspended)
                    <form action="{{ route('accounts.unsuspend', $account) }}" method="POST">
                        @csrf
                        <x-primary-button type="submit">{{ __('Unsuspend Account') }}</x-primary-button>
                    </form>
                @else
                    <x-danger-button onclick="openSuspendModal('{{ $account->id }}')">{{ __('Suspend Account') }}</x-danger-button>
                @endif

                @if (!$account->email_verified_at)
                    <form action="{{ route('accounts.verify-email', $account) }}" method="POST">
                        @csrf
                        <x-primary-button type="submit">{{ __('Verify Email') }}</x-primary-button>
                    </form>
                @endif

                <x-secondary-button onclick="openResetHwidModal('{{ $account->id }}')">{{ __('Reset HWID') }}</x-secondary-button>

                <form action="{{ route('accounts.destroy', $account) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <x-danger-button type="submit">{{ __('Delete Account') }}</x-danger-button>
                </form>
            </div>
        </div>

        {{-- Licenses --}}
        <div class="card-shell mb-8">
            <div class="mb-4">
                <p class="section-kicker">{{ __('Licenses') }}</p>
                @if ($account->licenses->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No licenses found for this account.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
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
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($account->licenses as $license)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $license->key }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <x-status-badge :status="strtolower($license->privilege?->getLabel() ?? 'default')" :text="$license->privilege?->getLabel() ?? __('Unknown')" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-status-badge :status="strtolower($license->status?->getLabel() ?? 'default')" :text="$license->status?->getLabel() ?? __('Unknown')" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $license->expires_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('licenses.show', $license) }}"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
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

        {{-- Devices --}}
        <div class="card-shell mb-8">
            <div class="mb-4">
                <p class="section-kicker">{{ __('Devices') }}</p>
                @if ($account->devices->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No devices found for this account.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
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
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($account->devices as $device)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $device->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $device->first_seen_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $device->last_seen_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
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

        {{-- Recent Activity --}}
        <div class="card-shell">
            <div class="mb-4">
                <p class="section-kicker">{{ __('Recent Activity') }}</p>
                @if ($account->eventLogs->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No activity found for this account.') }}</p>
                @else
                    <div class="space-y-4">
                        @foreach ($account->eventLogs as $log)
                            <div class="card-shell-muted">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <x-status-badge status="info" :text="$log->event_type" />
                                        <span
                                            class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('ID:') }}
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
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white text-center mb-2">{{ __('Suspend Account') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-4">{{ __('Enter suspension details for this account.') }}</p>
                <form id="suspendForm" method="POST" class="mt-5">
                    @csrf
                    <div class="mb-4">
                        <label for="suspend_reason"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Reason') }}</label>
                        <input type="text" name="reason" id="suspend_reason"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    </div>
                    <div class="mb-4">
                        <label for="suspend_duration"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Duration (days) - Optional') }}</label>
                        <input type="number" name="duration" id="suspend_duration" min="1"
                            max="365"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    </div>
                    <div class="flex justify-end gap-2">
                        <x-secondary-button type="button" x-on:click="show = false">{{ __('Cancel') }}</x-secondary-button>
                        <x-danger-button type="submit">{{ __('Suspend') }}</x-danger-button>
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
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white text-center mb-2">{{ __('Reset HWID') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-2">{{ __('This will unbind all devices and reset the HWID for this account.') }}</p>
                <p class="text-sm text-red-600 dark:text-red-400 text-center mb-4">{{ __('Warning: This action cannot be undone.') }}</p>
                <form id="resetHwidForm" method="POST" class="mt-5">
                    @csrf
                    <div class="flex justify-end gap-2">
                        <x-secondary-button type="button" x-on:click="show = false">{{ __('Cancel') }}</x-secondary-button>
                        <x-primary-button type="submit">{{ __('Reset HWID') }}</x-primary-button>
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
