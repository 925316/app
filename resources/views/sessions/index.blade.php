<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Session Management') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Track live heartbeats, narrow the session stream, and keep termination controls intact.') }}
    </x-slot>

    @php
        $sessionTitle = $isAdmin ? __('Session management') : __('My sessions');
        $sessionSubtitle = $isAdmin
            ? __('Monitor account activity, filter the heartbeat stream, and keep the same query behavior.')
            : __('Review your active and expired sessions without changing the existing filters or actions.');
        $sessionHeaders = $isAdmin
            ? [__('Account'), __('Session'), __('Activity'), __('Status'), __('Actions')]
            : [__('Session'), __('Activity'), __('Status'), __('Actions')];
        $hasFilters =
            request()->filled('status') ||
            request()->filled('search') ||
            $currentFilters['sort'] !== 'last_heartbeat_at' ||
            $currentFilters['direction'] !== 'desc';
    @endphp

    <div class="space-y-8" data-page="sessions-index">
        @if ($isAdmin && $statistics)
            <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5" aria-label="{{ __('Session statistics') }}">
                <x-stat-card :title="__('Total Sessions')" :value="$statistics['total']" icon="server" iconColor="icon-blue" />
                <x-stat-card :title="__('Active Sessions')" :value="$statistics['active']" icon="success" iconColor="icon-green" />
                <x-stat-card :title="__('Expired Sessions')" :value="$statistics['expired']" icon="error" iconColor="icon-red" />
                <x-stat-card :title="__('Unique Accounts')" :value="$statistics['unique_accounts']" icon="users" iconColor="icon-purple" />
                <x-stat-card :title="__('Unique Devices')" :value="$statistics['unique_devices']" icon="desktop" iconColor="icon-orange" />
            </section>
        @endif

        <section class="card-shell space-y-6" data-sessions-panel>
            <div class="app-toolbar" data-sessions-toolbar>
                <div>
                    <p class="section-kicker">{{ __('Heartbeats') }}</p>
                    <h2 class="app-toolbar-title">{{ $sessionTitle }}</h2>
                    <p class="app-toolbar-subtitle">{{ $sessionSubtitle }}</p>
                </div>
            </div>

            <x-filter-box
                :action="route('sessions.index')"
                :totalCount="$sessions->total()"
                :title="__('Filter sessions')"
                defaultValues="sort:last_heartbeat_at,direction:desc"
            >
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="space-y-2 lg:col-span-5">
                        <label for="search" class="form-label">{{ __('Search') }}</label>
                        <x-input-with-icon
                            id="search"
                            name="search"
                            type="text"
                            :value="$currentFilters['search']"
                            :placeholder="$isAdmin ? __('Search by account username, device name, or session token...') : __('Search by device name or session token...')"
                            icon="search"
                        />
                    </div>

                    <div class="space-y-2 lg:col-span-2">
                        <label for="status" class="form-label">{{ __('Status') }}</label>
                        <select name="status" id="status" class="form-select">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $currentFilters['status'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2 lg:col-span-3">
                        <label for="sort" class="form-label">{{ __('Sort By') }}</label>
                        <select name="sort" id="sort" class="form-select">
                            <option value="last_heartbeat_at" {{ $currentFilters['sort'] === 'last_heartbeat_at' ? 'selected' : '' }}>
                                {{ __('Last Heartbeat') }}
                            </option>
                            <option value="created_at" {{ $currentFilters['sort'] === 'created_at' ? 'selected' : '' }}>
                                {{ __('Created') }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2 lg:col-span-2">
                        <label for="direction" class="form-label">{{ __('Direction') }}</label>
                        <select name="direction" id="direction" class="form-select">
                            <option value="desc" {{ $currentFilters['direction'] === 'desc' ? 'selected' : '' }}>
                                {{ __('Desc') }}
                            </option>
                            <option value="asc" {{ $currentFilters['direction'] === 'asc' ? 'selected' : '' }}>
                                {{ __('Asc') }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-12">
                    <div class="space-y-2 md:col-span-12 filter-box-actions">
                        <span class="form-label text-transparent">{{ __('Actions') }}</span>
                        <div class="form-actions-cluster">
                            <button type="submit" class="btn btn-primary btn-sm flex-1 justify-center gap-2">
                                <x-icon name="search" class="h-4 w-4" />
                                {{ __('Filter') }}
                            </button>
                            <a href="{{ route('sessions.index') }}" class="btn btn-secondary btn-sm justify-center gap-2">
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
                                <p class="active-filters__subtitle">{{ __('Remove a single filter or clear the whole session query without changing routes.') }}</p>
                            </div>
                            <a href="{{ route('sessions.index') }}" class="active-filters__clear">
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
                                    <x-filter-badge :label="__('Status:').' '.$statusLabel" color="blue" :removeUrl="request()->fullUrlWithQuery(['status' => null])" />
                                @endif
                            @endif

                            @if (request()->filled('search'))
                                @php
                                    $searchFilterLabel = __('Search:').' "'.request('search').'"';
                                @endphp
                                <x-filter-badge :label="$searchFilterLabel" color="purple" :removeUrl="request()->fullUrlWithQuery(['search' => null])" />
                            @endif

                            @if ($currentFilters['sort'] !== 'last_heartbeat_at' || $currentFilters['direction'] !== 'desc')
                                @php
                                    $sortLabel = match ($currentFilters['sort']) {
                                        'last_heartbeat_at' => __('Last Heartbeat'),
                                        'created_at' => __('Created'),
                                        default => ucfirst((string) $currentFilters['sort']),
                                    };

                                    $directionLabel = $currentFilters['direction'] === 'asc' ? __('Ascending') : __('Descending');
                                @endphp

                                <x-filter-badge
                                    :label="__('Sort:').' '.$sortLabel.' · '.$directionLabel"
                                    color="yellow"
                                    :removeUrl="request()->fullUrlWithQuery(['sort' => null, 'direction' => null])"
                                />
                            @endif
                        </div>
                    </div>
                @endif
            </x-filter-box>

            <x-table
                :headers="$sessionHeaders"
                :emptyColspan="$isAdmin ? 5 : 4"
                compact="true"
                ariaLabel="{{ __('Sessions table') }}"
            >
                @forelse ($sessions as $session)
                    @php
                        $deviceHash = (string) ($session->device->hwid_hash ?? '');
                        $deviceHashPreview = $deviceHash !== ''
                            ? \Illuminate\Support\Str::limit($deviceHash, 20, '...')
                            : null;
                        $clientVersion = (string) ($session->client_version ?? __('Unknown'));
                        $clientVersionPreview = \Illuminate\Support\Str::limit($clientVersion, 22, '...');
                    @endphp

                    <tr class="table-row">
                        @if ($isAdmin)
                            <td class="table-cell-primary">
                                @if ($session->account)
                                    <div class="flex items-center gap-3">
                                        <div class="table-avatar">
                                            {{ $session->account->initials() }}
                                        </div>

                                        <div class="table-stack table-stack-tight min-w-0">
                                            <div class="table-title table-truncate table-truncate-md text-sm" title="{{ $session->account->username }}">{{ $session->account->username }}</div>
                                            <div class="table-meta table-truncate table-truncate-md" title="{{ $session->account->email }}">
                                                {{ $session->account->email }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="table-meta">{{ __('Unknown') }}</span>
                                @endif
                            </td>
                        @endif

                        <td class="table-cell">
                            <div class="table-stack table-stack-tight min-w-0">
                                @if ($session->device && $deviceHashPreview)
                                    <button
                                        type="button"
                                        class="badge badge-default table-inline-copy max-w-full transition hover:border-cool-400 hover:text-cool-800 dark:hover:border-cool-500 dark:hover:text-cool-100"
                                        title="{{ $deviceHash }}"
                                        aria-label="{{ __('Copy full device hash') }}"
                                        data-copy-value="{{ $deviceHash }}"
                                        onclick="copyTextValue(this)"
                                    >
                                        <span class="table-truncate table-truncate-md font-mono text-xs sm:text-sm">
                                            {{ $deviceHashPreview }}
                                        </span>
                                    </button>
                                @elseif ($session->device)
                                    <div class="table-title table-truncate table-truncate-md text-sm" title="{{ $session->device->device_name ?? __('Unknown Device') }}">
                                        {{ $session->device->device_name ?? __('Unknown Device') }}
                                    </div>
                                @else
                                    <span class="table-meta">{{ __('Unknown') }}</span>
                                @endif

                                @if ($session->device)
                                    <div class="table-meta">{{ __('Device ID:') }} {{ $session->device->id }}</div>
                                @endif

                                <div class="table-meta table-truncate table-truncate-sm" title="{{ $session->ip_address ?? __('N/A') }}">
                                    {{ $session->ip_address ?? __('N/A') }}
                                </div>

                                <button
                                    type="button"
                                    class="badge badge-default table-inline-copy max-w-full transition hover:border-cool-400 hover:text-cool-800 dark:hover:border-cool-500 dark:hover:text-cool-100"
                                    title="{{ $clientVersion }}"
                                    aria-label="{{ __('Copy full client version') }}"
                                    data-copy-value="{{ $clientVersion }}"
                                    onclick="copyTextValue(this)"
                                >
                                    <span class="table-truncate table-truncate-sm text-xs sm:text-sm">
                                        {{ $clientVersionPreview }}
                                    </span>
                                </button>
                            </div>
                        </td>

                        <td class="table-cell">
                            <div class="table-stack table-stack-tight min-w-0">
                                @if ($session->last_heartbeat_at)
                                    <div>{{ $session->last_heartbeat_at->diffForHumans() }}</div>
                                    <div class="table-meta" title="{{ $session->last_heartbeat_at->format('Y-m-d H:i:s') }}">
                                        {{ __('Heartbeat:') }} {{ $session->last_heartbeat_at->format('Y-m-d H:i') }}
                                    </div>
                                @else
                                    <div>{{ __('Never') }}</div>
                                    <div class="table-meta">{{ __('No heartbeat recorded') }}</div>
                                @endif

                                @if ($session->created_at)
                                    <div class="table-meta" title="{{ $session->created_at->format('Y-m-d H:i:s') }}">
                                        {{ __('Created:') }} {{ $session->created_at->format('Y-m-d H:i') }}
                                    </div>
                                @else
                                    <div class="table-meta">{{ __('Created:') }} {{ __('Unknown') }}</div>
                                @endif
                            </div>
                        </td>

                        <td class="table-cell table-cell-fit">
                            <div class="table-stack table-stack-tight">
                                <x-status-badge :status="$session->isActive() ? 'active' : 'error'" :text="$session->isActive() ? __('Active') : __('Expired')" />

                                @if ($session->device?->bound_at)
                                    <span class="table-meta">{{ __('Bound') }} {{ $session->device->bound_at->diffForHumans() }}</span>
                                @else
                                    <span class="table-meta">{{ __('No binding metadata') }}</span>
                                @endif
                            </div>
                        </td>

                        <td class="table-cell table-cell-fit text-right">
                            <div class="table-actions" aria-label="{{ __('Session row actions') }}">
                                <a href="{{ route('sessions.show', $session) }}" class="table-action table-action--primary">
                                    {{ __('View') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="{{ $isAdmin ? 5 : 4 }}" class="table-empty">
                            <div class="table-empty-state">
                                <x-icon name="server" class="table-empty-icon" />
                                <p class="table-empty-title">{{ __('No sessions found.') }}</p>
                                <p class="table-empty-copy">
                                    {{ __('Adjust the heartbeat filters or reset the query to surface more sessions.') }}
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div>
                <x-pagination :paginator="$sessions" />
            </div>
        </section>
    </div>
</x-app-sidebar-layout>

<script>
    function copyTextValue(element) {
        const value = element?.getAttribute('data-copy-value') ?? '';
        if (!value) {
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
