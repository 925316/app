<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Licenses') }}
        </h2>
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                                maxlength="29" required
                                                pattern="^[A-Z0-9]{5}-[0-9A-F]{5}-[A-Z2-7]{5}-[A-Z3-8]{5}-[A-Z0-9]{5}$"
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
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <form method="GET" action="{{ route('licenses.index') }}"
                                class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                <div>
                                    <label for="status"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                    <select name="status" id="status"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        <option value="">All Statuses</option>
                                        @foreach ($statusOptions as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ request('status') == $value ? 'selected' : '' }}>
                                                {{ ucfirst($label) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="privilege"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Privilege</label>
                                    <select name="privilege" id="privilege"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        <option value="">All Privileges</option>
                                        @foreach ($privilegeOptions as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ request('privilege') == $value ? 'selected' : '' }}>
                                                {{ ucfirst($label) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="search"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                                    <div class="flex gap-2">
                                        <input type="text" name="search" id="search"
                                            value="{{ request('search') }}"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                            placeholder="Key or username">
                                        <button type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition whitespace-nowrap">
                                            Filter
                                        </button>
                                        <a href="{{ route('licenses.index') }}"
                                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition whitespace-nowrap">
                                            Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- Licenses table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        License Key
                                    </th>
                                    @if ($isAdmin ?? false)
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Account
                                        </th>
                                    @endif
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Privilege
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Expires
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($licenses as $license)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $license->key }}
                                        </td>
                                        @if ($isAdmin ?? false)
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $license->account?->username ?? 'Unassigned' }}
                                            </td>
                                        @endif
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $license->getPrivilegeTextAttribute() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-medium {{ $license->getStatusColorAttribute() }}">
                                                {{ $license->getStatusTextAttribute() }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $license->expires_at->format('Y-m-d') }}
                                            @if ($license->isActive() && !$license->isExpired())
                                                ({{ $license->daysUntilExpiry() }} days)
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('licenses.show', $license) }}"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                                View
                                            </a>
                                            @if ($isAdmin ?? false)
                                                <span class="mx-2 text-gray-400">|</span>
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
                                            class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-300">
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
</x-app-layout>
