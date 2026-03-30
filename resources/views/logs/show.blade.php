<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Log Details') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Inspect a single system event with the same surface hierarchy used across the management shell while preserving payload readability and navigation behavior.') }}
    </x-slot>

    @php
        $eventBadge = match ($log->event_level) {
            0 => 'info',
            1 => 'warning',
            default => 'danger',
        };

        $eventLevelLabel = match ($log->event_level) {
            0 => __('Info'),
            1 => __('Warning'),
            default => __('Error'),
        };

        $logDetailsJson = $log->details ? json_encode($log->details, JSON_PRETTY_PRINT) : null;
        $logRawJson = json_encode($log->toArray(), JSON_PRETTY_PRINT);
    @endphp

    <div class="space-y-8" data-page="logs-show">
        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Event log') }}</p>
                    <h2 class="app-toolbar-title">{{ $log->event_type }}</h2>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <x-status-badge :status="$eventBadge" :text="$eventLevelLabel" />
                        <span class="badge badge-default">{{ __('ID:') }} {{ $log->id }}</span>
                        <span class="app-shell-body-copy text-sm">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div class="app-toolbar-actions">
                    <a href="{{ route('logs.index') }}" class="btn btn-secondary btn-sm gap-2">
                        <x-icon name="reset" class="h-4 w-4" />
                        {{ __('Back to Logs') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="card-shell-muted space-y-5 p-6">
                    <div>
                        <p class="section-kicker">{{ __('Overview') }}</p>
                        <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('Event basics') }}</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="card-shell-muted space-y-2 p-4 sm:col-span-2">
                            <p class="section-kicker">{{ __('Event type') }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $log->event_type }}</p>
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Event level') }}</p>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-status-badge :status="$eventBadge" :text="$eventLevelLabel" />
                            </div>
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('IP address') }}</p>
                            <p class="font-mono text-sm font-semibold text-gray-900 dark:text-white">{{ $log->ip_address }}</p>
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Logged at') }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $log->created_at->format('Y-m-d H:i:s') }}</p>
                            <p class="app-shell-body-copy text-sm">{{ $log->created_at->diffForHumans() }}</p>
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Last updated') }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $log->updated_at->format('Y-m-d H:i:s') }}</p>
                            <p class="app-shell-body-copy text-sm">{{ $log->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>

                <div class="card-shell-muted space-y-5 p-6">
                    <div>
                        <p class="section-kicker">{{ __('Relationships') }}</p>
                        <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('Related entities') }}</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Account') }}</p>
                            <div class="min-w-0">
                                @if ($log->account)
                                    <a href="#" class="table-title table-truncate text-sm hover:underline" title="{{ $log->account->username }}">
                                        {{ $log->account->username }}
                                    </a>
                                @else
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('System') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Actor') }}</p>
                            <div class="min-w-0">
                                @if ($log->actor)
                                    <a href="#" class="table-title table-truncate text-sm hover:underline" title="{{ $log->actor->username }}">
                                        {{ $log->actor->username }}
                                    </a>
                                @else
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('System') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('License') }}</p>
                            <div class="min-w-0">
                                @if ($log->license)
                                    <a href="{{ route('licenses.show', $log->license) }}" class="table-title table-truncate text-sm hover:underline" title="{{ $log->license->key }}">
                                        {{ $log->license->key }}
                                    </a>
                                @else
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('N/A') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if ($logDetailsJson)
            <section class="card-shell space-y-6">
                <div class="app-toolbar">
                    <div>
                        <p class="section-kicker">{{ __('Payload') }}</p>
                        <h2 class="app-toolbar-title">{{ __('Event details') }}</h2>
                        <p class="app-toolbar-subtitle">{{ __('Keep the structured event payload legible in both themes without changing the underlying data.') }}</p>
                    </div>
                </div>

                <div class="card-shell-muted p-5">
                    <pre class="table-code overflow-x-auto whitespace-pre-wrap break-all bg-transparent text-xs text-gray-700 dark:text-gray-300">{{ $logDetailsJson }}</pre>
                </div>
            </section>
        @endif

        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Payload') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Raw data') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Render the full event record inside the shared shell surfaces so raw inspection still feels part of the same interface.') }}</p>
                </div>
            </div>

            <div class="card-shell-muted p-5">
                <pre class="table-code overflow-x-auto whitespace-pre-wrap break-all bg-transparent text-xs text-gray-700 dark:text-gray-300">{{ $logRawJson }}</pre>
            </div>
        </section>
    </div>
</x-app-sidebar-layout>
