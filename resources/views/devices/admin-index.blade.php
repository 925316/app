<x-app-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Device Management') }}
        </h2>
    </x-slot>

    <div class="py-7">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <x-stat-card :title="__('Total Devices')" :value="$totalDevices" icon="desktop" iconColor="icon-blue" />
                <x-stat-card :title="__('Bound Devices')" :value="$boundDevices" icon="success" iconColor="icon-green" />
                <x-stat-card :title="__('Active (30d)')" :value="$activeDevices" icon="lightning" iconColor="icon-yellow" />
                <x-stat-card :title="__('Unbound')" :value="$unboundDevices" icon="ban" iconColor="icon-gray" />
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Filters Section -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <h4 class="text-sm font-medium mb-3">{{ __('Filters') }}</h4>
                        <form method="GET" action="{{ route('devices.index') }}" data-clean-form="true"
                            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div>
                                <label for="search"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Search') }}</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="{{ __('HWID, IP, Username, Email') }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100">
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label for="status"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Status') }}</label>
                                <select name="status" id="status"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="bound" {{ request('status') === 'bound' ? 'selected' : '' }}>
                                        {{ __('Currently Bound') }}
                                    </option>
                                    <option value="unbound" {{ request('status') === 'unbound' ? 'selected' : '' }}>
                                        {{ __('Unbound') }}
                                    </option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active
                                        {{ __('(30d)') }}
                                    </option>
                                </select>
                            </div>

                            <!-- Date Range Filter -->
                            <div>
                                <label for="date_range"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Date Range') }}</label>
                                <select name="date_range" id="date_range"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">{{ __('All Time') }}</option>
                                    <option value="24h" {{ request('date_range') === '24h' ? 'selected' : '' }}>Last
                                        {{ __('Last 24 Hours') }}
                                    </option>
                                    <option value="7d" {{ request('date_range') === '7d' ? 'selected' : '' }}>Last
                                        {{ __('Last 7 Days') }}
                                    </option>
                                    <option value="30d" {{ request('date_range') === '30d' ? 'selected' : '' }}>Last
                                        {{ __('Last 30 Days') }}
                                    </option>
                                    <option value="90d" {{ request('date_range') === '90d' ? 'selected' : '' }}>Last
                                        {{ __('Last 90 Days') }}
                                    </option>
                                </select>
                            </div>

                            <!-- Country Code Filter -->
                            <div>
                                <label for="country_code"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Country Code') }}</label>
                                <input type="text" name="country_code" id="country_code"
                                    value="{{ request('country_code') }}" placeholder="{{ __('e.g., US, CN') }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100 uppercase">
                            </div>

                            <!-- HWID Reset Count Filter -->
                            <div>
                                <label for="min_reset_count"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Min HWID Reset') }}</label>
                                <input type="number" name="min_reset_count" id="min_reset_count"
                                    value="{{ request('min_reset_count') }}" min="0"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100">
                            </div>

                            <!-- Account Status Filter -->
                            <div>
                                <label for="account_status"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Account Status') }}</label>
                                <select name="account_status" id="account_status"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="active"
                                        {{ request('account_status') === 'active' ? 'selected' : '' }}>
                                        {{ __('Active') }}
                                    </option>
                                    <option value="suspended"
                                        {{ request('account_status') === 'suspended' ? 'selected' : '' }}>
                                        {{ __('Suspended') }}
                                    </option>
                                </select>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-span-full flex gap-2 mt-2">
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition">
                                    {{ __('Apply Filters') }}
                                </button>
                                <a href="{{ route('devices.index') }}"
                                    class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 transition">
                                    {{ __('Reset') }}
                                </a>
                                <a href="{{ route('devices.export', request()->query()) }}"
                                    class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition">
                                    {{ __('Export CSV') }}
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Device Table -->

                    <x-table :headers="[
                        __('Account'),
                        __('HWID Hash'),
                        __('IP / Country'),
                        __('First / Last Seen'),
                        __('Status'),
                        __('Account Status'),
                        __('HWID Resets'),
                        __('Actions'),
                    ]" :emptyColspan="5">
                        @forelse($devices as $device)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $device->account->username }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $device->account->email }}</div>
                                </td>
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    @if ($device->hwid_hash)
                                        <span title="{{ $device->hwid_hash }}" class="cursor-help">
                                            {{ substr($device->hwid_hash, 0, 8) }}...
                                        </span>
                                    @else
                                        {{ __('N/A') }}
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <div class="text-gray-900 dark:text-gray-100">{{ $device->ip_address }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $device->country_code ?? __('Unknown') }}</div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <div class="text-gray-900 dark:text-gray-100">
                                        {{ __('L:') }} {{ $device->last_seen_at->format('Y-m-d H:i') }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('F:') }} {{ $device->first_seen_at->format('Y-m-d H:i') }}</div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    @if ($device->isBound())
                                        <span
                                            class="px-2 py-0.5 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs font-medium">
                                            {{ __('Currently Bound') }}
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-xs font-medium">
                                            {{ __('Historical') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    @if ($device->account->isSuspended())
                                        <span
                                            class="px-2 py-0.5 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded text-xs font-medium">
                                            {{ __('Suspended') }}
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs font-medium">
                                            {{ __('Active') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $device->account->hwid_reset_count }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <div class="flex gap-2">
                                        @if ($device->isBound())
                                            <form method="POST"
                                                action="{{ route('devices.unbind-admin', $device) }}" class="inline"
                                                onsubmit="return confirm('Unbind device from {{ $device->account->username }}?');">
                                                @csrf
                                                <button type="submit"
                                                    class="px-2 py-1 bg-yellow-600 text-white text-xs rounded hover:bg-yellow-700 transition">
                                                    {{ __('Unbind') }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($device->account->canResetHwid())
                                            <form method="POST"
                                                action="{{ route('devices.reset-hwid-admin', $device->account) }}"
                                                class="inline"
                                                onsubmit="return confirm('Reset HWID for {{ $device->account->username }}? This will unbind all devices.');">
                                                @csrf
                                                <button type="submit"
                                                    class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition">
                                                    {{ __('Reset HWID') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8"
                                    class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                                    {{ __('No devices found matching your filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </x-table>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <x-pagination :paginator="$devices" />
                    </div>
                </div>
        </div>
    </div>

</x-app-sidebar-layout>
