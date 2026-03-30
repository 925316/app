<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Licenses') }}
    </x-slot>

    @php
        $hasAdminFilters = request()->filled('status') || request()->filled('privilege') || request()->filled('search');
    @endphp

    <div class="space-y-6">

            @if (Auth::user()->hasPrivilege(7))
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <x-stat-card :title="__('Total Licenses')" :value="$statistics['total']" icon="document" iconColor="icon-blue" />
                    <x-stat-card :title="__('Active')" :value="$statistics['active']" icon="success" iconColor="icon-green" />
                    <x-stat-card :title="__('Expired')" :value="$statistics['expired']" icon="error" iconColor="icon-red" />
                    <x-stat-card :title="__('Unassigned')" :value="$statistics['unassigned']" icon="warning" iconColor="icon-yellow" />
                </div>
            @endif

            <div class="card-shell overflow-hidden">
                <div class="space-y-6 p-6">
                    <!-- Header with actions -->
                    <div class="app-toolbar">
                        <div>
                            <h3 class="app-toolbar-title">
                                @if ($isAdmin ?? false)
                                    {{ __('All Licenses') }}
                                @else
                                    {{ __('My Licenses') }}
                                @endif
                            </h3>
                            <p class="app-toolbar-subtitle">
                                {{ __('Review license status, expiry, and account ownership in one place.') }}
                            </p>
                        </div>

                        @if ($isAdmin ?? false)
                            <div class="app-toolbar-actions">
                                <a href="{{ route('licenses.create') }}" class="btn btn-primary btn-sm">
                                    {{ __('Create License') }}
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- License Activation Form for Regular Users -->
                    @if (!$isAdmin ?? false)
                        <div class="card-shell-muted form-panel">
                            <div class="flex items-start space-x-4">
                                <div class="card-icon-container icon-blue h-12 w-12 shrink-0 rounded-full">
                                    <svg class="h-6 w-6" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="section-kicker mb-1">{{ __('License Redeem') }}</p>
                                    <h4 class="card-form-title mb-2 text-lg font-semibold">{{ __('Activate License') }}</h4>
                                    <p class="card-form-copy mb-4">
                                        Enter your license key below to activate premium features. License keys follow
                                        the format: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX
                                    </p>

                                    <form method="POST" action="{{ route('licenses.activate-by-key') }}" class="space-y-4">
                                        @csrf

                                        <div>
                                            <label for="license_key" class="form-label mb-2">
                                                License Key
                                            </label>
                                            <input type="text" id="license_key" name="license_key"
                                                value="{{ old('license_key') }}"
                                                placeholder="{{ __('XXXXX-XXXXX-XXXXX-XXXXX-XXXXX') }}"
                                                class="form-input w-full py-3 text-center font-mono text-lg uppercase tracking-wider @error('license_key') border-red-500 @enderror"
                                                maxlength="29" required {{-- pattern="^[A-Z0-9]{5}-[0-9A-F]{5}-[A-Z2-7]{5}-[A-Z3-8]{5}-[A-Z0-9]{5}$" --}}
                                                title="{{ __('License key must be in the format: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX') }}">
                                            @error('license_key')
                                                <p class="form-error mt-2">{{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div class="form-panel rounded-lg px-4 py-3 text-sm">
                                            <p class="card-label-strong font-medium">{{ __('Terms Reminder') }}</p>
                                            <p class="mt-1">{{ __('By activating a key, you agree that license usage is device-bound and subject to account suspension rules on abuse.') }}</p>
                                        </div>

                                        <div class="flex justify-end">
                                            <button type="submit" class="btn btn-primary">
                                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                                    </path>
                                                </svg>
                                                {{ __('Activate License') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($isAdmin ?? false)
                        <!-- Admin filters -->
                        <x-filter-box :action="route('licenses.index')" :totalCount="$licenses->total()" :title="__('Filter licenses')">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Status filter -->
                                <div class="space-y-2">
                                    <label for="status" class="form-label">{{ __('Status') }}</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">{{ __('All Statuses') }}</option>
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
                                    <label for="privilege" class="form-label">{{ __('Privilege') }}</label>
                                    <select name="privilege" id="privilege" class="form-select">
                                        <option value="">{{ __('All Privileges') }}</option>
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
                                    <label for="search" class="form-label">{{ __('Search') }}</label>
                                    <x-input-with-icon id="search" name="search" type="text" :value="request('search', '')"
                                        :placeholder="__('Search by key or username...')" icon="search" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-12">
                                <div class="space-y-2 md:col-span-8">
                                    <span class="form-label text-transparent">{{ __('Search field alignment') }}</span>
                                </div>

                                <div class="space-y-2 md:col-span-4 filter-box-actions">
                                    <span class="form-label text-transparent">{{ __('Actions') }}</span>
                                    <div class="form-actions-cluster">
                                        <button type="submit" class="btn btn-primary btn-sm flex-1 justify-center gap-2">
                                            <x-icon name="search" class="h-4 w-4" />
                                            {{ __('Filter') }}
                                        </button>
                                        <a href="{{ route('licenses.index') }}" class="btn btn-secondary btn-sm justify-center gap-2">
                                            <x-icon name="reset" class="h-4 w-4" />
                                            {{ __('Reset') }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Active filters badge -->
                            @if ($hasAdminFilters)
                                <div class="active-filters" data-active-filters>
                                    <div class="active-filters__header">
                                        <div class="active-filters__copy">
                                            <p class="active-filters__title">{{ __('Active Filters') }}</p>
                                            <p class="active-filters__subtitle">{{ __('Remove a single filter or clear the license query without changing admin access or pagination behavior.') }}</p>
                                        </div>
                                        <a href="{{ route('licenses.index') }}" class="active-filters__clear">
                                            {{ __('Clear All') }}
                                        </a>
                                    </div>

                                    <div class="active-filters__list">
                                        @if (request()->filled('status'))
                                            @php
                                                $statusValue = request('status');
                                                $statusLabel = $statusOptions[$statusValue] ?? null;
                                            @endphp
                                            @if ($statusLabel)
                                                <x-filter-badge :label="__('Status:').' '.ucfirst($statusLabel)" color="blue"
                                                    :removeUrl="request()->fullUrlWithQuery(['status' => null])" />
                                            @endif
                                        @endif
                                        @if (request()->filled('privilege'))
                                            @php
                                                $privilegeValue = request('privilege');
                                                $privilegeLabel = $privilegeOptions[$privilegeValue] ?? null;
                                            @endphp
                                            @if ($privilegeLabel)
                                                <x-filter-badge :label="__('Privilege:').' '.ucfirst($privilegeLabel)" color="green"
                                                    :removeUrl="request()->fullUrlWithQuery(['privilege' => null])" />
                                            @endif
                                        @endif
                                        @if (request()->filled('search'))
                                            @php
                                                $searchFilterLabel = __('Search:').' "'.request('search').'"';
                                            @endphp
                                            <x-filter-badge :label="$searchFilterLabel" color="purple"
                                                :removeUrl="request()->fullUrlWithQuery(['search' => null])" />
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </x-filter-box>
                    @endif

                    <!-- Licenses table -->
                    <x-table
                        :headers="$isAdmin ?? false ? ['Key', 'Account', 'Privilege', 'Status', 'Expires', 'Actions'] : ['Key', 'Privilege', 'Status', 'Expires', 'Actions']"
                        :emptyColspan="$isAdmin ?? false ? 6 : 5"
                        compact="true"
                        ariaLabel="{{ __('Licenses table') }}"
                    >
                        @forelse($licenses as $license)
                            <tr class="table-row">
                                <td class="table-cell-primary">
                                    <div class="table-stack table-stack-tight">
                                        <span class="table-title table-code table-truncate table-truncate-lg" title="{{ $license->key }}">{{ $license->key }}</span>
                                        <span class="table-meta">{{ __('ID:') }} {{ $license->id }}</span>
                                    </div>
                                </td>
                                @if ($isAdmin ?? false)
                                <td class="table-cell">
                                    @if ($license->account)
                                        <div class="table-stack table-stack-tight">
                                            <span class="table-title table-truncate table-truncate-md text-sm" title="{{ $license->account->username }}">{{ $license->account->username }}</span>
                                            <span class="table-meta table-truncate table-truncate-md" title="{{ $license->account->email }}">{{ $license->account->email }}</span>
                                        </div>
                                    @else
                                        <span class="table-meta">{{ __('Unassigned') }}</span>
                                    @endif
                                </td>
                                @endif
                                <td class="table-cell table-cell-fit">
                                    {{ $license->getPrivilegeTextAttribute() }}
                                </td>
                                <td class="table-cell table-cell-fit">
                                    <x-status-badge :status="$license->status->value" :text="$license->getStatusTextAttribute()" />
                                </td>
                                <td class="table-cell table-cell-fit">
                                    <span title="{{ $license->expires_at->format('Y-m-d H:i:s') }}">
                                        {{ $license->expires_at->format('Y-m-d') }}
                                    </span>
                                    @if ($license->isActive() && !$license->isExpired())
                                        <span class="table-link-muted text-xs">
                                            ({{ $license->daysUntilExpiry() }}d)
                                        </span>
                                    @endif
                                </td>
                                <td class="table-cell table-cell-fit text-right font-medium">
                                    <div class="table-actions" aria-label="{{ __('License row actions') }}">
                                        <a href="{{ route('licenses.show', $license) }}" class="table-action table-action--primary">
                                            {{ __('View') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ?? false ? 6 : 5 }}" class="table-empty">
                                    {{ __('No licenses found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </x-table>

                    <!-- Pagination -->
                    <div>
                        <x-pagination :paginator="$licenses" />
                    </div>
                </div>
            </div>
    </div>

</x-app-sidebar-layout>
