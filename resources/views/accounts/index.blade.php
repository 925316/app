<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Account Management') }}
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-500/20 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Accounts</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $statistics['total'] }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-500/20 rounded-full">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Accounts</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $statistics['active'] }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-500/20 rounded-full">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Suspended</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $statistics['suspended'] }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-500/20 rounded-full">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Verified</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $statistics['verified'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header with actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h3 class="text-lg font-medium">
                            Account Management
                        </h3>

                        <div class="flex gap-2">
                            <a href="{{ route('accounts.create') }}"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                Create Account
                            </a>
                        </div>
                    </div>

                    <!-- filters -->
                    <div
                        class="mb-6 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                        <form method="GET" action="{{ route('accounts.index') }}" data-clean-form="true" data-default-values="sort:created_at_desc">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                <!-- Account Status -->
                                <div class="space-y-2">
                                    <label for="status"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account
                                        Status</label>
                                    <select name="status" id="status"
                                        class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                        <option value="">All Statuses</option>
                                        <option value="active"
                                            {{ $currentFilters['status'] === 'active' ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="suspended"
                                            {{ $currentFilters['status'] === 'suspended' ? 'selected' : '' }}>
                                            Suspended
                                        </option>
                                        <option value="verified"
                                            {{ $currentFilters['status'] === 'verified' ? 'selected' : '' }}>
                                            Verified
                                        </option>
                                        <option value="unverified"
                                            {{ $currentFilters['status'] === 'unverified' ? 'selected' : '' }}>
                                            Unverified
                                        </option>
                                        <option value="2fa-enabled"
                                            {{ $currentFilters['status'] === '2fa-enabled' ? 'selected' : '' }}>
                                            2FA Enabled
                                        </option>
                                    </select>
                                </div>

                                <!-- License Privilege -->
                                <div class="space-y-2">
                                    <label for="privilege"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">License
                                        Privilege</label>
                                    <select name="privilege" id="privilege"
                                        class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                        <option value="">All Privileges</option>
                                        @foreach ($privilegeOptions as $value => $label)
                                            @if ($value !== '')
                                                <option value="{{ $value }}"
                                                    {{ $currentFilters['privilege'] === (string) $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <!-- License Count -->
                                <div class="space-y-2">
                                    <label for="license_count"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">License
                                        Count</label>
                                    <select name="license_count" id="license_count"
                                        class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                        <option value="">Any</option>
                                        <option value="none"
                                            {{ $currentFilters['license_count'] === 'none' ? 'selected' : '' }}>
                                            No Licenses
                                        </option>
                                        <option value="has"
                                            {{ $currentFilters['license_count'] === 'has' ? 'selected' : '' }}>
                                            Has Licenses
                                        </option>
                                    </select>
                                </div>

                                <!-- Sort -->
                                <div class="space-y-2">
                                    <label for="sort"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort
                                        By</label>
                                    <select name="sort" id="sort"
                                        class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                        <option value="created_at_desc"
                                            {{ $currentFilters['sort'] === 'created_at_desc' ? 'selected' : '' }}>
                                            Created (Newest First)
                                        </option>
                                        <option value="created_at_asc"
                                            {{ $currentFilters['sort'] === 'created_at_asc' ? 'selected' : '' }}>
                                            Created (Oldest First)
                                        </option>
                                        <option value="username_asc"
                                            {{ $currentFilters['sort'] === 'username_asc' ? 'selected' : '' }}>
                                            Username (A-Z)
                                        </option>
                                        <option value="username_desc"
                                            {{ $currentFilters['sort'] === 'username_desc' ? 'selected' : '' }}>
                                            Username (Z-A)
                                        </option>
                                        <option value="email_asc"
                                            {{ $currentFilters['sort'] === 'email_asc' ? 'selected' : '' }}>
                                            Email (A-Z)
                                        </option>
                                        <option value="email_desc"
                                            {{ $currentFilters['sort'] === 'email_desc' ? 'selected' : '' }}>
                                            Email (Z-A)
                                        </option>
                                        <option value="last_login_at_desc"
                                            {{ $currentFilters['sort'] === 'last_login_at_desc' ? 'selected' : '' }}>
                                            Last Login (Recent First)
                                        </option>
                                        <option value="last_login_at_asc"
                                            {{ $currentFilters['sort'] === 'last_login_at_asc' ? 'selected' : '' }}>
                                            Last Login (Oldest First)
                                        </option>
                                    </select>
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
                                            placeholder="Search by username, email, or license key...">
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
                                        <a href="{{ route('accounts.index') }}"
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
                                    $currentFilters['status'] ||
                                    $currentFilters['privilege'] ||
                                    $currentFilters['license_count'] ||
                                    $currentFilters['search'] ||
                                    $currentFilters['sort'] !== 'created_at_desc';
                            @endphp

                            @if ($hasFilters)
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Active Filters
                                        </span>
                                        <a href="{{ route('accounts.index') }}"
                                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                                            Clear All
                                        </a>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @if ($currentFilters['status'])
                                            @php
                                                $statusValue = $currentFilters['status'];
                                                $statusLabel = match ($statusValue) {
                                                    'active' => 'Active',
                                                    'suspended' => 'Suspended',
                                                    'verified' => 'Verified',
                                                    'unverified' => 'Unverified',
                                                    '2fa-enabled' => '2FA Enabled',
                                                    default => ucfirst($statusValue),
                                                };
                                            @endphp
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
                                        @if ($currentFilters['privilege'])
                                            @php
                                                $privilegeValue = $currentFilters['privilege'];
                                                $privilegeLabel = $privilegeOptions[$privilegeValue] ?? null;
                                            @endphp
                                            @if ($privilegeLabel)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    Privilege: {{ $privilegeLabel }}
                                                    <a href="{{ request()->fullUrlWithQuery(['privilege' => null]) }}"
                                                        class="ml-1.5 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </a>
                                                </span>
                                            @endif
                                        @endif
                                        @if ($currentFilters['license_count'])
                                            @php
                                                $licenseCountValue = $currentFilters['license_count'];
                                                $licenseCountLabel = match ($licenseCountValue) {
                                                    'none' => 'No Licenses',
                                                    'has' => 'Has Licenses',
                                                    default => ucfirst($licenseCountValue),
                                                };
                                            @endphp
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                Licenses: {{ $licenseCountLabel }}
                                                <a href="{{ request()->fullUrlWithQuery(['license_count' => null]) }}"
                                                    class="ml-1.5 text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-200">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </a>
                                            </span>
                                        @endif
                                        @if ($currentFilters['search'])
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                Search: "{{ $currentFilters['search'] }}"
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
                                        @if ($currentFilters['sort'] !== 'created_at_desc')
                                            @php
                                                $sortValue = $currentFilters['sort'];
                                                $sortLabel = match ($sortValue) {
                                                    'created_at_desc' => 'Created (Newest First)',
                                                    'created_at_asc' => 'Created (Oldest First)',
                                                    'username_asc' => 'Username (A-Z)',
                                                    'username_desc' => 'Username (Z-A)',
                                                    'email_asc' => 'Email (A-Z)',
                                                    'email_desc' => 'Email (Z-A)',
                                                    'last_login_at_desc' => 'Last Login (Recent First)',
                                                    'last_login_at_asc' => 'Last Login (Oldest First)',
                                                    default => ucfirst($sortValue),
                                                };
                                            @endphp
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                Sort: {{ $sortLabel }}
                                                <a href="{{ request()->fullUrlWithQuery(['sort' => null]) }}"
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

                    <!-- Accounts table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Privilege
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Licenses
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Devices
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Last Login
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($accounts as $account)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div
                                                        class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                                        {{ $account->initials() }}
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $account->username }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        ID: {{ $account->id }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $account->email }}
                                            @if ($account->email_verified_at)
                                                <span
                                                    class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                    Verified
                                                </span>
                                            @else
                                                <span
                                                    class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                    Unverified
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            @if ($account->isCurrentlySuspended)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                    Suspended
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                    Active
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            @php
                                                $privilege = $account->getPrivilegeLevel();
                                                $privilegeLabel = match ($privilege) {
                                                    1 => 'Standard',
                                                    2 => 'Upgrade',
                                                    3 => 'Ultimate',
                                                    6 => 'Tester',
                                                    7 => 'Staff',
                                                    default => 'None',
                                                };
                                                $privilegeColor = match ($privilege) {
                                                    1
                                                        => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                                    2
                                                        => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                                    3
                                                        => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                                                    6
                                                        => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                    7 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                                    default
                                                        => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                };
                                            @endphp
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $privilegeColor }}">
                                                {{ $privilegeLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $account->licenses_count }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $account->devices_count }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            @if ($account->last_login_at)
                                                {{ $account->last_login_at->diffForHumans() }}
                                                <br>
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400">{{ $account->last_login_at->format('Y-m-d H:i') }}</span>
                                            @else
                                                <span class="text-gray-500 dark:text-gray-400">Never</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('accounts.show', $account) }}"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                                View
                                            </a>
                                            <span class="mx-1 text-gray-400">|</span>
                                            <a href="{{ route('accounts.edit', $account) }}"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                Edit
                                            </a>
                                            @if ($account->isCurrentlySuspended)
                                                <span class="mx-1 text-gray-400">|</span>
                                                <form action="{{ route('accounts.unsuspend', $account) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300">
                                                        Unsuspend
                                                    </button>
                                                </form>
                                            @else
                                                <span class="mx-1 text-gray-400">|</span>
                                                <button onclick="openSuspendModal({{ $account->id }})"
                                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                                    Suspend
                                                </button>
                                            @endif
                                            <span class="mx-1 text-gray-400">|</span>
                                            <form action="{{ route('accounts.destroy', $account) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('Are you sure you want to delete this account? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                                            No accounts found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $accounts->links() }}
                    </div>

                    <!-- Suspend Modal -->
                    <div id="suspendModal"
                        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden"
                        style="z-index: 9999;">
                        <div
                            class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                            <div class="mt-3">
                                <div
                                    class="flex items-center justify-center mx-auto h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="mt-2 text-center">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Suspend
                                        Account</h3>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Enter suspension details
                                        for this account.
                                    </p>
                                </div>
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
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration
                                            (days) -
                                            Optional</label>
                                        <input type="number" name="duration" id="suspend_duration" min="1"
                                            max="365"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                    </div>
                                    <div class="flex justify-end space-x-2">
                                        <button type="button" onclick="closeSuspendModal()"
                                            class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                                            Suspend
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>

</x-app-sidebar-layout>
