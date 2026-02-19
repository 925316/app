<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Session Management') }}
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <x-stat-card title="Total Sessions" :value="$statistics['total']" icon="server" iconColor="icon-blue" />
                <x-stat-card title="Active Sessions" :value="$statistics['active']" icon="success" iconColor="icon-green" />
                <x-stat-card title="Expired Sessions" :value="$statistics['expired']" icon="error" iconColor="icon-red" />
                <x-stat-card title="Unique Accounts" :value="$statistics['unique_accounts']" icon="users" iconColor="icon-purple" />
                <x-stat-card title="Unique Devices" :value="$statistics['unique_devices']" icon="desktop" iconColor="icon-orange" />
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h3 class="text-lg font-medium">
                            Session Management
                        </h3>
                    </div>

                    <!-- Filters -->
                    <div
                        class="mb-6 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                    </path>
                                </svg>
                                Filter Sessions
                            </h4>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $sessions->total() }} total sessions
                                </span>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('sessions.index') }}" data-clean-form="true"
                            data-default-values="sort:last_heartbeat_at,direction:desc">
                            <!-- Status & Sort Row -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <!-- Status filter -->
                                <div class="space-y-2">
                                    <label for="status"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <select name="status" id="status"
                                        class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                        @foreach ($statusOptions as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ $currentFilters['status'] === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Sort with direction -->
                                <div class="space-y-2 md:col-span-2">
                                    <label for="sort"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort
                                        By</label>
                                    <div class="flex gap-2">
                                        <select name="sort" id="sort"
                                            class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                            <option value="last_heartbeat_at"
                                                {{ $currentFilters['sort'] === 'last_heartbeat_at' ? 'selected' : '' }}>
                                                Last Heartbeat
                                            </option>
                                            <option value="created_at"
                                                {{ $currentFilters['sort'] === 'created_at' ? 'selected' : '' }}>
                                                Created
                                            </option>
                                        </select>
                                        <select name="direction"
                                            class="w-24 px-2 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                            <option value="desc"
                                                {{ $currentFilters['direction'] === 'desc' ? 'selected' : '' }}>
                                                ↓
                                            </option>
                                            <option value="asc"
                                                {{ $currentFilters['direction'] === 'asc' ? 'selected' : '' }}>
                                                ↑
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Search Row -->
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                <!-- Search -->
                                <div class="space-y-2 md:col-span-8">
                                    <label for="search"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </div>
                                        <input type="text" name="search" id="search"
                                            value="{{ $currentFilters['search'] }}"
                                            class="w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200"
                                            placeholder="Search by account username, device name, or session token...">
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="space-y-2 md:col-span-4">
                                    <label class="block text-sm font-medium text-transparent">Actions</label>
                                    <div class="flex gap-2">
                                        <button type="submit"
                                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-medium shadow-sm flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                            Filter
                                        </button>
                                        <a href="{{ route('sessions.index') }}"
                                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-medium shadow-sm flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                            Reset
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Active Filters -->
                            @php
                                $hasFilters =
                                    request()->filled('status') ||
                                    request()->filled('search') ||
                                    request()->filled('sort');
                            @endphp

                            @if ($hasFilters)
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Active Filters
                                        </span>
                                        <a href="{{ route('sessions.index') }}"
                                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                                            Clear All
                                        </a>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @if (request()->filled('status'))
                                            @php
                                                $statusValue = request('status');
                                                $statusLabel = $statusOptions[$statusValue] ?? null;
                                            @endphp
                                            @if ($statusLabel)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    Status: {{ $statusLabel }}
                                                    <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}"
                                                        class="ml-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </a>
                                                </span>
                                            @endif
                                        @endif
                                        @if (request()->filled('search'))
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                Search: "{{ request('search') }}"
                                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}"
                                                    class="ml-1.5 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-200">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </a>
                                            </span>
                                        @endif
                                        @if (request()->filled('sort'))
                                            @php
                                                $sortValue = request('sort');
                                                $directionValue = request('direction', 'desc');
                                                $sortLabel = match ($sortValue) {
                                                    'last_heartbeat_at' => 'Last Heartbeat',
                                                    'created_at' => 'Created',
                                                    default => ucfirst($sortValue),
                                                };
                                                $directionArrow = $directionValue === 'desc' ? '↓' : '↑';
                                            @endphp
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                Sort: {{ $sortLabel }} {{ $directionArrow }}
                                                <a href="{{ request()->fullUrlWithQuery(['sort' => null, 'direction' => null]) }}"
                                                    class="ml-1.5 text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-200">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </a>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>

                    <!-- Sessions table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Account
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Device
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        IP Address
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Client Version
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Last Heartbeat
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Created
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($sessions as $session)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            @if ($session->account)
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div
                                                            class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                                            {{ $session->account->initials() }}
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ $session->account->username }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $session->account->email }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400">Unknown</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            @if ($session->device)
                                                <div>
                                                    <div class="text-sm font-medium">
                                                        {{ $session->device->device_name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        ID: {{ $session->device->id }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400">Unknown</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $session->ip_address ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                {{ $session->client_version ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            @if ($session->isActive())
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                    Active
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                    Expired
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            @if ($session->last_heartbeat_at)
                                                {{ $session->last_heartbeat_at->diffForHumans() }}
                                                <br>
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400">{{ $session->last_heartbeat_at->format('Y-m-d H:i:s') }}</span>
                                            @else
                                                <span class="text-gray-500 dark:text-gray-400">Never</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            @if ($session->created_at)
                                                {{ $session->created_at->diffForHumans() }}
                                                <br>
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400">{{ $session->created_at->format('Y-m-d H:i:s') }}</span>
                                            @else
                                                <span class="text-gray-500 dark:text-gray-400">Unknown</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('sessions.show', $session) }}"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                                View
                                            </a>
                                            <span class="mx-1 text-gray-400">|</span>
                                            <form action="{{ route('sessions.destroy', $session) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('Are you sure you want to terminate this session? The client will be disconnected on next heartbeat check. This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                                    Terminate
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                                            No sessions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 pagination-container">
                        {{ $sessions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-sidebar-layout>
