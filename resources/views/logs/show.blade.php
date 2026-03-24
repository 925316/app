<x-app-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Log Details') }}
        </h2>
    </x-slot>

    <div>
            <div class="card-shell overflow-hidden">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Log Header -->
                    <div class="mb-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Log Entry:') }} {{ $log->event_type }}</h3>
                                <div class="mt-2 flex items-center gap-4">
                                    <span
                                        class="px-3 py-1 rounded-full text-sm font-medium @if ($log->event_level == 0) bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 @elseif($log->event_level == 1) bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 @else bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100 @endif">
                                        {{ $log->event_level == 0 ? __('Info') : ($log->event_level == 1 ? __('Warning') : __('Error')) }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-300">
                                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                                    {{ __('Back to Logs') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Log Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-200/60 dark:border-zinc-700/60">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">{{ __('Basic Information') }}</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Event Type:') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $log->event_type }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Event Level:') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        @if ($log->event_level == 0)
                                            {{ __('Info') }}
                                        @elseif($log->event_level == 1)
                                            {{ __('Warning') }}
                                        @else
                                            {{ __('Error') }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Timestamp:') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $log->created_at->format('Y-m-d H:i:s') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('IP Address:') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $log->ip_address }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-200/60 dark:border-zinc-700/60">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">{{ __('Related Entities') }}</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Account:') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        @if ($log->account)
                                            <a href="#" class="text-zinc-600 dark:text-zinc-300 hover:underline">
                                                {{ $log->account->username }}
                                            </a>
                                        @else
                                            {{ __('System') }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Actor:') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        @if ($log->actor)
                                            <a href="#" class="text-zinc-600 dark:text-zinc-300 hover:underline">
                                                {{ $log->actor->username }}
                                            </a>
                                        @else
                                            {{ __('System') }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('License:') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        @if ($log->license)
                                            <a href="{{ route('licenses.show', $log->license) }}"
                                                class="text-zinc-600 dark:text-zinc-300 hover:underline">
                                                {{ $log->license->key }}
                                            </a>
                                        @else
                                            {{ __('N/A') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Event Details -->
                    @if ($log->details)
                        <div class="mb-6">
                            <h4 class="font-medium mb-2 text-gray-800 dark:text-gray-200">{{ __('Event Details') }}</h4>
                            <div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-200/60 dark:border-zinc-700/60">
                                <div class="text-sm">
                                    <pre class="bg-white dark:bg-zinc-900 p-4 rounded-lg overflow-x-auto">{{ json_encode($log->details, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Raw Data -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-2 text-gray-800 dark:text-gray-200">{{ __('Raw Data') }}</h4>
                        <div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-200/60 dark:border-zinc-700/60">
                            <div class="text-sm">
                                <pre class="bg-white dark:bg-zinc-900 p-4 rounded-lg overflow-x-auto">{{ json_encode($log->toArray(), JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>

</x-app-sidebar-layout>
