<x-app-sidebar-layout>
    <x-slot name="header"> {{ __('Account Management') }} </x-slot>
    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"> <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <x-stat-card title="Total Accounts" :value="$statistics['total']" icon="users" iconColor="icon-blue" />
                <x-stat-card title="Active Accounts" :value="$statistics['active']" icon="success" iconColor="icon-green" />
                <x-stat-card title="Suspended" :value="$statistics['suspended']" icon="warning" iconColor="icon-red" />
                <x-stat-card title="Verified" :value="$statistics['verified']" icon="shield" iconColor="icon-purple" />
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100"> <!-- Header with actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h3 class="text-lg font-medium"> Account Management </h3>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white"> Account Management </h3>
                        <div class="flex gap-2"><a href="{{ route('accounts.create') }}"
                                class="btn btn-blue btn-sm">
                                Create
                                Account </a></div>
                    </div> <!-- filters -->
                    <x-filter-box :action="route('accounts.index')" :showTotal="true" :totalCount="$statistics['total']">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4"> <!-- Account Status -->
                            <div class="space-y-2"><label for="status"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account
                                    Status</label> <select name="status" id="status"
                                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                    <option value="">All Statuses</option>
                                    <option value="active"
                                        {{ $currentFilters['status'] === 'active' ? 'selected' : '' }}> Active
                                    </option>
                                    <option value="suspended"
                                        {{ $currentFilters['status'] === 'suspended' ? 'selected' : '' }}> Suspended
                                    </option>
                                    <option value="verified"
                                        {{ $currentFilters['status'] === 'verified' ? 'selected' : '' }}> Verified
                                    </option>
                                    <option value="unverified"
                                        {{ $currentFilters['status'] === 'unverified' ? 'selected' : '' }}> Unverified
                                    </option>
                                    <option value="2fa-enabled"
                                        {{ $currentFilters['status'] === '2fa-enabled' ? 'selected' : '' }}> 2FA Enabled
                                    </option>
                                </select></div> <!-- License Privilege -->
                            <div class="space-y-2"><label for="privilege"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">License
                                    Privilege</label> <select name="privilege" id="privilege"
                                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                    <option value="">All Privileges</option>
                                    @foreach ($privilegeOptions as $value => $label)
                                        @if ($value !== '')
                                            <option value="{{ $value }}"
                                                {{ $currentFilters['privilege'] === (string) $value ? 'selected' : '' }}>
                                                {{ $label }} </option>
                                        @endif
                                    @endforeach
                                </select></div> <!-- License Count -->
                            <div class="space-y-2"><label for="license_count"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">License
                                    Count</label> <select name="license_count" id="license_count"
                                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                    <option value="">Any</option>
                                    <option value="none"
                                        {{ $currentFilters['license_count'] === 'none' ? 'selected' : '' }}> No
                                        Licenses
                                    </option>
                                    <option value="has"
                                        {{ $currentFilters['license_count'] === 'has' ? 'selected' : '' }}> Has
                                        Licenses
                                    </option>
                                </select></div> <!-- Sort -->
                            <div class="space-y-2"><label for="sort"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort
                                    By</label>
                                <select name="sort" id="sort"
                                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200">
                                    <option value="created_at_desc"
                                        {{ $currentFilters['sort'] === 'created_at_desc' ? 'selected' : '' }}> Created
                                        (Newest First)
                                    </option>
                                    <option value="created_at_asc"
                                        {{ $currentFilters['sort'] === 'created_at_asc' ? 'selected' : '' }}> Created
                                        (Oldest First)
                                    </option>
                                    <option value="username_asc"
                                        {{ $currentFilters['sort'] === 'username_asc' ? 'selected' : '' }}> Username
                                        (A-Z)
                                    </option>
                                    <option value="username_desc"
                                        {{ $currentFilters['sort'] === 'username_desc' ? 'selected' : '' }}> Username
                                        (Z-A)
                                    </option>
                                    <option value="email_asc"
                                        {{ $currentFilters['sort'] === 'email_asc' ? 'selected' : '' }}> Email (A-Z)
                                    </option>
                                    <option value="email_desc"
                                        {{ $currentFilters['sort'] === 'email_desc' ? 'selected' : '' }}> Email (Z-A)
                                    </option>
                                    <option value="last_login_at_desc"
                                        {{ $currentFilters['sort'] === 'last_login_at_desc' ? 'selected' : '' }}> Last
                                        Login (Recent First)
                                    </option>
                                    <option value="last_login_at_asc"
                                        {{ $currentFilters['sort'] === 'last_login_at_asc' ? 'selected' : '' }}> Last
                                        Login (Oldest First)
                                    </option>
                                </select>
                            </div>
                        </div> <!-- Search Row -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end"> <!-- Search -->
                            <div class="space-y-2 md:col-span-8"><label for="search"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <x-icon name="search" class="h-4 w-4 text-gray-400" />
                                    </div>
                                    <input type="text" name="search" id="search"
                                        value="{{ $currentFilters['search'] }}"
                                        class="w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-gray-200 transition-all duration-200"
                                        placeholder="Search by username, email, or license key...">
                                </div>
                            </div> <!-- Action Buttons -->
                            <div class="space-y-2 md:col-span-4"><label
                                    class="block text-sm font-medium text-transparent">Actions</label>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="flex-1 btn btn-blue btn-sm flex items-center justify-center gap-2">
                                        <x-icon name="search" class="w-4 h-4" />
                                        Filter
                                    </button>
                                    <a href="{{ route('accounts.index') }}"
                                        class="btn btn-secondary btn-sm flex items-center justify-center gap-2">
                                        <x-icon name="reset" class="w-4 h-4" />
                                        Reset </a>
                                </div>
                            </div>
                        </div>
                        <!-- Active Filters --> @php $hasFilters = $currentFilters['status'] || $currentFilters['privilege'] || $currentFilters['license_count'] || $currentFilters['search'] || $currentFilters['sort'] !== 'created_at_desc'; @endphp @if ($hasFilters)
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-2"> <span
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300"> Active Filters
                                    </span> <a href="{{ route('accounts.index') }}"
                                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                                        Clear All </a></div>
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
                                        <x-filter-badge label="Status: {{ $statusLabel }}" color="blue"
                                            :removeUrl="request()->fullUrlWithQuery(['status' => null])" />
                                        @endif @if ($currentFilters['privilege'])
                                            @php
                                                $privilegeValue = $currentFilters['privilege'];
                                                $privilegeLabel = $privilegeOptions[$privilegeValue] ?? null;
                                            @endphp @if ($privilegeLabel)
                                                <x-filter-badge label="Privilege: {{ $privilegeLabel }}"
                                                    color="green" :removeUrl="request()->fullUrlWithQuery(['privilege' => null])" />
                                            @endif
                                            @endif @if ($currentFilters['license_count'])
                                                @php
                                                    $licenseCountValue = $currentFilters['license_count'];
                                                    $licenseCountLabel = match ($licenseCountValue) {
                                                        'none' => 'No Licenses',
                                                        'has' => 'Has Licenses',
                                                        default => ucfirst($licenseCountValue),
                                                    };
                                                @endphp
                                                <x-filter-badge label="Licenses: {{ $licenseCountLabel }}"
                                                    color="orange" :removeUrl="request()->fullUrlWithQuery([
                                                        'license_count' => null,
                                                    ])" />
                                                @endif @if ($currentFilters['search'])
                                                    <x-filter-badge label="Search: "{{ $currentFilters['search'] }}""
                                                        color="purple" :removeUrl="request()->fullUrlWithQuery(['search' => null])" />
                                                    @endif @if ($currentFilters['sort'] !== 'created_at_desc')
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
                                                        <x-filter-badge label="Sort: {{ $sortLabel }}"
                                                            color="yellow" :removeUrl="request()->fullUrlWithQuery([
                                                                'sort' => null,
                                                            ])" />
                                                    @endif
                                </div>
                            </div>
                        @endif
                    </x-filter-box> <!-- Accounts table -->
                    <x-data-table :headers="['User', 'Email', 'Status', 'Privilege', 'Licenses', 'Devices', 'Last Login', 'Actions']" :emptyColspan="8">
                        @foreach ($accounts as $account)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div
                                                class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                                {{ $account->initials() }} </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $account->username }} </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400"> ID:
                                                {{ $account->id }} </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <div class="max-w-[200px] truncate" title="{{ $account->email }}">
                                        <span class="px-1 rounded {{ $account->email_verified_at ? 'bg-green-100 dark:bg-green-800' : 'bg-red-100 dark:bg-red-800' }}">{{ $account->email }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    @if ($account->isCurrentlySuspended)
                                        <x-status-badge status="suspended" />
                                    @else
                                        <x-status-badge status="active" />
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    @php
                                        $privilege = $account->getPrivilegeLevel();
                                        $privilegeLabel = match ($privilege) {
                                            1 => 'standard',
                                            2 => 'upgrade',
                                            3 => 'ultimate',
                                            6 => 'tester',
                                            7 => 'staff',
                                            default => 'default',
                                        };
                                    @endphp
                                    <x-status-badge :status="$privilegeLabel" />
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $account->licenses_count }} </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $account->devices_count }} </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    @if ($account->last_login_at)
                                        {{ $account->last_login_at->diffForHumans() }} <br> <span
                                            class="text-xs text-gray-500 dark:text-gray-400">{{ $account->last_login_at->format('Y-m-d H:i') }}</span>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">Never</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium"><a
                                        href="{{ route('accounts.show', $account) }}"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                        View </a>
                                    @if ($account->isCurrentlySuspended)
                                        <span class="mx-1 text-gray-400">|</span>
                                        <form action="{{ route('accounts.unsuspend', $account) }}" method="POST"
                                            class="inline"> @csrf
                                            <button type="submit"
                                                class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300">
                                                Unsuspend
                                            </button>
                                        </form>
                                    @else
                                        <span class="mx-1 text-gray-400">|</span>
                                        <form action="{{ route('accounts.suspend', $account) }}" method="POST"
                                            class="inline"
                                            onsubmit="return confirm('Are you sure you want to suspend this account?')">
                                            @csrf
                                            <button type="submit"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                                Suspend
                                            </button>
                                        </form>
                                    @endif <span class="mx-1 text-gray-400">|</span>
                                    <form action="{{ route('accounts.destroy', $account) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this account? This action cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach @if ($accounts->count() === 0)
                                <tr>
                                    <td colspan="8"
                                        class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300"> No
                                        accounts found.
                                    </td>
                                </tr>
                            @endif
                    </x-data-table> <!-- Pagination -->
                    <div class="mt-4"> {{ $accounts->links() }} </div>
                </div>
            </div>
        </div>
    </div>
</x-app-sidebar-layout>
