<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('System Logs') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Inspect event history with the shared filter, table, and modal language.') }}
    </x-slot>

    @php
        $isStaff = Auth::user()->hasPrivilege(7);
        $hasLogFilters = filled(request('event_type')) || filled(request('event_level')) || filled(request('account_id')) || filled(request('search')) || filled(request('start_date')) || filled(request('end_date'));
    @endphp

    <div class="space-y-8" data-page="logs-index">
        @if ($isStaff)
            <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Log statistics') }}">
                <x-stat-card :title="__('Total Logs')" :value="$statistics['total']" icon="document" iconColor="icon-blue" />
                <x-stat-card :title="__('Info')" :value="$statistics['info']" icon="info" iconColor="icon-blue" />
                <x-stat-card :title="__('Warnings')" :value="$statistics['warning']" icon="warning" iconColor="icon-yellow" />
                <x-stat-card :title="__('Errors')" :value="$statistics['error']" icon="error" iconColor="icon-red" />
            </section>
        @endif

        <section class="card-shell space-y-6" data-logs-panel>
            <div class="app-toolbar" data-logs-toolbar>
                <div>
                    <p class="section-kicker">{{ __('Events') }}</p>
                    <h2 class="app-toolbar-title">{{ __('System event logs') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Narrow the event stream without changing the current filter names, routes, or pagination behavior.') }}</p>
                </div>

                @if ($isStaff)
                    <div class="app-toolbar-actions">
                        <button type="button" class="btn btn-danger btn-sm gap-2" x-data @click="$dispatch('open-modal', 'clear-old-logs')">
                            <x-icon name="trash" class="h-4 w-4" />
                            {{ __('Clear Old Logs') }}
                        </button>
                    </div>
                @endif
            </div>

            <x-filter-box :action="route('logs.index')" :title="__('Filter logs')">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5 items-end">
                    <div class="space-y-2">
                        <label for="event_type" class="form-label">{{ __('Event Type') }}</label>
                        <select name="event_type" id="event_type" class="form-select">
                            <option value="">{{ __('All Types') }}</option>
                            @foreach ($eventTypes as $type)
                                <option value="{{ $type }}" {{ request('event_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="event_level" class="form-label">{{ __('Event Level') }}</label>
                        <select name="event_level" id="event_level" class="form-select">
                            <option value="">{{ __('All Levels') }}</option>
                            @foreach ($eventLevels as $value => $label)
                                <option value="{{ $value }}" {{ request('event_level') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="account_id" class="form-label">{{ __('Account') }}</label>
                        <select name="account_id" id="account_id" class="form-select">
                            <option value="">{{ __('All Accounts') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" {{ (string) request('account_id') === (string) $account->id ? 'selected' : '' }}>{{ $account->username }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2 xl:col-span-2">
                        <label for="search" class="form-label">{{ __('Search') }}</label>
                        <x-input-with-icon id="search" name="search" type="text" :value="request('search')"
                            :placeholder="__('Search by type, IP, or username')" icon="search" />
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 items-end border-t border-gray-200 pt-4 dark:border-gray-700">
                    <div class="space-y-2">
                        <label for="start_date" class="form-label">{{ __('Start Date') }}</label>
                        <input type="datetime-local" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-input">
                    </div>

                    <div class="space-y-2">
                        <label for="end_date" class="form-label">{{ __('End Date') }}</label>
                        <input type="datetime-local" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-input">
                    </div>

                    <div class="xl:col-span-2 flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary btn-sm gap-2">
                            <x-icon name="search" class="h-4 w-4" />
                            {{ __('Filter') }}
                        </button>
                        <a href="{{ route('logs.index') }}" class="btn btn-secondary btn-sm gap-2">
                            <x-icon name="reset" class="h-4 w-4" />
                            {{ __('Reset') }}
                        </a>
                    </div>
                </div>

                @if ($hasLogFilters)
                    <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700" data-active-filters>
                        <div class="flex flex-wrap gap-2">
                            @if (filled(request('event_type')))
                                <x-filter-badge :label="__('Type:').' '.request('event_type')" color="blue" :removeUrl="request()->fullUrlWithQuery(['event_type' => null])" />
                            @endif
                            @if (filled(request('event_level')))
                                <x-filter-badge :label="__('Level:').' '.($eventLevels[(int) request('event_level')] ?? request('event_level'))" color="yellow" :removeUrl="request()->fullUrlWithQuery(['event_level' => null])" />
                            @endif
                            @if (filled(request('account_id')))
                                @php $selectedAccount = $accounts->firstWhere('id', (int) request('account_id')); @endphp
                                <x-filter-badge :label="__('Account:').' '.($selectedAccount?->username ?? request('account_id'))" color="green" :removeUrl="request()->fullUrlWithQuery(['account_id' => null])" />
                            @endif
                            @if (filled(request('search')))
                                <x-filter-badge :label="__('Search:').' \"'.request('search').'\"'" color="purple" :removeUrl="request()->fullUrlWithQuery(['search' => null])" />
                            @endif
                            @if (filled(request('start_date')))
                                <x-filter-badge :label="__('Start:').' '.request('start_date')" color="orange" :removeUrl="request()->fullUrlWithQuery(['start_date' => null])" />
                            @endif
                            @if (filled(request('end_date')))
                                <x-filter-badge :label="__('End:').' '.request('end_date')" color="orange" :removeUrl="request()->fullUrlWithQuery(['end_date' => null])" />
                            @endif
                        </div>
                    </div>
                @endif
            </x-filter-box>

            <x-table :headers="[__('Time'), __('Type'), __('Level'), __('Account'), __('IP'), __('Actions')]" :emptyColspan="6" ariaLabel="{{ __('Logs table') }}">
                @forelse ($logs as $log)
                    <tr class="table-row">
                        <td class="table-cell whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        <td class="table-cell whitespace-nowrap">
                            @php
                                $eventBadge = match ($log->event_level) {
                                    0 => 'info',
                                    1 => 'warning',
                                    default => 'danger',
                                };
                            @endphp
                            <x-status-badge :status="$eventBadge" :text="$log->event_type" />
                        </td>
                        <td class="table-cell whitespace-nowrap">{{ $eventLevels[$log->event_level] ?? __('Unknown') }}</td>
                        <td class="table-cell whitespace-nowrap">{{ $log->account?->username ?? __('System') }}</td>
                        <td class="table-cell whitespace-nowrap">{{ $log->ip_address }}</td>
                        <td class="table-cell whitespace-nowrap text-right">
                            <a href="{{ route('logs.show', $log) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                {{ __('View') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="6" class="table-empty">{{ __('No logs found.') }}</td>
                    </tr>
                @endforelse
            </x-table>

            <div>
                <x-pagination :paginator="$logs" />
            </div>
        </section>

        @if ($isStaff)
            <x-modal name="clear-old-logs" :show="false" maxWidth="md">
                <div class="modal-header">
                    <div class="flex items-start gap-4">
                        <span class="card-icon-container icon-red shrink-0">
                            <x-icon name="trash" class="h-6 w-6" />
                        </span>

                        <div class="space-y-1">
                            <p class="section-kicker">{{ __('Maintenance') }}</p>
                            <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('Clear Old Logs') }}</h3>
                        </div>
                    </div>
                </div>

                <form action="{{ route('logs.clear') }}" method="POST" data-clear-logs-form>
                    @csrf

                    <div class="modal-body space-y-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ __('This will permanently delete all log entries older than the specified number of days.') }}
                        </p>

                        <div class="space-y-2">
                            <label for="days" class="form-label">{{ __('Delete logs older than') }}</label>
                            <div class="flex items-center gap-3">
                                <input type="number" name="days" id="days" min="1" max="365" value="30" class="form-input">
                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ __('days') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'clear-old-logs')">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <x-danger-button class="gap-2">
                            <x-icon name="trash" class="h-4 w-4" />
                            {{ __('Clear Old Logs') }}
                        </x-danger-button>
                    </div>
                </form>
            </x-modal>
        @endif
    </div>
</x-app-sidebar-layout>
