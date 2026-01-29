<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('System Logs') }}
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-500/20 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Logs</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $logs->total() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-500/20 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Info</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $logs->filter(fn($l) => $l->event_level === 0)->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-500/20 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Warnings</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $logs->filter(fn($l) => $l->event_level === 1)->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-500/20 rounded-full">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Errors</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $logs->filter(fn($l) => $l->event_level === 2)->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header with actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h3 class="text-lg font-medium">System Event Logs</h3>
                        <button onclick="showClearModal()"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                            Clear Old Logs
                        </button>
                    </div>

                    <!-- Filters -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <form method="GET" action="{{ route('logs.index') }}"
                              class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label for="event_type"
                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event
                                    Type</label>
                                <select name="event_type" id="event_type"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option value="">All Types</option>
                                    @foreach($eventTypes as $type)
                                        <option
                                            value="{{ $type }}" {{ request('event_type') == $type ? 'selected' : '' }}>
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
                                    @foreach($eventLevels as $value => $label)
                                        <option
                                            value="{{ $value }}" {{ request('event_level') == $value ? 'selected' : '' }}>
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
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Timestamp
                                </th>
                                <th scope="col"
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Type
                                </th>
                                <th scope="col"
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Level
                                </th>
                                <th scope="col"
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Account
                                </th>
                                <th scope="col"
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    IP Address
                                </th>
                                <th scope="col"
                                    class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($logs as $log)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $log->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                                @if($log->event_level == 0) bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200
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
                                    <td colspan="6"
                                        class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                                        No logs found.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $logs->links() }}
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
                                        <input type="number" name="days" id="days" min="1" max="365" value="30"
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
                    document.getElementById('clearModal').addEventListener('click', function (e) {
                        if (e.target === this) {
                            hideClearModal();
                        }
                    });
                </script>
            </div>
        </div>
    </div>

</x-app-sidebar-layout>
