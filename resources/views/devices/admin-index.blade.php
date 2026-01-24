<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Device Management') }}
        </h2>
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header with actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h3 class="text-lg font-medium">
                            All Devices
                        </h3>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('devices.manage') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                My Device Management
                            </a>
                            <button id="exportBtn" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                Export Data
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Dashboard -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 p-4 rounded-lg text-white shadow-lg">
                            <div class="text-sm font-medium opacity-90">Total Devices</div>
                            <div class="text-3xl font-bold mt-1">{{ $totalDevices }}</div>
                            <div class="text-xs opacity-80 mt-1">All devices in system</div>
                        </div>
                        <div class="bg-gradient-to-r from-green-500 to-green-600 dark:from-green-600 dark:to-green-700 p-4 rounded-lg text-white shadow-lg">
                            <div class="text-sm font-medium opacity-90">Bound Devices</div>
                            <div class="text-3xl font-bold mt-1">{{ $boundDevices }}</div>
                            <div class="text-xs opacity-80 mt-1">Currently active bindings</div>
                        </div>
                        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 dark:from-yellow-600 dark:to-yellow-700 p-4 rounded-lg text-white shadow-lg">
                            <div class="text-sm font-medium opacity-90">Active Devices (30d)</div>
                            <div class="text-3xl font-bold mt-1">{{ $activeDevices }}</div>
                            <div class="text-xs opacity-80 mt-1">Seen in last 30 days</div>
                        </div>
                        <div class="bg-gradient-to-r from-red-500 to-red-600 dark:from-red-600 dark:to-red-700 p-4 rounded-lg text-white shadow-lg">
                            <div class="text-sm font-medium opacity-90">Unbound Devices</div>
                            <div class="text-3xl font-bold mt-1">{{ $unboundDevices }}</div>
                            <div class="text-xs opacity-80 mt-1">Historical/unbound devices</div>
                        </div>
                    </div>

                    <!-- Admin filters -->
                    <div class="mb-6 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Filter Devices
                            </h4>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $devices->total() }} total devices
                                </span>
                                <button id="toggleAdvancedFilters" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                    </svg>
                                    Advanced
                                </button>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('devices.admin') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Status filter -->
                            <div class="space-y-2">
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <select name="status" id="status" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                    <option value="">All Statuses</option>
                                    <option value="bound" {{ request('status') === 'bound' ? 'selected' : '' }}>Bound</option>
                                    <option value="unbound" {{ request('status') === 'unbound' ? 'selected' : '' }}>Unbound</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active (30 days)</option>
                                </select>
                            </div>

                            <!-- Date range filter -->
                            <div class="space-y-2">
                                <label for="date_range" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range</label>
                                <select name="date_range" id="date_range" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                    <option value="">All Time</option>
                                    <option value="24h" {{ request('date_range') === '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                                    <option value="7d" {{ request('date_range') === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                                    <option value="30d" {{ request('date_range') === '30d' ? 'selected' : '' }}>Last 30 Days</option>
                                    <option value="90d" {{ request('date_range') === '90d' ? 'selected' : '' }}>Last 90 Days</option>
                                </select>
                            </div>

                            <!-- Search -->
                            <div class="space-y-2 md:col-span-2">
                                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </div>
                                        <input type="text" name="search" id="search" value="{{ request('search', '') }}"
                                               class="w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200"
                                               placeholder="Search by HWID, IP, username, or email...">
                                    </div>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 font-medium shadow-sm">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        Filter
                                    </button>
                                    <a href="{{ route('devices.admin') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 font-medium shadow-sm">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Advanced filters (hidden by default) -->
                        <div id="advancedFilters" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
                            <div class="space-y-2">
                                <label for="country_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country Code</label>
                                <input type="text" name="country_code" id="country_code" value="{{ request('country_code', '') }}"
                                       class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200"
                                       placeholder="e.g., US, CN, JP">
                            </div>
                            <div class="space-y-2">
                                <label for="min_reset_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min HWID Resets</label>
                                <input type="number" name="min_reset_count" id="min_reset_count" value="{{ request('min_reset_count', '') }}"
                                       class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200"
                                       placeholder="Minimum reset count">
                            </div>
                            <div class="space-y-2">
                                <label for="account_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Status</label>
                                <select name="account_status" id="account_status" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                    <option value="">All Accounts</option>
                                    <option value="active" {{ request('account_status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ request('account_status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>
                        </div>

                        <!-- Active filters badge -->
                        @if (request()->filled(['status', 'search', 'date_range', 'country_code', 'min_reset_count', 'account_status']) ||
                                request()->filled('status') ||
                                request()->filled('search') ||
                                request()->filled('date_range') ||
                                request()->filled('country_code') ||
                                request()->filled('min_reset_count') ||
                                request()->filled('account_status'))
                            <div class="mt-4 flex items-center space-x-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active filters:</span>
                                <div class="flex flex-wrap gap-2">
                                    @if (request()->filled('status'))
                                        @php
                                            $statusValue = request('status');
                                            $statusLabel = match ($statusValue) {
                                                'bound' => 'Bound',
                                                'unbound' => 'Unbound',
                                                'active' => 'Active (30 days)',
                                                default => ucfirst($statusValue),
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            Status: {{ $statusLabel }}
                                            <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="ml-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </a>
                                        </span>
                                    @endif
                                    @if (request()->filled('search'))
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            Search: "{{ request('search') }}"
                                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="ml-2 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </a>
                                        </span>
                                    @endif
                                    @if (request()->filled('date_range'))
                                        @php
                                            $dateRangeValue = request('date_range');
                                            $dateRangeLabel = match ($dateRangeValue) {
                                                '24h' => 'Last 24 Hours',
                                                '7d' => 'Last 7 Days',
                                                '30d' => 'Last 30 Days',
                                                '90d' => 'Last 90 Days',
                                                default => ucfirst($dateRangeValue),
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Date: {{ $dateRangeLabel }}
                                            <a href="{{ request()->fullUrlWithQuery(['date_range' => null]) }}" class="ml-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </a>
                                        </span>
                                    @endif
                                    @if (request()->filled('country_code'))
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Country: {{ strtoupper(request('country_code')) }}
                                            <a href="{{ request()->fullUrlWithQuery(['country_code' => null]) }}" class="ml-2 text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </a>
                                        </span>
                                    @endif
                                    @if (request()->filled('min_reset_count'))
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                            Min Resets: {{ request('min_reset_count') }}
                                            <a href="{{ request()->fullUrlWithQuery(['min_reset_count' => null]) }}" class="ml-2 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </a>
                                        </span>
                                    @endif
                                    @if (request()->filled('account_status'))
                                        @php
                                            $accountStatusValue = request('account_status');
                                            $accountStatusLabel = match ($accountStatusValue) {
                                                'active' => 'Active Accounts',
                                                'suspended' => 'Suspended Accounts',
                                                default => ucfirst($accountStatusValue),
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Account: {{ $accountStatusLabel }}
                                            <a href="{{ request()->fullUrlWithQuery(['account_status' => null]) }}" class="ml-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </a>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Bulk Actions -->
                    <div class="mb-4 flex items-center gap-2">
                        <select id="bulkAction" class="px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm">
                            <option value="">Bulk Actions</option>
                            <option value="unbind">Unbind Selected</option>
                            <option value="reset-hwid">Reset HWID for Selected</option>
                        </select>
                        <button id="applyBulkAction" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition text-sm" disabled>
                            Apply
                        </button>
                        <span id="selectedCount" class="text-sm text-gray-600 dark:text-gray-400">0 selected</span>
                    </div>

                    <!-- Devices table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 dark:text-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Account
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        HWID Hash
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        IP Address
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Country
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        First Seen
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Last Seen
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($devices as $device)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" name="selected_devices[]" value="{{ $device->id }}" class="device-checkbox rounded border-gray-300 dark:border-gray-600 text-blue-600 dark:text-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                                                        {{ $device->account->initials() }}
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $device->account->username }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $device->account->email }}
                                                    </div>
                                                    <div class="text-xs {{ $device->account->isSuspended() ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                                        {{ $device->account->isSuspended() ? 'Suspended' : 'Active' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 break-all">
                                            {{ $device->hwid_hash }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $device->ip_address }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $device->country_code ?? 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($device->isBound())
                                                <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-xs font-medium">
                                                    Currently Bound
                                                </span>
                                            @else
                                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-full text-xs font-medium">
                                                    Historical
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $device->first_seen_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $device->last_seen_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('accounts.show', $device->account) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300" title="View Account">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>
                                                @if($device->isBound())
                                                    <form action="{{ route('devices.admin.unbind', $device) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to unbind this device for user {{ $device->account->username }}?')">
                                                        @csrf
                                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" title="Unbind Device">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('devices.admin.reset-hwid', $device->account) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to reset HWID for user {{ $device->account->username }}? This will unbind all their devices.')">
                                                    @csrf
                                                    <button type="submit" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-300" title="Reset HWID">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-300">
                                            No devices found.
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Advanced filters toggle
                const toggleBtn = document.getElementById('toggleAdvancedFilters');
                const advancedFilters = document.getElementById('advancedFilters');

                if (toggleBtn && advancedFilters) {
                    toggleBtn.addEventListener('click', function() {
                        advancedFilters.classList.toggle('hidden');
                        const icon = this.querySelector('svg');
                        if (advancedFilters.classList.contains('hidden')) {
                            this.querySelector('span').textContent = 'Advanced';
                            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>';
                        } else {
                            this.querySelector('span').textContent = 'Basic';
                            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>';
                        }
                    });
                }

                // Bulk actions
                const selectAll = document.getElementById('selectAll');
                const deviceCheckboxes = document.querySelectorAll('.device-checkbox');
                const bulkAction = document.getElementById('bulkAction');
                const applyBulkAction = document.getElementById('applyBulkAction');
                const selectedCount = document.getElementById('selectedCount');

                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        deviceCheckboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                        updateSelectedCount();
                    });
                }

                if (deviceCheckboxes.length > 0) {
                    deviceCheckboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', updateSelectedCount);
                    });
                }

                function updateSelectedCount() {
                    const checkedCount = Array.from(deviceCheckboxes).filter(checkbox => checkbox.checked).length;
                    selectedCount.textContent = checkedCount + ' selected';

                    if (checkedCount > 0 && bulkAction.value) {
                        applyBulkAction.disabled = false;
                    } else {
                        applyBulkAction.disabled = true;
                    }
                }

                if (applyBulkAction) {
                    applyBulkAction.addEventListener('click', function() {
                        const action = bulkAction.value;
                        const selectedDevices = Array.from(deviceCheckboxes)
                            .filter(checkbox => checkbox.checked)
                            .map(checkbox => checkbox.value);

                        if (selectedDevices.length === 0) {
                            alert('Please select at least one device.');
                            return;
                        }

                        let confirmationMessage = '';
                        if (action === 'unbind') {
                            confirmationMessage = 'Are you sure you want to unbind ' + selectedDevices.length + ' devices?';
                        } else if (action === 'reset-hwid') {
                            confirmationMessage = 'Are you sure you want to reset HWID for ' + selectedDevices.length + ' accounts? This will unbind all their devices.';
                        }

                        if (confirm(confirmationMessage)) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = action === 'unbind'
                                ? '{{ route('devices.admin.bulk-unbind') }}'
                                : '{{ route('devices.admin.bulk-reset-hwid') }}';

                            const csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = '{{ csrf_token() }}';
                            form.appendChild(csrfToken);

                            selectedDevices.forEach(deviceId => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'device_ids[]';
                                input.value = deviceId;
                                form.appendChild(input);
                            });

                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }

                // Export functionality
                const exportBtn = document.getElementById('exportBtn');
                if (exportBtn) {
                    exportBtn.addEventListener('click', function() {
                        const url = new URL('{{ route('devices.admin.export') }}', window.location.origin);
                        const params = new URLSearchParams(window.location.search);
                        url.search = params.toString();
                        window.location.href = url.toString();
                    });
                }

                // Form cleanup
                const filterForm = document.querySelector('form[method="GET"]');
                if (filterForm) {
                    cleanupUrl();

                    filterForm.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const formData = new FormData(this);
                        const params = new URLSearchParams();

                        for (const [key, value] of formData.entries()) {
                            const trimmedValue = value.toString().trim();
                            if (trimmedValue !== '') {
                                params.append(key, trimmedValue);
                            }
                        }

                        const baseUrl = this.action.split('?')[0];
                        const queryString = params.toString();
                        const url = queryString ? `${baseUrl}?${queryString}` : baseUrl;

                        window.location.href = url;
                    });

                    const resetBtn = filterForm.querySelector('a[href*="devices.admin"]');
                    if (resetBtn) {
                        resetBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            window.location.href = this.href;
                        });
                    }
                }

                function cleanupUrl() {
                    const url = new URL(window.location);
                    const params = new URLSearchParams(url.search);
                    let hasChanges = false;

                    for (const [key, value] of params.entries()) {
                        if (value === '' || value.trim() === '') {
                            params.delete(key);
                            hasChanges = true;
                        }
                    }

                    if (hasChanges) {
                        const newUrl = params.toString() ?
                            `${url.pathname}?${params.toString()}` :
                            url.pathname;
                        window.history.replaceState({}, '', newUrl);
                    }
                }

                window.addEventListener('popstate', function() {
                    cleanupUrl();
                });
            });
        </script>
    @endpush
</x-app-layout>