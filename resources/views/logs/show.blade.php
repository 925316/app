<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Log Details') }}
        </h2>
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Log Header -->
                    <div class="mb-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <h3 class="text-lg font-medium">Log Entry: {{ $log->event_type }}</h3>
                                <div class="mt-2 flex items-center gap-4">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium
                                        @if($log->event_level == 0) bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200
                                        @elseif($log->event_level == 1) bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                        @else bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 @endif">
                                        {{ $log->event_level == 0 ? 'Info' : ($log->event_level == 1 ? 'Warning' : 'Error') }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-300">
                                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('logs.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                                    Back to Logs
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Log Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Basic Information</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Event Type:</span>
                                    <span class="font-medium">{{ $log->event_type }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Event Level:</span>
                                    <span class="font-medium">
                                        @if($log->event_level == 0) Info
                                        @elseif($log->event_level == 1) Warning
                                        @else Error
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Timestamp:</span>
                                    <span class="font-medium">{{ $log->created_at->format('Y-m-d H:i:s') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">IP Address:</span>
                                    <span class="font-medium">{{ $log->ip_address }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Related Entities</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Account:</span>
                                    <span class="font-medium">
                                        @if($log->account)
                                            <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $log->account->username }}
                                            </a>
                                        @else
                                            System
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Actor:</span>
                                    <span class="font-medium">
                                        @if($log->actor)
                                            <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $log->actor->username }}
                                            </a>
                                        @else
                                            System
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">License:</span>
                                    <span class="font-medium">
                                        @if($log->license)
                                            <a href="{{ route('licenses.show', $log->license) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $log->license->key }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Event Details -->
                    @if($log->details)
                        <div class="mb-6">
                            <h4 class="font-medium mb-2 text-gray-800 dark:text-gray-200">Event Details</h4>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="text-sm">
                                    <pre class="bg-white dark:bg-gray-800 p-4 rounded overflow-x-auto">{{ json_encode($log->details, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Raw Data -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-2 text-gray-800 dark:text-gray-200">Raw Data</h4>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="text-sm">
                                <pre class="bg-white dark:bg-gray-800 p-4 rounded overflow-x-auto">{{ json_encode($log->toArray(), JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
