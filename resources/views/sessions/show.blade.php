<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Session Details') }}
    </x-slot>

    @php
        $terminateSessionConfirmation = __('Are you sure you want to terminate this session? The client will be disconnected on next heartbeat check. This action cannot be undone.');
    @endphp

    <div class="mx-auto max-w-7xl py-7">
            <!-- Breadcrumb and Actions -->
            <div class="mb-6 flex justify-between items-center gap-3 lg:max-xl:flex-wrap">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('sessions.index') }}" class="text-cool-700 dark:text-cool-300 hover:underline text-sm">
                        {{ __('Back to Sessions') }}
                    </a>
                </div>
                <form action="{{ route('sessions.destroy', $session) }}" method="POST"
                    onsubmit="return confirm('{{ $terminateSessionConfirmation }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger text-sm">
                        {{ __('Terminate Session') }}
                    </button>
                </form>
            </div>

            <div class="card-shell overflow-hidden">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-6">
                    <!-- Session Header -->
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="section-kicker">{{ __('Heartbeat Session') }}</p>
                            <h3 class="text-2xl font-bold mb-2">{{ __('Session') }} #{{ $session->id }}</h3>
                            <div class="flex items-center space-x-2">
                                @if ($session->isActive())
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        {{ __('Active') }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                        {{ __('Expired') }}
                                    </span>
                                @endif
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    {{ __('Client:') }} {{ $session->client_version ?? __('Unknown') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Session Details Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="card-shell-muted">
                            <h4 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Basic Information') }}</h4>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Session Token') }}</dt>
                                    @php
                                        $sessionToken = (string) $session->session_token;
                                        $sessionTokenPreview = \Illuminate\Support\Str::limit($sessionToken, 24, '...');
                                    @endphp
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">
                                        <button
                                            type="button"
                                            class="max-w-[280px] truncate text-left hover:text-blue-600 dark:hover:text-blue-300"
                                            title="{{ $sessionToken }}"
                                            data-copy-value="{{ $sessionToken }}"
                                            onclick="copySessionField(this)">
                                            {{ $sessionTokenPreview }}
                                        </button>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('IP Address') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">
                                        {{ $session->ip_address ?? __('N/A') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Client Version') }}</dt>
                                    @php
                                        $sessionClientVersion = (string) ($session->client_version ?? __('Unknown'));
                                        $sessionClientVersionPreview = \Illuminate\Support\Str::limit($sessionClientVersion, 18, '...');
                                    @endphp
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        <button
                                            type="button"
                                            class="max-w-[180px] truncate text-left hover:text-blue-600 dark:hover:text-blue-300"
                                            title="{{ $sessionClientVersion }}"
                                            data-copy-value="{{ $sessionClientVersion }}"
                                            onclick="copySessionField(this)">
                                            {{ $sessionClientVersionPreview }}
                                        </button>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Timing Information -->
                        <div class="card-shell-muted">
                            <h4 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Timing Information') }}</h4>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created At') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : __('Unknown') }}
                                        @if ($session->created_at)
                                            <span class="text-xs text-gray-500 dark:text-gray-400 block mt-1">
                                                ({{ $session->created_at->diffForHumans() }})
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Last Updated') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $session->updated_at ? $session->updated_at->format('Y-m-d H:i:s') : __('Unknown') }}
                                        @if ($session->updated_at)
                                            <span class="text-xs text-gray-500 dark:text-gray-400 block mt-1">
                                                ({{ $session->updated_at->diffForHumans() }})
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Last Heartbeat') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        @if ($session->last_heartbeat_at)
                                            {{ $session->last_heartbeat_at->format('Y-m-d H:i:s') }}
                                            <span class="text-xs text-gray-500 dark:text-gray-400 block mt-1">
                                                ({{ $session->last_heartbeat_at->diffForHumans() }})
                                            </span>
                                        @else
                                            <span class="text-gray-500">{{ __('Never') }}</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Session Age') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        @if ($session->age_in_minutes !== null)
                                            {{ number_format($session->age_in_minutes, 2) }} {{ __('minutes') }}
                                        @else
                                            {{ __('Unknown') }}
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Time Since Last Heartbeat') }}
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        @if ($session->time_since_last_heartbeat !== null)
                                            {{ number_format($session->time_since_last_heartbeat, 2) }} {{ __('minutes') }}
                                        @else
                                            {{ __('Never') }}
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Related Accounts -->
                    @if ($session->account)
                        <div class="card-shell-muted p-6">
                            <h4 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Related Account') }}</h4>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        <div
                                            class="h-12 w-12 rounded-full bg-cool-600 dark:bg-cool-500 flex items-center justify-center text-white font-bold text-lg">
                                            {{ $session->account->initials() }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $session->account->username }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $session->account->email }}
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('accounts.show', $session->account) }}" class="btn btn-blue text-sm">
                                    {{ __('View Account') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="card-shell-muted p-6">
                            <h4 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Related Account') }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No account associated with this session. This may indicate a deleted account.') }}</p>
                        </div>
                    @endif

                    <!-- Related Device -->
                    @if ($session->device)
                        <div class="card-shell-muted p-6">
                            <h4 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Related Device') }}</h4>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        <div
                                            class="h-12 w-12 rounded-full bg-purple-500 flex items-center justify-center text-white">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $session->device->device_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('Device ID:') }} {{ $session->device->id }}
                                        </div>
                                        @if ($session->device->bound_at)
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ __('Bound since:') }} {{ $session->device->bound_at->format('Y-m-d H:i:s') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card-shell-muted p-6">
                            <h4 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">{{ __('Related Device') }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No device associated with this session. This may indicate a deleted device or an unbound session.') }}</p>
                        </div>
                    @endif

                </div>
            </div>
    </div>

</x-app-sidebar-layout>

<script>
    function copySessionField(element) {
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
