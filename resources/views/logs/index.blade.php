<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('System Logs') }}
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (Auth::user()->hasPrivilege(7))
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <x-stat-card title="Total Logs" :value="$statistics['total']" icon="document" iconColor="icon-blue" />
                    <x-stat-card title="Info" :value="$statistics['info']" icon="info" iconColor="icon-blue" />
                    <x-stat-card title="Warnings" :value="$statistics['warning']" icon="warning" iconColor="icon-yellow" />
                    <x-stat-card title="Errors" :value="$statistics['error']" icon="error" iconColor="icon-red" />
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header with actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">System Event Logs</h3>
                        @if (Auth::user()->hasPrivilege(7))
                            <button onclick="showClearModal()"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                                Clear Old Logs
                            </button>
                        @endif
                    </div>

                    <!-- Filters -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <form method="GET" action="{{ route('logs.index') }}" data-clean-form="true"
                            class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label for="event_type"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event
                                    Type</label>
                                <select name="event_type" id="event_type"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option value="">All Types</option>
                                    @foreach ($eventTypes as $type)
                                        <option value="{{ $type }}"
                                            {{ request('event_type') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="event_level"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event
                                    Level</label>
                                <select name="event_level" id="event_level"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option value="">All Levels</option>
                                    @foreach ($eventLevels as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ request('event_level') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="search"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    placeholder="Search by type, IP, or username">
                            </div>

                            <div class="flex gap-2">
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition mt-auto">
                                    Filter
                                </button>
                                <a href="{{ route('logs.index') }}"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition mt-auto">
                                    Reset
                                </a>
                            </div>
                        </form>

                        <!-- Date Range Filter -->
                        <div
                            class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start
                                    Date</label>
                                <input type="datetime-local" name="start_date" id="start_date"
                                    value="{{ request('start_date') }}"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            </div>
                            <div>
                                <label for="end_date"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End
                                    Date</label>
                                <input type="datetime-local" name="end_date" id="end_date"
                                    value="{{ request('end_date') }}"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            </div>
                        </div>
                    </div>

                    <!-- Logs table -->
                    <x-table :headers="['Time', 'Type', 'Level', 'Account', 'IP', 'Actions']" :emptyColspan="6">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $log->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium
                                        @if ($log->event_level == 0) bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200
                                        @elseif($log->event_level == 1) bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                        @else bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 @endif">
                                        {{ $log->event_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $eventLevels[$log->event_level] ?? 'Unknown' }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $log->account?->username ?? 'System' }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('logs.show', $log) }}"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                                    No logs found.
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
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg max-w-md w-full mx-4">
                            <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Clear Old Logs</h3>
                            <form action="{{ route('logs.clear') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="days"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Delete
                                        logs older than</label>
                                    <div class="flex gap-2 items-center">
                                        <input type="number" name="days" id="days" min="1"
                                            max="365" value="30"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        <span class="text-gray-600 dark:text-gray-300">days</span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        This will permanently delete all log entries older than the specified number of
                                        days.
                                    </p>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="hideClearModal()"
                                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                                        Clear Old Logs
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
    </div>

</x-app-sidebar-layout>
