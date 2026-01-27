<x-app-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Device Management') }}
        </h2>
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-500/20 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Devices</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalDevices }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-500/20 rounded-full">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bound Devices</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $boundDevices }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-500/20 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active (30d)</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $activeDevices }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-gray-500/20 rounded-full">
                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Unbound</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $unboundDevices }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Filters Section -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <h4 class="text-sm font-medium mb-3">Filters</h4>
                        <form method="GET" action="{{ route('devices.admin') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div>
                                <label for="search" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                    placeholder="HWID, IP, Username, Email"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100">
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label for="status" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select name="status" id="status" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">All</option>
                                    <option value="bound" {{ request('status') === 'bound' ? 'selected' : '' }}>Currently Bound</option>
                                    <option value="unbound" {{ request('status') === 'unbound' ? 'selected' : '' }}>Unbound</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active (30d)</option>
                                </select>
                            </div>

                            <!-- Date Range Filter -->
                            <div>
                                <label for="date_range" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
                                <select name="date_range" id="date_range" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">All Time</option>
                                    <option value="24h" {{ request('date_range') === '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                                    <option value="7d" {{ request('date_range') === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                                    <option value="30d" {{ request('date_range') === '30d' ? 'selected' : '' }}>Last 30 Days</option>
                                    <option value="90d" {{ request('date_range') === '90d' ? 'selected' : '' }}>Last 90 Days</option>
                                </select>
                            </div>

                            <!-- Country Code Filter -->
                            <div>
                                <label for="country_code" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Country Code</label>
                                <input type="text" name="country_code" id="country_code" value="{{ request('country_code') }}" 
                                    placeholder="e.g., US, CN"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100 uppercase">
                            </div>

                            <!-- HWID Reset Count Filter -->
                            <div>
                                <label for="min_reset_count" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Min HWID Reset</label>
                                <input type="number" name="min_reset_count" id="min_reset_count" value="{{ request('min_reset_count') }}" 
                                    min="0"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100">
                            </div>

                            <!-- Account Status Filter -->
                            <div>
                                <label for="account_status" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Account Status</label>
                                <select name="account_status" id="account_status" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">All</option>
                                    <option value="active" {{ request('account_status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ request('account_status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-span-full flex gap-2 mt-2">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition">
                                    Apply Filters
                                </button>
                                <a href="{{ route('devices.admin') }}" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 transition">
                                    Reset
                                </a>
                                <a href="{{ route('devices.admin.export', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition">
                                    Export CSV
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Device Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Account
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        HWID Hash
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        IP / Country
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        First / Last Seen
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Account Status
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        HWID Resets
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($devices as $device)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $device->account->username }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $device->account->email }}</div>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            @if($device->hwid_hash)
                                                <span title="{{ $device->hwid_hash }}" class="cursor-help">
                                                    {{ substr($device->hwid_hash, 0, 8) }}...
                                                </span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            <div class="text-gray-900 dark:text-gray-100">{{ $device->ip_address }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $device->country_code ?? 'Unknown' }}</div>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            <div class="text-gray-900 dark:text-gray-100">F: {{ $device->first_seen_at->format('Y-m-d H:i') }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">L: {{ $device->last_seen_at->format('Y-m-d H:i') }}</div>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            @if($device->isBound())
                                                <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs font-medium">
                                                    Currently Bound
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-xs font-medium">
                                                    Historical
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            @if($device->account->isSuspended())
                                                <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded text-xs font-medium">
                                                    Suspended
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs font-medium">
                                                    Active
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $device->account->hwid_reset_count }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            <div class="flex gap-2">
                                                @if($device->isBound())
                                                    <form method="POST" action="{{ route('devices.admin.unbind', $device) }}" class="inline" onsubmit="return confirm('Unbind device from {{ $device->account->username }}?');">
                                                        @csrf
                                                        <button type="submit" class="px-2 py-1 bg-yellow-600 text-white text-xs rounded hover:bg-yellow-700 transition">
                                                            Unbind
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($device->account->canResetHwid())
                                                    <form method="POST" action="{{ route('devices.admin.reset-hwid', $device->account) }}" class="inline" onsubmit="return confirm('Reset HWID for {{ $device->account->username }}? This will unbind all devices.');">
                                                        @csrf
                                                        <button type="submit" class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition">
                                                            Reset HWID
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                                            No devices found matching your filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $devices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>