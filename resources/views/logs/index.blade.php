<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('System Logs') }}
    </x-slot>

    <div>
            @if (Auth::user()->hasPrivilege(7))
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <x-stat-card :title="__('Total Logs')" :value="$statistics['total']" icon="document" iconColor="icon-gray" />
                    <x-stat-card :title="__('Info')" :value="$statistics['info']" icon="info" iconColor="icon-gray" />
                    <x-stat-card :title="__('Warnings')" :value="$statistics['warning']" icon="warning" iconColor="icon-yellow" />
                    <x-stat-card :title="__('Errors')" :value="$statistics['error']" icon="error" iconColor="icon-red" />
                </div>
            @endif

            <div class="card-shell overflow-hidden">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header with actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 lg:max-xl:flex-wrap">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('System Event Logs') }}</h3>
                        @if (Auth::user()->hasPrivilege(7))
                            <button onclick="showClearModal()" class="btn btn-danger">
                                {{ __('Clear Old Logs') }}
                            </button>
                        @endif
                    </div>

                    <!-- Filters -->
                    <x-filter-box :action="route('logs.index')" :showTotal="true" :totalCount="$logs->total()" :title="__('Filter Logs')">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                            <div class="space-y-2">
                                <label for="event_type"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Event Type') }}</label>
                                <select name="event_type" id="event_type" class="form-select form-pill form-select-enhanced">
                                    <option value="">{{ __('All Types') }}</option>
                                    @foreach ($eventTypes as $type)
                                        <option value="{{ $type }}" {{ request('event_type') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="event_level"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Event Level') }}</label>
                                <select name="event_level" id="event_level" class="form-select form-pill form-select-enhanced">
                                    <option value="">{{ __('All Levels') }}</option>
                                    @foreach ($eventLevels as $value => $label)
                                        <option value="{{ $value }}" {{ request('event_level') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="account_id"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Account') }}</label>
                                <select name="account_id" id="account_id" class="form-select form-pill form-select-enhanced">
                                    <option value="">{{ __('All Accounts') }}</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" {{ (string) request('account_id') === (string) $account->id ? 'selected' : '' }}>
                                            {{ $account->username }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="search"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Search') }}</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-input form-pill"
                                    placeholder="{{ __('Search by type, IP, or username') }}">
                            </div>

                            <div class="flex items-end gap-2 xl:justify-end">
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    {{ __('Filter') }}
                                </button>
                                <a href="{{ route('logs.index') }}" class="btn btn-secondary btn-sm">
                                    {{ __('Reset') }}
                                </a>
                            </div>

                            <div
                                class="grid grid-cols-1 gap-4 border-t border-gray-200 pt-4 dark:border-gray-600 md:grid-cols-2 xl:col-span-5">
                                <div class="space-y-2">
                                    <label for="start_date"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Start Date') }}</label>
                                    <input type="datetime-local" name="start_date" id="start_date" value="{{ request('start_date') }}"
                                        class="form-input form-pill">
                                </div>
                                <div class="space-y-2">
                                    <label for="end_date"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('End Date') }}</label>
                                    <input type="datetime-local" name="end_date" id="end_date" value="{{ request('end_date') }}"
                                        class="form-input form-pill">
                                </div>
                            </div>
                        </div>
                    </x-filter-box>

                    <!-- Logs table -->
                    <x-table :headers="[__('Time'), __('Type'), __('Level'), __('Account'), __('IP'), __('Actions')]" :emptyColspan="6">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $log->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-medium @if ($log->event_level == 0) bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 @elseif($log->event_level == 1) bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 @else bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100 @endif">
                                        {{ $log->event_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $eventLevels[$log->event_level] ?? __('Unknown') }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $log->account?->username ?? __('System') }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('logs.show', $log) }}"
                                        class="text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-zinc-100">
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                                    {{ __('No logs found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </x-table>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <x-pagination :paginator="$logs" />
                    </div>

                    <!-- Clear Logs Modal -->
                    <div id="clearModal"
                        class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                        <div class="card-shell p-6 max-w-md w-full mx-4">
                            <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">{{ __('Clear Old Logs') }}</h3>
                            <form action="{{ route('logs.clear') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="days"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Delete logs older than') }}</label>
                                    <div class="flex gap-2 items-center">
                                        <input type="number" name="days" id="days" min="1" max="365" value="30" class="form-input form-pill">
                                        <span class="text-gray-600 dark:text-gray-300">{{ __('days') }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('This will permanently delete all log entries older than the specified number of days.') }}
                                    </p>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="hideClearModal()" class="btn btn-secondary">
                                        {{ __('Cancel') }}
                                    </button>
                                    <button type="submit" class="btn btn-danger">
                                        {{ __('Clear Old Logs') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>

                <script>
                    function showClearModal() {
                        document.getElementById('clearModal').classList.remove('hidden');
                        document.getElementById('clearModal').classList.add('flex');
                    }

                    function hideClearModal() {
                        document.getElementById('clearModal').classList.add('hidden');
                        document.getElementById('clearModal').classList.remove('flex');
                    }

                    // Close modal when clicking outside
                    document.getElementById('clearModal').addEventListener('click', function(e) {
                        if (e.target === this) {
                            hideClearModal();
                        }
                    });
                </script>
            </div>
    </div>

</x-app-sidebar-layout>
