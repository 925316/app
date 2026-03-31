<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Licenses') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Review license status, ownership, and activation from the shared command surface.') }}
    </x-slot>

    @php
        $hasAdminFilters = request()->filled('status') || request()->filled('privilege') || request()->filled('search');
        $pageTitle = $isAdmin ?? false ? __('License directory') : __('My licenses');
        $pageSubtitle = $isAdmin ?? false
            ? __('Review license status, expiry, and account ownership in one place without changing filters or routes.')
            : __('Activate a key, review your assigned licenses, and keep the same ownership and activation semantics.');
    @endphp

    <div class="space-y-8" data-page="licenses-index">
        @if (Auth::user()->hasPrivilege(7))
            <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('License statistics') }}">
                <x-stat-card :title="__('Total Licenses')" :value="$statistics['total']" icon="document" iconColor="icon-blue" />
                <x-stat-card :title="__('Active')" :value="$statistics['active']" icon="success" iconColor="icon-green" />
                <x-stat-card :title="__('Expired')" :value="$statistics['expired']" icon="error" iconColor="icon-red" />
                <x-stat-card :title="__('Unassigned')" :value="$statistics['unassigned']" icon="warning" iconColor="icon-yellow" />
            </section>
        @endif

        <section class="card-shell space-y-6" data-licenses-panel>
            <div class="app-toolbar" data-licenses-toolbar>
                <div>
                    <p class="section-kicker">{{ $isAdmin ?? false ? __('Directory') : __('Activation') }}</p>
                    <h2 class="app-toolbar-title">{{ $pageTitle }}</h2>
                    <p class="app-toolbar-subtitle">{{ $pageSubtitle }}</p>
                </div>

                @if ($isAdmin ?? false)
                    <div class="app-toolbar-actions">
                        <a href="{{ route('licenses.create') }}" class="btn btn-primary btn-sm gap-2">
                            <x-icon name="plus" class="h-4 w-4" />
                            {{ __('Create License') }}
                        </a>
                    </div>
                @endif
            </div>

            @if (!$isAdmin ?? false)
                <div class="card-shell-muted space-y-6" data-license-activation-panel>
                    <div class="flex flex-col gap-4 md:flex-row md:items-start">
                        <span class="card-icon-container icon-blue shrink-0" aria-hidden="true">
                            <x-icon name="shield" class="h-6 w-6" />
                        </span>

                        <div class="space-y-2">
                            <p class="section-kicker">{{ __('License Redeem') }}</p>
                            <h3 class="card-form-title text-lg font-semibold">{{ __('Activate License') }}</h3>
                            <p class="card-form-copy max-w-3xl">
                                {{ __('Enter your license key to activate premium features. Keys follow the format XXXXX-XXXXX-XXXXX-XXXXX-XXXXX.') }}
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('licenses.activate-by-key') }}" class="space-y-4">
                        @csrf

                        <div class="space-y-2">
                            <label for="license_key" class="form-label">{{ __('License Key') }}</label>
                            <input
                                type="text"
                                id="license_key"
                                name="license_key"
                                value="{{ old('license_key') }}"
                                placeholder="{{ __('XXXXX-XXXXX-XXXXX-XXXXX-XXXXX') }}"
                                class="form-input table-code text-center text-sm uppercase tracking-[0.3em] sm:text-base @error('license_key') border-red-500 @enderror"
                                maxlength="29"
                                required
                                title="{{ __('License key must be in the format: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX') }}"
                            >
                            @error('license_key')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-panel rounded-2xl px-4 py-3 text-sm">
                            <p class="card-label-strong font-medium">{{ __('Terms Reminder') }}</p>
                            <p class="mt-1 card-form-copy">
                                {{ __('By activating a key, you agree that license usage is device-bound and subject to account suspension rules on abuse.') }}
                            </p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary gap-2">
                                <x-icon name="shield" class="h-4 w-4" />
                                {{ __('Activate License') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            @if ($isAdmin ?? false)
                <x-filter-box :action="route('licenses.index')" :totalCount="$licenses->total()" :title="__('Filter licenses')">
                    <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-12">
                        <div class="space-y-2 md:col-span-8 xl:col-span-6">
                            <label for="search" class="form-label">{{ __('Search') }}</label>
                            <x-input-with-icon id="search" name="search" type="text" :value="request('search', '')"
                                :placeholder="__('Search by key or username...')" icon="search" />
                        </div>

                        <div class="md:col-span-4 xl:col-span-3">
                            <x-filter-dropdown
                                id="status"
                                name="status"
                                :label="__('Status')"
                                :value="request('status', '')"
                                :options="['' => __('All Statuses')] + collect($statusOptions)
                                    ->mapWithKeys(fn ($optionLabel, $optionValue) => [(string) $optionValue => ucfirst($optionLabel)])
                                    ->all()"
                            />
                        </div>

                        <div class="space-y-2 md:col-span-12 xl:col-span-3 filter-box-actions">
                            <span class="form-label text-transparent">{{ __('Actions') }}</span>
                            <div class="form-actions-cluster">
                                <button type="submit" class="btn btn-primary btn-sm flex-1 justify-center gap-2 xl:flex-none">
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

                    <div class="form-divider grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div class="xl:max-w-sm">
                            <x-filter-dropdown
                                id="privilege"
                                name="privilege"
                                :label="__('Privilege')"
                                :value="request('privilege', '')"
                                :options="['' => __('All Privileges')] + collect($privilegeOptions)
                                    ->mapWithKeys(fn ($optionLabel, $optionValue) => [(string) $optionValue => ucfirst($optionLabel)])
                                    ->all()"
                            />
                        </div>
                    </div>

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
                                    <div class="table-stack table-stack-tight">
                                        <span class="table-title text-sm">{{ __('Unassigned') }}</span>
                                        <span class="table-meta">{{ __('No account owner yet') }}</span>
                                    </div>
                                @endif
                            </td>
                        @endif
                        <td class="table-cell table-cell-fit">
                            <span class="table-title text-sm">{{ $license->getPrivilegeTextAttribute() }}</span>
                        </td>
                        <td class="table-cell table-cell-fit">
                            <x-status-badge :status="$license->status->value" :text="$license->getStatusTextAttribute()" />
                        </td>
                        <td class="table-cell table-cell-fit">
                            <div class="table-stack table-stack-tight">
                                <span title="{{ $license->expires_at->format('Y-m-d H:i:s') }}">
                                    {{ $license->expires_at->format('Y-m-d') }}
                                </span>
                                @if ($license->isActive() && !$license->isExpired())
                                    <span class="table-meta">{{ __('In :days days', ['days' => $license->daysUntilExpiry()]) }}</span>
                                @elseif ($license->isExpired())
                                    <span class="table-meta">{{ __('Expired') }}</span>
                                @endif
                            </div>
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
                    <tr class="table-row">
                        <td colspan="{{ $isAdmin ?? false ? 6 : 5 }}" class="table-empty">
                            <div class="table-empty-state">
                                <x-icon name="document" class="table-empty-icon" />
                                <p class="table-empty-title">{{ __('No licenses found.') }}</p>
                                <p class="table-empty-copy">
                                    {{ $isAdmin ?? false ? __('Try broadening the filters or creating a new license.') : __('Activate a license key to start building your license history here.') }}
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div>
                <x-pagination :paginator="$licenses" />
            </div>
        </section>
    </div>

</x-app-sidebar-layout>
