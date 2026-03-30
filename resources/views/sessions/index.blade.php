<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Session Management') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Track live heartbeats, narrow the session stream, and keep termination controls intact.') }}
    </x-slot>

    @php
        $terminateSessionConfirmation = __('Are you sure you want to terminate this session? The client will be disconnected on next heartbeat check. This action cannot be undone.');
        $sessionTitle = $isAdmin ? __('Session management') : __('My sessions');
        $sessionSubtitle = $isAdmin
            ? __('Monitor account activity, filter the heartbeat stream, and keep the same query behavior.')
            : __('Review your active and expired sessions without changing the existing filters or actions.');
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
                :showTotal="true"
                :totalCount="$sessions->total()"
                :title="__('Filter sessions')"
                defaultValues="sort:last_heartbeat_at,direction:desc"
            >
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="space-y-2">
                        <label for="status" class="form-label">{{ __('Status') }}</label>
                        <select name="status" id="status" class="form-select">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $currentFilters['status'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label for="sort" class="form-label">{{ __('Sort By') }}</label>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_7rem]">
                            <select name="sort" id="sort" class="form-select">
                                <option value="last_heartbeat_at" {{ $currentFilters['sort'] === 'last_heartbeat_at' ? 'selected' : '' }}>
                                    {{ __('Last Heartbeat') }}
                                </option>
                                <option value="created_at" {{ $currentFilters['sort'] === 'created_at' ? 'selected' : '' }}>
                                    {{ __('Created') }}
                                </option>
                            </select>

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
                </div>

                <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-12">
                    <div class="space-y-2 md:col-span-8">
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

                    <div class="space-y-2 md:col-span-4">
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
                                <x-filter-badge :label="__('Search:').' \"'.request('search').'\"'" color="purple" :removeUrl="request()->fullUrlWithQuery(['search' => null])" />
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
                :headers="$isAdmin
                    ? [__('Account'), __('Device'), __('IP Address'), __('Client Version'), __('Status'), __('Last Heartbeat'), __('Created'), __('Actions')]
                    : [__('Device'), __('IP Address'), __('Client Version'), __('Status'), __('Last Heartbeat'), __('Created'), __('Actions')]"
                :emptyColspan="$isAdmin ? 8 : 7"
                ariaLabel="{{ __('Sessions table') }}"
            >
                @forelse ($sessions as $session)
                    <tr class="table-row">
                        @if ($isAdmin)
                            <td class="table-cell-primary whitespace-nowrap">
                                @if ($session->account)
                                    <div class="flex items-center gap-3">
                                        <div class="table-avatar">
                                            {{ $session->account->initials() }}
                                        </div>

                                        <div class="table-stack table-stack-tight">
                                            <div class="table-title text-sm">{{ $session->account->username }}</div>
                                            <div class="table-meta max-w-[240px] truncate" title="{{ $session->account->email }}">
                                                {{ $session->account->email }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="table-meta">{{ __('Unknown') }}</span>
                                @endif
                            </td>
                        @endif

                        <td class="table-cell whitespace-nowrap">
                            @if ($session->device)
                                <div class="table-stack table-stack-tight">
                                    <div class="table-title max-w-[220px] truncate text-sm" title="{{ $session->device->hwid_hash ?? __('Unknown Device') }}">
                                        {{ $session->device->hwid_hash ?? __('Unknown Device') }}
                                    </div>
                                    <div class="table-meta">{{ __('ID:') }} {{ $session->device->id }}</div>
                                </div>
                            @else
                                <span class="table-meta">{{ __('Unknown') }}</span>
                            @endif
                        </td>

                        <td class="table-cell whitespace-nowrap">
                            <span class="table-meta inline-block max-w-[180px] truncate align-middle" title="{{ $session->ip_address ?? __('N/A') }}">
                                {{ $session->ip_address ?? __('N/A') }}
                            </span>
                        </td>

                        <td class="table-cell whitespace-nowrap">
                            @php
                                $clientVersion = (string) ($session->client_version ?? __('Unknown'));
                                $clientVersionPreview = \Illuminate\Support\Str::limit($clientVersion, 24, '...');
                            @endphp

                            <button
                                type="button"
                                class="badge badge-default inline-flex max-w-[190px] items-center truncate text-left transition hover:border-cool-400 hover:text-cool-800 dark:hover:border-cool-500 dark:hover:text-cool-100"
                                title="{{ $clientVersion }}"
                                data-copy-value="{{ $clientVersion }}"
                                onclick="copyTextValue(this)"
                            >
                                {{ $clientVersionPreview }}
                            </button>
                        </td>

                        <td class="table-cell whitespace-nowrap">
                            <x-status-badge :status="$session->isActive() ? 'active' : 'error'" :text="$session->isActive() ? __('Active') : __('Expired')" />
                        </td>

                        <td class="table-cell whitespace-nowrap">
                            @if ($session->last_heartbeat_at)
                                <div class="table-stack table-stack-tight">
                                    <div>{{ $session->last_heartbeat_at->diffForHumans() }}</div>
                                    <div class="table-meta">{{ $session->last_heartbeat_at->format('Y-m-d H:i:s') }}</div>
                                </div>
                            @else
                                <span class="table-meta">{{ __('Never') }}</span>
                            @endif
                        </td>

                        <td class="table-cell whitespace-nowrap">
                            @if ($session->created_at)
                                <div class="table-stack table-stack-tight">
                                    <div>{{ $session->created_at->diffForHumans() }}</div>
                                    <div class="table-meta">{{ $session->created_at->format('Y-m-d H:i:s') }}</div>
                                </div>
                            @else
                                <span class="table-meta">{{ __('Unknown') }}</span>
                            @endif
                        </td>

                        <td class="table-cell whitespace-nowrap text-right">
                            <div class="table-actions table-actions--nowrap">
                                <a href="{{ route('sessions.show', $session) }}" class="table-action table-action--primary">
                                    {{ __('View') }}
                                </a>

                                <form action="{{ route('sessions.destroy', $session) }}" method="POST" class="inline" onsubmit="return confirm('{{ $terminateSessionConfirmation }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-action table-action--danger">
                                        {{ __('Terminate') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="{{ $isAdmin ? 8 : 7 }}" class="table-empty">
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
