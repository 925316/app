<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Account Management') }}
    </x-slot>

    <div class="bg-white/80 dark:bg-cool-800/80 backdrop-blur-sm rounded-xl shadow-sm border border-cool-200/50 dark:border-cool-700/50 p-6">
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
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                    </path>
                                </svg>
                                Filter Accounts
                            </h4>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $accounts->total() }} total accounts
                                </span>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('accounts.index') }}">

                            <!-- Status & Privilege Row -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                <!-- Status filter with checkboxes -->
                                <div class="space-y-2 md:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ([
                                            'active' => 'Active',
                                            'suspended' => 'Suspended',
                                            'verified' => 'Verified',
                                            'unverified' => 'Unverified',
                                        ] as $value => $label)
                                            <label class="inline-flex items-center space-x-2 cursor-pointer">
                                                <input type="checkbox" name="status[]" value="{{ $value }}"
                                                    @if (is_array($currentFilters['status']) && in_array($value, $currentFilters['status'])) checked @endif
                                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Privilege filter -->
                                <div class="space-y-2 md:col-span-1">
                                    <label for="privilege"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Privilege</label>
                                    <select name="privilege" id="privilege"
                                        class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                        @foreach ($privilegeOptions as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ $currentFilters['privilege'] === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Sort with direction -->
                                <div class="space-y-2 md:col-span-1">
                                    <label for="sort"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort By</label>
                                    <div class="flex gap-2">
                                        <select name="sort" id="sort"
                                            class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                            <option value="created_at"
                                                {{ $currentFilters['sort'] === 'created_at' ? 'selected' : '' }}>
                                                Created
                                            </option>
                                            <option value="username"
                                                {{ $currentFilters['sort'] === 'username' ? 'selected' : '' }}>
                                                Username
                                            </option>
                                            <option value="email"
                                                {{ $currentFilters['sort'] === 'email' ? 'selected' : '' }}>
                                                Email
                                            </option>
                                            <option value="last_login_at"
                                                {{ $currentFilters['sort'] === 'last_login_at' ? 'selected' : '' }}>
                                                Last Login
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
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
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
                                $hasFilters = request()->filled('status') ||
                                    request()->filled('privilege') ||
                                    request()->filled('search') ||
                                    request()->filled('sort');
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
                                        @if (request()->filled('status'))
                                            @php
                                                $statusValues = request('status');
                                                if (!is_array($statusValues)) {
                                                    $statusValues = [$statusValues];
                                                }
                                            @endphp
                                            @foreach ($statusValues as $statusValue)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    Status: {{ ucfirst($statusValue) }}
                                                    <a href="{{ request()->fullUrlWithQuery(['status' => array_diff($statusValues, [$statusValue]) ?: null]) }}"
                                                        class="ml-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </a>
                                                </span>
                                            @endforeach
                                        @endif
                                        @if (request()->filled('privilege'))
                                            @php
                                                $privilegeValue = request('privilege');
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
                                                    'created_at' => 'Created',
                                                    'username' => 'Username',
                                                    'email' => 'Email',
                                                    'last_login_at' => 'Last Login',
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
                                                    7 => 'Staff/Admin',
                                                    default => 'None',
                                                };
                                                $privilegeColor = match ($privilege) {
                                                    1 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                                    2 => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                                    3 => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                                                    6 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                    7 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
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
                                                <form action="{{ route('accounts.unsuspend', $account) }}" method="POST"
                                                    class="inline">
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
    <div id="suspendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden"
        style="z-index: 9999;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-center mx-auto h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                </div>
                <div class="mt-2 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Suspend Account</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Enter suspension details for this account.
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
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration (days) -
                            Optional</label>
                        <input type="number" name="duration" id="suspend_duration" min="1" max="365"
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
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

                    const resetBtn = filterForm.querySelector('a[href*="accounts.index"]');
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

            let suspendAccountId = null;

            function openSuspendModal(accountId) {
                suspendAccountId = accountId;
                document.getElementById('suspendModal').classList.remove('hidden');
                document.getElementById('suspendForm').action = `/accounts/${accountId}/suspend`;
            }

            function closeSuspendModal() {
                document.getElementById('suspendModal').classList.add('hidden');
                suspendAccountId = null;
            }

            // Close modal when clicking outside
            document.getElementById('suspendModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeSuspendModal();
                }
            });

            // Close modal on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeSuspendModal();
                }
            });
        </script>
    @endpush
</x-app-sidebar-layout>
