<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Licenses') }}
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (Auth::user()->hasPrivilege(7))
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-500/20 rounded-full">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Licenses</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $statistics['total'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-500/20 rounded-full">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $statistics['active'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-red-500/20 rounded-full">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expired</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $statistics['expired'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="p-3 bg-yellow-500/20 rounded-full">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Unassigned</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $statistics['unassigned'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header with actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h3 class="text-lg font-medium">
                            @if ($isAdmin ?? false)
                                All Licenses
                            @else
                                My Licenses
                            @endif
                        </h3>

                        @if ($isAdmin ?? false)
                            <div class="flex gap-2">
                                <a href="{{ route('licenses.create') }}"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                    Create License
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- License Activation Form for Regular Users -->
                    @if (!$isAdmin ?? false)
                        <div
                            class="mb-8 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/50 dark:to-blue-800/50 p-6 rounded-xl border border-blue-200/50 dark:border-blue-700/50 shadow-sm">
                            <div class="flex items-start space-x-4">
                                <div class="p-3 bg-blue-500/20 rounded-full">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-2">Activate
                                        License</h4>
                                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                                        Enter your license key below to activate premium features. License keys follow
                                        the format: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX
                                    </p>

                                    <form method="POST" action="{{ route('licenses.activate-by-key') }}"
                                        class="space-y-4">
                                        @csrf

                                        <div>
                                            <label for="license_key"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                License Key
                                            </label>
                                            <input type="text" id="license_key" name="license_key"
                                                value="{{ old('license_key') }}"
                                                placeholder="XXXXX-XXXXX-XXXXX-XXXXX-XXXXX"
                                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-200 text-center font-mono text-lg tracking-wider uppercase @error('license_key') border-red-500 @enderror"
                                                maxlength="29" required {{-- pattern="^[A-Z0-9]{5}-[0-9A-F]{5}-[A-Z2-7]{5}-[A-Z3-8]{5}-[A-Z0-9]{5}$" --}}
                                                title="License key must be in the format: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX">
                                            @error('license_key')
                                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div class="flex justify-end">
                                            <button type="submit"
                                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 shadow-md font-medium">
                                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                                    </path>
                                                </svg>
                                                Activate License
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($isAdmin ?? false)
                        <!-- Admin filters -->
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
                                    Filter Licenses
                                </h4>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $licenses->total() }} total licenses
                                    </span>
                                </div>
                            </div>

                            <form method="GET" action="{{ route('licenses.index') }}" data-clean-form="true"
                                class="grid grid-cols-1 md:grid-cols-4 gap-4">

                                <!-- Status filter -->
                                <div class="space-y-2">
                                    <label for="status"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <select name="status" id="status"
                                        class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                        <option value="">All Statuses</option>
                                        @foreach ($statusOptions as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ request('status', '') === (string) $value ? 'selected' : '' }}>
                                                {{ ucfirst($label) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Privilege filter -->
                                <div class="space-y-2">
                                    <label for="privilege"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Privilege</label>
                                    <select name="privilege" id="privilege"
                                        class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                        <option value="">All Privileges</option>
                                        @foreach ($privilegeOptions as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ request('privilege', '') === (string) $value ? 'selected' : '' }}>
                                                {{ ucfirst($label) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Search -->
                                <div class="space-y-2 md:col-span-2">
                                    <label for="search"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-4 w-4 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </div>
                                            <input type="text" name="search" id="search"
                                                value="{{ request('search', '') }}"
                                                class="w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200"
                                                placeholder="Search by key or username...">
                                        </div>
                                        <button type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 font-medium shadow-sm">
                                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                            Filter
                                        </button>
                                        <a href="{{ route('licenses.index') }}"
                                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 font-medium shadow-sm">
                                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                            Reset
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <!-- Active filters badge -->
                            @if (request()->filled(['status', 'privilege', 'search']) ||
                                    request()->filled('status') ||
                                    request()->filled('privilege') ||
                                    request()->filled('search'))
                                <div class="mt-4 flex items-center space-x-3">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active
                                        filters:</span>
                                    <div class="flex flex-wrap gap-2">
                                        @if (request()->filled('status'))
                                            @php
                                                $statusValue = request('status');
                                                $statusLabel = $statusOptions[$statusValue] ?? null;
                                            @endphp
                                            @if ($statusLabel)
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    Status: {{ ucfirst($statusLabel) }}
                                                    <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}"
                                                        class="ml-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </a>
                                                </span>
                                            @endif
                                        @endif
                                        @if (request()->filled('privilege'))
                                            @php
                                                $privilegeValue = request('privilege');
                                                $privilegeLabel = $privilegeOptions[$privilegeValue] ?? null;
                                            @endphp
                                            @if ($privilegeLabel)
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    Privilege: {{ ucfirst($privilegeLabel) }}
                                                    <a href="{{ request()->fullUrlWithQuery(['privilege' => null]) }}"
                                                        class="ml-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
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
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                Search: "{{ request('search') }}"
                                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}"
                                                    class="ml-2 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-200">
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
                        </div>
                    @endif

                    <!-- Licenses table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        License Key
                                    </th>
                                    @if ($isAdmin ?? false)
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Account
                                        </th>
                                    @endif
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Privilege
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Expires
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($licenses as $license)
                                    <tr>
                                        <td
                                            class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $license->key }}
                                        </td>
                                        @if ($isAdmin ?? false)
                                            <td
                                                class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $license->account?->username ?? 'Unassigned' }}
                                            </td>
                                        @endif
                                        <td
                                            class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $license->getPrivilegeTextAttribute() }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            <span
                                                class="px-2 py-0.5 rounded text-xs font-medium {{ $license->getStatusColorAttribute() }}">
                                                {{ $license->getStatusTextAttribute() }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $license->expires_at->format('Y-m-d') }}
                                            @if ($license->isActive() && !$license->isExpired())
                                                <br>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    ({{ $license->daysUntilExpiry() }} days)
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('licenses.show', $license) }}"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                                View
                                            </a>
                                            @if ($isAdmin ?? false)
                                                <span class="mx-1 text-gray-400">|</span>
                                                <a href="{{ route('licenses.edit', $license) }}"
                                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                    Edit
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isAdmin ? 7 : 6 }}"
                                            class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                                            No licenses found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $licenses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-sidebar-layout>
