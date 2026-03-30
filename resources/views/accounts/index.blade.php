<x-app-sidebar-layout>
    <x-slot name="header">{{ __('Account Management') }}</x-slot>

    <x-slot name="subheader">
        {{ __('Review account health, filter the list precisely, and keep destructive actions intact.') }}
    </x-slot>

    @php
        $hasFilters = $currentFilters['status'] || $currentFilters['privilege'] || $currentFilters['license_count'] || $currentFilters['search'] || $currentFilters['sort'] !== 'created_at_desc';
    @endphp

    <div class="space-y-6" data-page="accounts-index">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Account statistics') }}">
            <x-stat-card :title="__('Total Accounts')" :value="$statistics['total']" icon="users" iconColor="icon-blue" />
            <x-stat-card :title="__('Active Accounts')" :value="$statistics['active']" icon="success" iconColor="icon-green" />
            <x-stat-card :title="__('Suspended')" :value="$statistics['suspended']" icon="warning" iconColor="icon-red" />
            <x-stat-card :title="__('Verified')" :value="$statistics['verified']" icon="shield" iconColor="icon-purple" />
        </section>

        <section class="card-shell space-y-6" data-accounts-panel>
            <div class="app-toolbar" data-accounts-toolbar>
                <div>
                    <p class="section-kicker">{{ __('Directory') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Account directory') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Scan account state, usage, and recent access without changing routes, semantics, or query behavior.') }}</p>
                </div>

                <div class="app-toolbar-actions">
                    <a href="{{ route('accounts.create') }}" class="btn btn-primary btn-sm gap-2">
                        <x-icon name="plus" class="h-4 w-4" />
                        {{ __('Create Account') }}
                    </a>
                </div>
            </div>

            <x-filter-box :action="route('accounts.index')" :totalCount="$statistics['total']" :title="__('Filter accounts')">
                <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-12">
                    <div class="space-y-2 md:col-span-7 xl:col-span-6">
                        <label for="search" class="form-label">{{ __('Search') }}</label>
                        <x-input-with-icon id="search" name="search" type="text" :value="$currentFilters['search']"
                            :placeholder="__('Search by username, email, or license key...')" icon="search" />
                    </div>

                    <div class="space-y-2 md:col-span-5 xl:col-span-3">
                        <label for="sort" class="form-label">{{ __('Sort By') }}</label>
                        <select name="sort" id="sort" class="form-select">
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

                    <div class="space-y-2 md:col-span-12 xl:col-span-3 filter-box-actions">
                        <span class="form-label text-transparent">{{ __('Actions') }}</span>
                        <div class="form-actions-cluster">
                            <button type="submit" class="btn btn-primary btn-sm flex-1 justify-center gap-2 xl:flex-none">
                                <x-icon name="search" class="h-4 w-4" />
                                {{ __('Filter') }}
                            </button>
                            <a href="{{ route('accounts.index') }}" class="btn btn-secondary btn-sm justify-center gap-2">
                                <x-icon name="reset" class="h-4 w-4" />
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="form-divider grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div class="space-y-2">
                        <label for="status" class="form-label">{{ __('Account Status') }}</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="active" {{ $currentFilters['status'] === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="suspended" {{ $currentFilters['status'] === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                            <option value="verified" {{ $currentFilters['status'] === 'verified' ? 'selected' : '' }}>{{ __('Verified') }}</option>
                            <option value="unverified" {{ $currentFilters['status'] === 'unverified' ? 'selected' : '' }}>{{ __('Unverified') }}</option>
                            <option value="2fa-enabled" {{ $currentFilters['status'] === '2fa-enabled' ? 'selected' : '' }}>{{ __('2FA Enabled') }}</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="privilege" class="form-label">{{ __('License Privilege') }}</label>
                        <select name="privilege" id="privilege" class="form-select">
                            <option value="">{{ __('All Privileges') }}</option>
                            @foreach ($privilegeOptions as $value => $label)
                                @if ($value !== '')
                                    <option value="{{ $value }}" {{ $currentFilters['privilege'] === (string) $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="license_count" class="form-label">{{ __('License Count') }}</label>
                        <select name="license_count" id="license_count" class="form-select">
                            <option value="">{{ __('Any') }}</option>
                            <option value="none" {{ $currentFilters['license_count'] === 'none' ? 'selected' : '' }}>{{ __('No Licenses') }}</option>
                            <option value="has" {{ $currentFilters['license_count'] === 'has' ? 'selected' : '' }}>{{ __('Has Licenses') }}</option>
                        </select>
                    </div>
                </div>

                @if ($hasFilters)
                    <div class="active-filters" data-active-filters>
                        <div class="active-filters__header">
                            <div class="active-filters__copy">
                                <p class="active-filters__title">{{ __('Active Filters') }}</p>
                                <p class="active-filters__subtitle">{{ __('Remove a single filter or clear the current query.') }}</p>
                            </div>
                            <a href="{{ route('accounts.index') }}" class="active-filters__clear">
                                {{ __('Clear All') }}
                            </a>
                        </div>

                        <div class="active-filters__list">
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
                                <x-filter-badge :label="__('Status:').' '.$statusLabel" color="blue" :removeUrl="request()->fullUrlWithQuery(['status' => null])" />
                            @endif

                            @if ($currentFilters['privilege'])
                                @php
                                    $privilegeValue = $currentFilters['privilege'];
                                    $privilegeLabel = $privilegeOptions[$privilegeValue] ?? null;
                                @endphp

                                @if ($privilegeLabel)
                                    <x-filter-badge :label="__('Privilege:').' '.$privilegeLabel" color="green" :removeUrl="request()->fullUrlWithQuery(['privilege' => null])" />
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
                                <x-filter-badge :label="__('Licenses:').' '.$licenseCountLabel" color="orange" :removeUrl="request()->fullUrlWithQuery(['license_count' => null])" />
                            @endif

                            @if ($currentFilters['search'])
                                @php
                                    $searchFilterLabel = __('Search:').' "'.$currentFilters['search'].'"';
                                @endphp
                                <x-filter-badge :label="$searchFilterLabel" color="purple" :removeUrl="request()->fullUrlWithQuery(['search' => null])" />
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
                                <x-filter-badge :label="__('Sort:').' '.$sortLabel" color="yellow" :removeUrl="request()->fullUrlWithQuery(['sort' => null])" />
                            @endif
                        </div>
                    </div>
                @endif
            </x-filter-box>

            <x-table
                :emptyColspan="5"
                compact="true"
                ariaLabel="{{ __('Accounts table') }}"
            >
                <x-slot:headers>
                    <th scope="col" class="table-header-cell">
                        <div class="flex items-center gap-2">
                            <span>{{ __('Account') }}</span>
                        </div>
                    </th>
                    <th scope="col" class="table-header-cell">
                        <div class="flex items-center gap-2">
                            <span>{{ __('Access') }}</span>
                        </div>
                    </th>
                    <th scope="col" class="table-header-cell">
                        <div class="flex items-center gap-2">
                            <span>{{ __('Usage') }}</span>
                        </div>
                    </th>
                    <th scope="col" class="table-header-cell">
                        <div class="flex items-center gap-2">
                            <span>{{ __('Last Login') }}</span>
                        </div>
                    </th>
                    <th scope="col" class="table-header-cell" style="width: 4rem;" aria-label="{{ __('Actions') }}">
                        <div class="flex items-center justify-end gap-2">
                            <span aria-hidden="true"></span>
                        </div>
                    </th>
                </x-slot:headers>

                @foreach ($accounts as $account)
                    <tr class="table-row">
                        <td class="table-cell-primary">
                            <div class="flex items-center gap-3" data-account-cell="identity">
                                <div class="table-avatar">
                                    {{ $account->initials() }}
                                </div>

                                <div class="table-stack table-stack-tight min-w-0">
                                    <div class="table-title table-truncate table-truncate-md text-sm" title="{{ $account->username }}">{{ $account->username }}</div>
                                    <div class="table-meta table-truncate table-truncate-lg" title="{{ $account->email }}">{{ $account->email }}</div>
                                    <div class="table-meta">{{ __('ID:') }} {{ $account->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell table-cell-fit">
                            <div class="table-stack table-stack-tight" data-account-cell="access">
                                @if ($account->isCurrentlySuspended)
                                    <x-status-badge status="suspended" />
                                @else
                                    <x-status-badge status="active" />
                                @endif

                                <span class="table-meta">{{ $account->email_verified_at ? __('Verified email') : __('Unverified email') }}</span>

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
                            </div>
                        </td>
                        <td class="table-cell table-cell-fit">
                            <div class="table-stack table-stack-tight" data-account-cell="usage">
                                <div>{{ trans_choice(':count license|:count licenses', $account->licenses_count, ['count' => $account->licenses_count]) }}</div>
                                <div class="table-meta">{{ trans_choice(':count device|:count devices', $account->devices_count, ['count' => $account->devices_count]) }}</div>
                            </div>
                        </td>
                        <td class="table-cell">
                            @if ($account->last_login_at)
                                <div class="table-stack table-stack-tight">
                                    <div>{{ $account->last_login_at->diffForHumans() }}</div>
                                    <div class="table-meta">{{ $account->last_login_at->format('Y-m-d H:i') }}</div>
                                </div>
                            @else
                                <span class="table-meta">{{ __('Never') }}</span>
                            @endif
                        </td>
                        <td class="table-cell table-cell-fit text-right" style="width: 4rem;">
                            <div class="flex justify-end" aria-label="{{ __('Account row actions') }}">
                                <a href="{{ route('accounts.show', $account) }}" class="table-action table-action--primary">
                                    {{ __('View') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach

                @if ($accounts->count() === 0)
                    <tr class="table-row">
                        <td colspan="5" class="table-empty">
                            <div class="table-empty-state">
                                <x-icon name="users" class="table-empty-icon" />
                                <p class="table-empty-title">{{ __('No accounts found.') }}</p>
                                <p class="table-empty-copy">{{ __('Adjust the current filters or reset the query to widen the account directory.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endif
            </x-table>

            <div>
                <x-pagination :paginator="$accounts" />
            </div>
        </section>
    </div>
</x-app-sidebar-layout>
