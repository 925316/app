<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Device Management') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Filter the device inventory, preserve export and admin actions, and align the remaining device admin surface with the shared cinematic system.') }}
    </x-slot>

    @php
        $hasFilters = filled(request('search'))
            || filled(request('status'))
            || filled(request('date_range'))
            || filled(request('country_code'))
            || filled(request('min_reset_count'))
            || filled(request('account_status'));

        $searchFilterLabel = filled(request('search'))
            ? __('Search:').' "'.request('search').'"'
            : null;

        $statusLabel = match (request('status')) {
            'bound' => __('Currently Bound'),
            'unbound' => __('Unbound'),
            'never_bound' => __('Never Bound'),
            default => null,
        };

        $dateRangeLabel = match (request('date_range')) {
            '24h' => __('Last 24 Hours'),
            '7d' => __('Last 7 Days'),
            '30d' => __('Last 30 Days'),
            '90d' => __('Last 90 Days'),
            default => null,
        };

        $accountStatusLabel = match (request('account_status')) {
            'active' => __('Active'),
            'suspended' => __('Suspended'),
            default => null,
        };
    @endphp

    <div class="space-y-8" data-page="devices-admin-index">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Device statistics') }}">
            <x-stat-card :title="__('Total Devices')" :value="$totalDevices" icon="desktop" iconColor="icon-blue" />
            <x-stat-card :title="__('Bound Devices')" :value="$boundDevices" icon="success" iconColor="icon-green" />
            <x-stat-card :title="__('Unbound')" :value="$unboundDevices" icon="ban" iconColor="icon-gray" />
            <x-stat-card :title="__('Never Bound')" :value="$neverBoundDevices" icon="warning" iconColor="icon-yellow" />
        </section>

        <section class="card-shell space-y-6" data-devices-admin-panel>
            <div class="app-toolbar" data-devices-admin-toolbar>
                <div>
                    <p class="section-kicker">{{ __('Inventory') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Device directory') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Keep the same admin filters, export endpoint, pagination, and reset or unbind actions while matching the accounts and sessions admin surfaces.') }}</p>
                </div>

                <div class="app-toolbar-actions">
                    <a href="{{ route('devices.export', request()->query()) }}" class="btn btn-secondary btn-sm gap-2">
                        <x-icon name="cloud" class="h-4 w-4" />
                        {{ __('Export CSV') }}
                    </a>
                </div>
            </div>

            <x-filter-box :action="route('devices.index')" :totalCount="$devices->total()" :title="__('Filter devices')">
                <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-12">
                    <div class="space-y-2 md:col-span-6 xl:col-span-5">
                        <label for="search" class="form-label">{{ __('Search') }}</label>
                        <x-input-with-icon id="search" name="search" type="text" :value="request('search')" :placeholder="__('HWID, IP, Username, Email')" icon="search" />
                    </div>

                    <div class="space-y-2 md:col-span-3 xl:col-span-2">
                        <label for="status" class="form-label">{{ __('Status') }}</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="bound" {{ request('status') === 'bound' ? 'selected' : '' }}>{{ __('Currently Bound') }}</option>
                            <option value="unbound" {{ request('status') === 'unbound' ? 'selected' : '' }}>{{ __('Unbound') }}</option>
                            <option value="never_bound" {{ request('status') === 'never_bound' ? 'selected' : '' }}>{{ __('Never Bound') }}</option>
                        </select>
                    </div>

                    <div class="space-y-2 md:col-span-3 xl:col-span-2">
                        <label for="date_range" class="form-label">{{ __('Date Range') }}</label>
                        <select name="date_range" id="date_range" class="form-select">
                            <option value="">{{ __('All Time') }}</option>
                            <option value="24h" {{ request('date_range') === '24h' ? 'selected' : '' }}>{{ __('Last 24 Hours') }}</option>
                            <option value="7d" {{ request('date_range') === '7d' ? 'selected' : '' }}>{{ __('Last 7 Days') }}</option>
                            <option value="30d" {{ request('date_range') === '30d' ? 'selected' : '' }}>{{ __('Last 30 Days') }}</option>
                            <option value="90d" {{ request('date_range') === '90d' ? 'selected' : '' }}>{{ __('Last 90 Days') }}</option>
                        </select>
                    </div>

                    <div class="space-y-2 md:col-span-12 xl:col-span-3 filter-box-actions">
                        <span class="form-label text-transparent">{{ __('Actions') }}</span>
                        <div class="form-actions-cluster">
                            <button type="submit" class="btn btn-primary btn-sm flex-1 justify-center gap-2 xl:flex-none">
                                <x-icon name="search" class="h-4 w-4" />
                                {{ __('Apply Filters') }}
                            </button>

                            <a href="{{ route('devices.index') }}" class="btn btn-secondary btn-sm justify-center gap-2">
                                <x-icon name="reset" class="h-4 w-4" />
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="form-divider grid grid-cols-1 gap-4 md:grid-cols-12">
                    <div class="space-y-2 md:col-span-4 xl:col-span-3">
                        <label for="country_code" class="form-label">{{ __('Country Code') }}</label>
                        <input type="text" name="country_code" id="country_code" value="{{ request('country_code') }}" placeholder="{{ __('e.g., US, CN') }}" class="form-input w-full uppercase">
                    </div>

                    <div class="space-y-2 md:col-span-4 xl:col-span-3">
                        <label for="min_reset_count" class="form-label">{{ __('Min HWID Reset') }}</label>
                        <input type="number" name="min_reset_count" id="min_reset_count" value="{{ request('min_reset_count') }}" min="0" class="form-input w-full">
                    </div>

                    <div class="space-y-2 md:col-span-4 xl:col-span-3">
                        <label for="account_status" class="form-label">{{ __('Account Status') }}</label>
                        <select name="account_status" id="account_status" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="active" {{ request('account_status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="suspended" {{ request('account_status') === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                        </select>
                    </div>

                    <div class="hidden xl:block xl:col-span-3"></div>
                </div>

                @if ($hasFilters)
                    <div class="active-filters" data-active-filters>
                        <div class="active-filters__header">
                            <div class="active-filters__copy">
                                <p class="active-filters__title">{{ __('Active Filters') }}</p>
                                <p class="active-filters__subtitle">{{ __('Remove a single filter or clear the full device query without changing admin permissions, exports, or pagination behavior.') }}</p>
                            </div>
                            <a href="{{ route('devices.index') }}" class="active-filters__clear">
                                {{ __('Clear All') }}
                            </a>
                        </div>

                        <div class="active-filters__list">
                            @if ($searchFilterLabel)
                                <x-filter-badge :label="$searchFilterLabel" color="purple" :removeUrl="request()->fullUrlWithQuery(['search' => null])" />
                            @endif

                            @if ($statusLabel)
                                <x-filter-badge :label="__('Status:').' '.$statusLabel" color="blue" :removeUrl="request()->fullUrlWithQuery(['status' => null])" />
                            @endif

                            @if ($dateRangeLabel)
                                <x-filter-badge :label="__('Date Range:').' '.$dateRangeLabel" color="yellow" :removeUrl="request()->fullUrlWithQuery(['date_range' => null])" />
                            @endif

                            @if (filled(request('country_code')))
                                <x-filter-badge :label="__('Country:').' '.strtoupper((string) request('country_code'))" color="green" :removeUrl="request()->fullUrlWithQuery(['country_code' => null])" />
                            @endif

                            @if (filled(request('min_reset_count')))
                                <x-filter-badge :label="__('Min Resets:').' '.request('min_reset_count')" color="orange" :removeUrl="request()->fullUrlWithQuery(['min_reset_count' => null])" />
                            @endif

                            @if ($accountStatusLabel)
                                <x-filter-badge :label="__('Account Status:').' '.$accountStatusLabel" color="green" :removeUrl="request()->fullUrlWithQuery(['account_status' => null])" />
                            @endif
                        </div>
                    </div>
                @endif
            </x-filter-box>

            <x-table
                :headers="[
                    __('Account'),
                    __('Device'),
                    __('Activity'),
                    __('State'),
                    __('HWID Resets'),
                    __('Actions'),
                ]"
                :emptyColspan="6"
                compact="true"
                ariaLabel="{{ __('Devices admin table') }}"
            >
                @forelse ($devices as $device)
                    <tr class="table-row">
                        <td class="table-cell-primary">
                            @if ($device->account)
                                <div class="flex items-center gap-3">
                                    <div class="table-avatar">
                                        {{ $device->account->initials() }}
                                    </div>

                                    <div class="table-stack table-stack-tight min-w-0">
                                        <div class="table-title table-truncate table-truncate-md text-sm" title="{{ $device->account->username }}">{{ $device->account->username }}</div>
                                         <div class="table-meta table-truncate table-truncate-md" title="{{ $device->account->email }}">{{ $device->account->email }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="table-meta">{{ __('Unknown account') }}</span>
                            @endif
                        </td>

                        <td class="table-cell">
                            @if ($device->hwid_hash)
                                <div class="table-stack table-stack-tight min-w-0">
                                    <button
                                        type="button"
                                        class="badge badge-default table-inline-copy max-w-full transition hover:border-cool-400 hover:text-cool-800 dark:hover:border-cool-500 dark:hover:text-cool-100"
                                        title="{{ $device->hwid_hash }}"
                                        aria-label="{{ __('Copy full HWID') }}"
                                        data-copy-value="{{ $device->hwid_hash }}"
                                        data-device-hwid-copy="true"
                                        onclick="copyDeviceValue(this)"
                                    >
                                        <span class="table-truncate table-truncate-md font-mono text-xs sm:text-sm">
                                            {{ \Illuminate\Support\Str::limit($device->hwid_hash, 20, '...') }}
                                        </span>
                                    </button>
                                    <div class="table-meta">{{ __('Device ID:') }} {{ $device->id }}</div>
                                    <div class="table-meta table-truncate table-truncate-sm" title="{{ $device->ip_address ?? __('Unknown') }}">
                                        {{ $device->ip_address ?? __('Unknown') }} · {{ $device->country_code ?? __('Unknown') }}
                                    </div>
                                </div>
                            @else
                                <span class="table-meta">{{ __('N/A') }}</span>
                            @endif
                        </td>

                        <td class="table-cell">
                            <div class="table-stack table-stack-tight">
                                <div>{{ __('Last:') }} {{ $device->last_seen_at ? $device->last_seen_at->format('Y-m-d H:i') : __('Unknown') }}</div>
                                <div class="table-meta">{{ __('First:') }} {{ $device->first_seen_at ? $device->first_seen_at->format('Y-m-d H:i') : __('Unknown') }}</div>
                            </div>
                        </td>

                        <td class="table-cell table-cell-fit">
                            <div class="table-stack table-stack-tight">
                                @if ($device->isBound())
                                    <x-status-badge status="active" :text="__('Currently Bound')" />
                                @elseif ($device->bound_at)
                                    <x-status-badge status="default" :text="__('Historical')" />
                                @else
                                    <x-status-badge status="warning" :text="__('Never Bound')" />
                                @endif

                                @if ($device->account && $device->account->isSuspended())
                                    <x-status-badge status="suspended" :text="__('Suspended')" />
                                @else
                                    <x-status-badge status="active" :text="__('Active')" />
                                @endif
                            </div>
                        </td>

                        <td class="table-cell table-cell-fit">
                            <div class="table-stack table-stack-tight">
                                <div>{{ $device->account?->hwid_reset_count ?? 0 }}</div>
                                <div class="table-meta">
                                    {{ $device->account && $device->account->canResetHwid() ? __('Reset available') : __('Cooldown') }}
                                </div>
                            </div>
                        </td>

                        <td class="table-cell table-cell-fit text-right">
                            @if ($device->account)
                                <div class="table-actions" aria-label="{{ __('Device row actions') }}">
                                    <a href="{{ route('accounts.show', $device->account) }}#account-device-{{ $device->id }}" class="table-action table-action--primary">
                                        {{ __('View') }}
                                    </a>
                                </div>
                            @else
                                <span class="table-meta">{{ __('No actions') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="6" class="table-empty">
                            <div class="table-empty-state">
                                <x-icon name="desktop" class="table-empty-icon" />
                                <p class="table-empty-title">{{ __('No devices found matching your filters.') }}</p>
                                <p class="table-empty-copy">{{ __('Broaden the current query or clear a filter badge to surface more device records.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div>
                <x-pagination :paginator="$devices" />
            </div>
        </section>
    </div>
</x-app-sidebar-layout>

<script>
    function copyDeviceValue(element) {
        const value = element?.getAttribute('data-copy-value') ?? '';
        if (! value) {
            return;
        }

        navigator.clipboard?.writeText(value).then(() => {
            const originalTitle = element.getAttribute('title') ?? value;
            element.setAttribute('title', "{{ __('Copied') }}");
            setTimeout(() => element.setAttribute('title', originalTitle), 1200);
        }).catch(() => {
            const textArea = document.createElement('textarea');
            textArea.value = value;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        });
    }
</script>
