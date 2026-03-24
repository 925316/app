<x-app-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Device Management') }}
        </h2>
    </x-slot>

    <div>
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <x-stat-card :title="__('Total Devices')" :value="$totalDevices" icon="desktop" iconColor="icon-gray" />
                <x-stat-card :title="__('Bound Devices')" :value="$boundDevices" icon="success" iconColor="icon-green" />
                <x-stat-card :title="__('Active (30d)')" :value="$activeDevices" icon="lightning" iconColor="icon-yellow" />
                <x-stat-card :title="__('Unbound')" :value="$unboundDevices" icon="ban" iconColor="icon-gray" />
            </div>

            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-xl border border-zinc-200/60 dark:border-zinc-700/60">
                <div class="p-6 text-zinc-900 dark:text-zinc-100">
                    <!-- Filters Section -->
                    <x-filter-box :action="route('devices.index')" :title="__('Filter Devices')" :showTotal="true" :totalCount="$devices->total()">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="space-y-2">
                                <label for="search"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Search') }}</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="{{ __('HWID, IP, Username, Email') }}" class="form-input form-pill py-2 text-sm">
                            </div>

                            <div class="space-y-2">
                                <label for="status"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Status') }}</label>
                                <select name="status" id="status" class="form-select form-pill form-select-enhanced py-2 text-sm">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="bound" {{ request('status') === 'bound' ? 'selected' : '' }}>
                                        {{ __('Currently Bound') }}
                                    </option>
                                    <option value="unbound" {{ request('status') === 'unbound' ? 'selected' : '' }}>
                                        {{ __('Unbound') }}
                                    </option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                                        {{ __('Active (30d)') }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="date_range"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Date Range') }}</label>
                                <select name="date_range" id="date_range" class="form-select form-pill form-select-enhanced py-2 text-sm">
                                    <option value="">{{ __('All Time') }}</option>
                                    <option value="24h" {{ request('date_range') === '24h' ? 'selected' : '' }}>
                                        {{ __('Last 24 Hours') }}
                                    </option>
                                    <option value="7d" {{ request('date_range') === '7d' ? 'selected' : '' }}>
                                        {{ __('Last 7 Days') }}
                                    </option>
                                    <option value="30d" {{ request('date_range') === '30d' ? 'selected' : '' }}>
                                        {{ __('Last 30 Days') }}
                                    </option>
                                    <option value="90d" {{ request('date_range') === '90d' ? 'selected' : '' }}>
                                        {{ __('Last 90 Days') }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="country_code"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Country Code') }}</label>
                                <input type="text" name="country_code" id="country_code"
                                    value="{{ request('country_code') }}" placeholder="{{ __('e.g., US, CN') }}" class="form-input form-pill py-2 text-sm uppercase">
                            </div>

                            <div class="space-y-2">
                                <label for="min_reset_count"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Min HWID Reset') }}</label>
                                <input type="number" name="min_reset_count" id="min_reset_count"
                                    value="{{ request('min_reset_count') }}" min="0" class="form-input form-pill py-2 text-sm">
                            </div>

                            <div class="space-y-2">
                                <label for="account_status"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Account Status') }}</label>
                                <select name="account_status" id="account_status" class="form-select form-pill form-select-enhanced py-2 text-sm">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="active" {{ request('account_status') === 'active' ? 'selected' : '' }}>
                                        {{ __('Active') }}
                                    </option>
                                    <option value="suspended" {{ request('account_status') === 'suspended' ? 'selected' : '' }}>
                                        {{ __('Suspended') }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex items-end gap-2 xl:col-span-2 xl:justify-end">
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    {{ __('Apply Filters') }}
                                </button>
                                <a href="{{ route('devices.index') }}" class="btn btn-secondary btn-sm">
                                    {{ __('Reset') }}
                                </a>
                                <a href="{{ route('devices.export', request()->query()) }}" class="btn btn-secondary btn-sm">
                                    {{ __('Export CSV') }}
                                </a>
                            </div>
                        </div>
                    </x-filter-box>

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
                                            class="px-2 py-0.5 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-xs font-medium">
                                            {{ __('Currently Bound') }}
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-xs font-medium">
                                            {{ __('Historical') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    @if ($device->account->isSuspended())
                                        <span
                                            class="px-2 py-0.5 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg text-xs font-medium">
                                            {{ __('Suspended') }}
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-xs font-medium">
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
                                                    class="px-2 py-1 bg-yellow-600 text-white text-xs rounded-lg hover:bg-yellow-700 transition">
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
                                                    class="px-2 py-1 bg-red-600 text-white text-xs rounded-lg hover:bg-red-700 transition">
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
