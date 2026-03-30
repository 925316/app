<x-app-sidebar-layout>
    <x-slot name="header">{{ __('Account Management') }}</x-slot>

    <x-slot name="subheader">
        {{ __('Review account health, filter the list precisely, and keep destructive actions intact.') }}
    </x-slot>

    @php
        $suspendAccountConfirmation = __('Are you sure you want to suspend this account?');
        $deleteAccountConfirmation = __('Are you sure you want to delete this account? This action cannot be undone.');
        $hasFilters = $currentFilters['status'] || $currentFilters['privilege'] || $currentFilters['license_count'] || $currentFilters['search'] || $currentFilters['sort'] !== 'created_at_desc';
    @endphp

    <div class="space-y-8" data-page="accounts-index">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Account statistics') }}">
            <x-stat-card :title="__('Total Accounts')" :value="$statistics['total']" icon="users" iconColor="icon-blue" />
            <x-stat-card :title="__('Active Accounts')" :value="$statistics['active']" icon="success" iconColor="icon-green" />
            <x-stat-card :title="__('Suspended')" :value="$statistics['suspended']" icon="warning" iconColor="icon-red" />
            <x-stat-card :title="__('Verified')" :value="$statistics['verified']" icon="shield" iconColor="icon-purple" />
        </section>

        <section class="card-shell space-y-6" data-accounts-panel>
            <div class="app-toolbar" data-accounts-toolbar>
                <div>
                    <p class="section-kicker">{{ __('Operations') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Account directory') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Use the shared filters below without changing routes, semantics, or query behavior.') }}</p>
                </div>

                <div class="app-toolbar-actions">
                    <a href="{{ route('accounts.create') }}" class="btn btn-primary btn-sm gap-2">
                        <x-icon name="plus" class="h-4 w-4" />
                        {{ __('Create Account') }}
                    </a>
                </div>
            </div>

            <x-filter-box :action="route('accounts.index')" :totalCount="$statistics['total']" :title="__('Filter accounts')">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
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

                    <div class="space-y-2">
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
                </div>

                <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-12">
                    <div class="space-y-2 md:col-span-8">
                        <label for="search" class="form-label">{{ __('Search') }}</label>
                        <x-input-with-icon id="search" name="search" type="text" :value="$currentFilters['search']"
                            :placeholder="__('Search by username, email, or license key...')" icon="search" />
                    </div>

                    <div class="space-y-2 md:col-span-4 filter-box-actions">
                        <span class="form-label text-transparent">{{ __('Actions') }}</span>
                        <div class="form-actions-cluster">
                            <button type="submit" class="btn btn-primary btn-sm flex-1 gap-2 justify-center">
                                <x-icon name="search" class="h-4 w-4" />
                                {{ __('Filter') }}
                            </button>
                            <a href="{{ route('accounts.index') }}" class="btn btn-secondary btn-sm gap-2 justify-center">
                                <x-icon name="reset" class="h-4 w-4" />
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                </div>

                @if ($hasFilters)
                    <div class="active-filters" data-active-filters>
                        <div class="active-filters__header">
                            <div class="active-filters__copy">
                                <p class="active-filters__title">{{ __('Active Filters') }}</p>
                                <p class="active-filters__subtitle">{{ __('Remove a single filter or clear the entire query.') }}</p>
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

            <x-data-table :headers="[__('User'), __('Email'), __('Status'), __('Privilege'), __('Licenses'), __('Devices'), __('Last Login'), __('Actions')]" :emptyColspan="8" ariaLabel="{{ __('Accounts table') }}">
                @foreach ($accounts as $account)
                    <tr class="table-row">
                        <td class="table-cell-primary whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="table-avatar">
                                    {{ $account->initials() }}
                                </div>

                                <div class="table-stack table-stack-tight">
                                    <div class="table-title text-sm">{{ $account->username }}</div>
                                    <div class="table-meta">{{ __('ID:') }} {{ $account->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell whitespace-nowrap">
                            <div class="max-w-[220px] truncate" title="{{ $account->email }}">
                                <span class="badge {{ $account->email_verified_at ? 'badge-verified' : 'badge-unverified' }}">
                                    {{ $account->email }}
                                </span>
                            </div>
                        </td>
                        <td class="table-cell whitespace-nowrap">
                            @if ($account->isCurrentlySuspended)
                                <x-status-badge status="suspended" />
                            @else
                                <x-status-badge status="active" />
                            @endif
                        </td>
                        <td class="table-cell whitespace-nowrap">
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
                        <td class="table-cell whitespace-nowrap">{{ $account->licenses_count }}</td>
                        <td class="table-cell whitespace-nowrap">{{ $account->devices_count }}</td>
                        <td class="table-cell whitespace-nowrap">
                            @if ($account->last_login_at)
                                <div class="table-stack table-stack-tight">
                                    <div>{{ $account->last_login_at->diffForHumans() }}</div>
                                    <div class="table-meta">{{ $account->last_login_at->format('Y-m-d H:i') }}</div>
                                </div>
                            @else
                                <span class="table-meta">{{ __('Never') }}</span>
                            @endif
                        </td>
                        <td class="table-cell whitespace-nowrap text-right">
                            <div class="table-actions table-actions--nowrap">
                                <a href="{{ route('accounts.show', $account) }}" class="table-action table-action--primary">
                                    {{ __('View') }}
                                </a>

                                @if ($account->isCurrentlySuspended)
                                    <form action="{{ route('accounts.unsuspend', $account) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="table-action table-action--success">
                                            {{ __('Unsuspend') }}
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('accounts.suspend', $account) }}" method="POST" class="inline" onsubmit="return confirm('{{ $suspendAccountConfirmation }}')">
                                        @csrf
                                        <button type="submit" class="table-action table-action--danger">
                                            {{ __('Suspend') }}
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline" onsubmit="return confirm('{{ $deleteAccountConfirmation }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-action table-action--danger">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach

                @if ($accounts->count() === 0)
                    <tr class="table-row">
                        <td colspan="8" class="table-empty">
                            <div class="table-empty-state">
                                <x-icon name="users" class="table-empty-icon" />
                                <p class="table-empty-title">{{ __('No accounts found.') }}</p>
                                <p class="table-empty-copy">{{ __('Adjust the current filters or reset the query to widen the account directory.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endif
            </x-data-table>

            <div>
                <x-pagination :paginator="$accounts" />
            </div>
        </section>
    </div>
</x-app-sidebar-layout>
