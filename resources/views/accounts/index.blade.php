<x-app-sidebar-layout>
    <x-slot name="header"> {{ __('Account Management') }} </x-slot>
    @php
        $suspendAccountConfirmation = __('Are you sure you want to suspend this account?');
        $deleteAccountConfirmation = __('Are you sure you want to delete this account? This action cannot be undone.');
    @endphp

    <div> <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <x-stat-card :title="__('Total Accounts')" :value="$statistics['total']" icon="users" iconColor="icon-gray" />
                <x-stat-card :title="__('Active Accounts')" :value="$statistics['active']" icon="success" iconColor="icon-green" />
                <x-stat-card :title="__('Suspended')" :value="$statistics['suspended']" icon="warning" iconColor="icon-red" />
                <x-stat-card :title="__('Verified')" :value="$statistics['verified']" icon="shield" iconColor="icon-purple" />
            </div>
            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-xl border border-zinc-200/60 dark:border-zinc-700/60">
                <div class="p-6 text-zinc-900 dark:text-zinc-100"> <!-- Header with actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 lg:max-xl:flex-wrap">
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100"> {{ __('Account Management') }} </h3>
                        <div class="flex flex-wrap gap-2"><a href="{{ route('accounts.create') }}"
                                class="btn btn-secondary btn-sm">
                                {{ __('Create Account') }} </a></div>
                    </div> <!-- filters -->
                    <x-filter-box :action="route('accounts.index')" :showTotal="true" :totalCount="$statistics['total']" :title="__('Filter Accounts')">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div class="space-y-2">
                                <label for="status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Account Status') }}</label>
                                <select name="status" id="status" class="form-select form-select-enhanced form-pill py-2">
                                    <option value="">{{ __('All Statuses') }}</option>
                                    <option value="active" {{ $currentFilters['status'] === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="suspended" {{ $currentFilters['status'] === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                                    <option value="verified" {{ $currentFilters['status'] === 'verified' ? 'selected' : '' }}>{{ __('Verified') }}</option>
                                    <option value="unverified" {{ $currentFilters['status'] === 'unverified' ? 'selected' : '' }}>{{ __('Unverified') }}</option>
                                    <option value="2fa-enabled" {{ $currentFilters['status'] === '2fa-enabled' ? 'selected' : '' }}>{{ __('2FA Enabled') }}</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="privilege" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('License Privilege') }}</label>
                                <select name="privilege" id="privilege" class="form-select form-select-enhanced form-pill py-2">
                                    <option value="">{{ __('All Privileges') }}</option>
                                    @foreach ($privilegeOptions as $value => $label)
                                        @if ($value !== '')
                                            <option value="{{ $value }}" {{ $currentFilters['privilege'] === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="license_count" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('License Count') }}</label>
                                <select name="license_count" id="license_count" class="form-select form-select-enhanced form-pill py-2">
                                    <option value="">{{ __('Any') }}</option>
                                    <option value="none" {{ $currentFilters['license_count'] === 'none' ? 'selected' : '' }}>{{ __('No Licenses') }}</option>
                                    <option value="has" {{ $currentFilters['license_count'] === 'has' ? 'selected' : '' }}>{{ __('Has Licenses') }}</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="sort" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Sort By') }}</label>
                                <select name="sort" id="sort" class="form-select form-select-enhanced form-pill py-2">
                                    <option value="created_at_desc" {{ $currentFilters['sort'] === 'created_at_desc' ? 'selected' : '' }}>{{ __('Created (Newest First)') }}</option>
                                    <option value="created_at_asc" {{ $currentFilters['sort'] === 'created_at_asc' ? 'selected' : '' }}>{{ __('Created (Oldest First)') }}</option>
                                    <option value="username_asc" {{ $currentFilters['sort'] === 'username_asc' ? 'selected' : '' }}>{{ __('Username (A-Z)') }}</option>
                                    <option value="username_desc" {{ $currentFilters['sort'] === 'username_desc' ? 'selected' : '' }}>{{ __('Username (Z-A)') }}</option>
                                    <option value="email_asc" {{ $currentFilters['sort'] === 'email_asc' ? 'selected' : '' }}>{{ __('Email (A-Z)') }}</option>
                                    <option value="email_desc" {{ $currentFilters['sort'] === 'email_desc' ? 'selected' : '' }}>{{ __('Email (Z-A)') }}</option>
                                    <option value="last_login_at_desc" {{ $currentFilters['sort'] === 'last_login_at_desc' ? 'selected' : '' }}>{{ __('Last Login (Recent First)') }}</option>
                                    <option value="last_login_at_asc" {{ $currentFilters['sort'] === 'last_login_at_asc' ? 'selected' : '' }}>{{ __('Last Login (Oldest First)') }}</option>
                                </select>
                            </div>

                            <div class="space-y-2 lg:col-span-3">
                                <label for="search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Search') }}</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <x-icon name="search" class="h-4 w-4 text-zinc-400" />
                                    </div>
                                    <input type="text" name="search" id="search" value="{{ $currentFilters['search'] }}"
                                        class="form-input form-pill py-2 pl-10 pr-4" placeholder="{{ __('Search by username, email, or license key...') }}">
                                </div>
                            </div>

                            <div class="flex items-end gap-2 lg:justify-end">
                                <button type="submit" class="btn btn-secondary btn-sm flex items-center justify-center gap-2">
                                    <x-icon name="search" class="w-4 h-4" />
                                    {{ __('Filter') }}
                                </button>
                                <a href="{{ route('accounts.index') }}" class="btn btn-secondary btn-sm flex items-center justify-center gap-2">
                                    <x-icon name="reset" class="w-4 h-4" />
                                    {{ __('Reset') }}
                                </a>
                            </div>
                        </div>

                        @php
                            $hasFilters =
                                $currentFilters['status'] ||
                                $currentFilters['privilege'] ||
                                $currentFilters['license_count'] ||
                                $currentFilters['search'] ||
                                $currentFilters['sort'] !== 'created_at_desc';
                        @endphp

                        @if ($hasFilters)
                            <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Active Filters') }}</span>
                                    <a href="{{ route('accounts.index') }}"
                                        class="text-sm text-zinc-600 transition-colors hover:text-zinc-800 dark:text-zinc-300 dark:hover:text-zinc-100">
                                        {{ __('Clear All') }}
                                    </a>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if ($currentFilters['status'])
                                        @php
                                            $statusValue = $currentFilters['status'];
                                            $statusLabel = match ($statusValue) {
                                                'active' => __('Active'),
                                                'suspended' => __('Suspended'),
                                                'verified' => __('Verified'),
                                                'unverified' => __('Unverified'),
                                                '2fa-enabled' => __('2FA Enabled'),
                                                default => ucfirst($statusValue),
                                            };
                                        @endphp
                                        <x-filter-badge :label="__('Status:').' '.$statusLabel" color="gray"
                                            :removeUrl="request()->fullUrlWithQuery(['status' => null])" />
                                    @endif

                                    @if ($currentFilters['privilege'])
                                        @php
                                            $privilegeValue = $currentFilters['privilege'];
                                            $privilegeLabel = $privilegeOptions[$privilegeValue] ?? null;
                                        @endphp
                                        @if ($privilegeLabel)
                                            <x-filter-badge :label="__('Privilege:').' '.$privilegeLabel" color="green"
                                                :removeUrl="request()->fullUrlWithQuery(['privilege' => null])" />
                                        @endif
                                    @endif

                                    @if ($currentFilters['license_count'])
                                        @php
                                            $licenseCountValue = $currentFilters['license_count'];
                                            $licenseCountLabel = match ($licenseCountValue) {
                                                'none' => __('No Licenses'),
                                                'has' => __('Has Licenses'),
                                                default => ucfirst($licenseCountValue),
                                            };
                                        @endphp
                                        <x-filter-badge :label="__('Licenses:').' '.$licenseCountLabel" color="gray"
                                            :removeUrl="request()->fullUrlWithQuery(['license_count' => null])" />
                                    @endif

                                    @if ($currentFilters['search'])
                                        <x-filter-badge :label="__('Search:').' &quot;'.$currentFilters['search'].'&quot;'" color="purple"
                                            :removeUrl="request()->fullUrlWithQuery(['search' => null])" />
                                    @endif

                                    @if ($currentFilters['sort'] !== 'created_at_desc')
                                        @php
                                            $sortValue = $currentFilters['sort'];
                                            $sortLabel = match ($sortValue) {
                                                'created_at_desc' => __('Created (Newest First)'),
                                                'created_at_asc' => __('Created (Oldest First)'),
                                                'username_asc' => __('Username (A-Z)'),
                                                'username_desc' => __('Username (Z-A)'),
                                                'email_asc' => __('Email (A-Z)'),
                                                'email_desc' => __('Email (Z-A)'),
                                                'last_login_at_desc' => __('Last Login (Recent First)'),
                                                'last_login_at_asc' => __('Last Login (Oldest First)'),
                                                default => ucfirst($sortValue),
                                            };
                                        @endphp
                                        <x-filter-badge :label="__('Sort:').' '.$sortLabel" color="yellow"
                                            :removeUrl="request()->fullUrlWithQuery(['sort' => null])" />
                                    @endif
                                </div>
                            </div>
                        @endif
                    </x-filter-box> <!-- Accounts table -->
                    <x-data-table :headers="[__('User'), __('Email'), __('Status'), __('Privilege'), __('Licenses'), __('Devices'), __('Last Login'), __('Actions')]" :emptyColspan="8">
                        @foreach ($accounts as $account)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div
                                                class="h-8 w-8 rounded-full bg-zinc-500 flex items-center justify-center text-white font-bold text-sm">
                                                {{ $account->initials() }} </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $account->username }} </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400"> {{ __('ID:') }}
                                                {{ $account->id }} </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <div class="max-w-[200px] truncate" title="{{ $account->email }}">
                                        <span class="px-1 rounded-lg {{ $account->email_verified_at ? 'bg-green-100 dark:bg-green-800' : 'bg-red-100 dark:bg-red-800' }}">{{ $account->email }}</span>
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
                                         <span class="text-gray-500 dark:text-gray-400">{{ __('Never') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium"><a
                                        href="{{ route('accounts.show', $account) }}"
                                        class="text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-zinc-100">
                                         {{ __('View') }} </a>
                                    @if ($account->isCurrentlySuspended)
                                        <span class="mx-1 text-gray-400">|</span>
                                        <form action="{{ route('accounts.unsuspend', $account) }}" method="POST"
                                            class="inline"> @csrf
                                            <button type="submit"
                                                class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300">
                                                 {{ __('Unsuspend') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="mx-1 text-gray-400">|</span>
                                        <form action="{{ route('accounts.suspend', $account) }}" method="POST"
                                            class="inline"
                                             onsubmit="return confirm('{{ $suspendAccountConfirmation }}')">
                                            @csrf
                                            <button type="submit"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                                 {{ __('Suspend') }}
                                            </button>
                                        </form>
                                    @endif <span class="mx-1 text-gray-400">|</span>
                                    <form action="{{ route('accounts.destroy', $account) }}" method="POST"
                                        class="inline"
                                         onsubmit="return confirm('{{ $deleteAccountConfirmation }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                             {{ __('Delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach @if ($accounts->count() === 0)
                                <tr>
                                    <td colspan="8"
                                        class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300"> {{ __('No accounts found.') }}
                                    </td>
                                </tr>
                            @endif
                    </x-data-table> <!-- Pagination -->
                    <div class="mt-4">
                        <x-pagination :paginator="$accounts" />
                    </div>
                </div>
        </div>
    </div>
</x-app-sidebar-layout>
