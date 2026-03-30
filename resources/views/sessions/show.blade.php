<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Session Details') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Inspect a single heartbeat session with the same cinematic framing used on the management index while preserving copy and termination behavior.') }}
    </x-slot>

    @php
        $terminateSessionConfirmation = __('Are you sure you want to terminate this session? The client will be disconnected on next heartbeat check. This action cannot be undone.');
        $sessionToken = (string) $session->session_token;
        $sessionTokenPreview = \Illuminate\Support\Str::limit($sessionToken, 28, '...');
        $sessionClientVersion = (string) ($session->client_version ?? __('Unknown'));
        $sessionClientVersionPreview = \Illuminate\Support\Str::limit($sessionClientVersion, 22, '...');
        $sessionClientVersionMetric = \Illuminate\Support\Str::limit($sessionClientVersion, 18, '...');
        $deviceHash = (string) ($session->device->hwid_hash ?? '');
        $deviceHashPreview = $deviceHash !== ''
            ? \Illuminate\Support\Str::limit($deviceHash, 28, '...')
            : null;
        $deviceDisplayName = (string) ($session->device->device_name ?? $session->device->hwid_hash ?? __('Unknown Device'));
    @endphp

    <div class="space-y-8" data-page="sessions-show">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Session statistics') }}">
            <x-stat-card :title="__('Status')" :value="$session->isActive() ? __('Active') : __('Expired')" icon="server" :iconColor="$session->isActive() ? 'icon-green' : 'icon-red'" />
            <x-stat-card :title="__('Last Heartbeat')" :value="$session->last_heartbeat_at ? $session->last_heartbeat_at->diffForHumans() : __('Never')" icon="success" iconColor="icon-blue" />
            <x-stat-card :title="__('Session Age')" :value="$session->age_in_minutes !== null ? number_format($session->age_in_minutes, 2).' '.__('min') : __('Unknown')" icon="document" iconColor="icon-purple" />
            <x-stat-card :title="__('Client')" :value="$sessionClientVersionMetric" icon="desktop" iconColor="icon-orange" />
        </section>

        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Heartbeat Session') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Session') }} #{{ $session->id }}</h2>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <x-status-badge :status="$session->isActive() ? 'active' : 'error'" :text="$session->isActive() ? __('Active') : __('Expired')" />
                        <span class="app-shell-body-copy text-sm">{{ __('Review token, heartbeat, and relationship details without changing session semantics.') }}</span>
                    </div>
                </div>

                <div class="app-toolbar-actions">
                    <a href="{{ route('sessions.index') }}" class="btn btn-secondary btn-sm gap-2">
                        <x-icon name="reset" class="h-4 w-4" />
                        {{ __('Back to Sessions') }}
                    </a>

                    <form action="{{ route('sessions.destroy', $session) }}" method="POST" onsubmit="return confirm('{{ $terminateSessionConfirmation }}')">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger btn-sm gap-2">
                            <x-icon name="trash" class="h-4 w-4" />
                            {{ __('Terminate Session') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="card-shell-muted space-y-5 p-6">
                    <div>
                        <p class="section-kicker">{{ __('Identifiers') }}</p>
                        <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('Session basics') }}</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="card-shell-muted space-y-2 p-4 sm:col-span-2">
                            <p class="section-kicker">{{ __('Session token') }}</p>
                            <div class="min-w-0">
                                <button
                                    type="button"
                                    class="badge badge-default table-inline-copy w-full max-w-full justify-start transition hover:border-cool-400 hover:text-cool-800 dark:hover:border-cool-500 dark:hover:text-cool-100"
                                    title="{{ $sessionToken }}"
                                    aria-label="{{ __('Copy full session token') }}"
                                    data-copy-value="{{ $sessionToken }}"
                                    onclick="copySessionField(this)">
                                    <span class="table-truncate font-mono text-xs sm:text-sm">
                                        {{ $sessionTokenPreview }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('IP address') }}</p>
                            <p class="font-mono text-sm text-gray-900 dark:text-white">{{ $session->ip_address ?? __('N/A') }}</p>
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Client version') }}</p>
                            <div class="min-w-0">
                                <button
                                    type="button"
                                    class="badge badge-default table-inline-copy w-full max-w-full justify-start transition hover:border-cool-400 hover:text-cool-800 dark:hover:border-cool-500 dark:hover:text-cool-100"
                                    title="{{ $sessionClientVersion }}"
                                    aria-label="{{ __('Copy full client version') }}"
                                    data-copy-value="{{ $sessionClientVersion }}"
                                    onclick="copySessionField(this)">
                                    <span class="table-truncate text-xs sm:text-sm">
                                        {{ $sessionClientVersionPreview }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-shell-muted space-y-5 p-6">
                    <div>
                        <p class="section-kicker">{{ __('Timing') }}</p>
                        <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('Heartbeat timeline') }}</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Created at') }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : __('Unknown') }}</p>
                            @if ($session->created_at)
                                <p class="app-shell-body-copy text-sm">{{ $session->created_at->diffForHumans() }}</p>
                            @endif
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Last updated') }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $session->updated_at ? $session->updated_at->format('Y-m-d H:i:s') : __('Unknown') }}</p>
                            @if ($session->updated_at)
                                <p class="app-shell-body-copy text-sm">{{ $session->updated_at->diffForHumans() }}</p>
                            @endif
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Last heartbeat') }}</p>
                            @if ($session->last_heartbeat_at)
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $session->last_heartbeat_at->format('Y-m-d H:i:s') }}</p>
                                <p class="app-shell-body-copy text-sm">{{ $session->last_heartbeat_at->diffForHumans() }}</p>
                            @else
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Never') }}</p>
                            @endif
                        </div>

                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Since last heartbeat') }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $session->time_since_last_heartbeat !== null ? number_format($session->time_since_last_heartbeat, 2).' '.__('minutes') : __('Never') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="card-shell-muted space-y-5 p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="section-kicker">{{ __('Relationship') }}</p>
                        <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('Related account') }}</h3>
                    </div>

                    @if ($session->account)
                        <a href="{{ route('accounts.show', $session->account) }}" class="btn btn-primary btn-sm gap-2">
                            <x-icon name="users" class="h-4 w-4" />
                            {{ __('View Account') }}
                        </a>
                    @endif
                </div>

                @if ($session->account)
                    <div class="card-shell-muted p-5">
                        <div class="flex items-center gap-4">
                            <div class="table-avatar">
                                {{ $session->account->initials() }}
                            </div>

                            <div class="table-stack min-w-0">
                                <div class="table-title table-truncate text-base" title="{{ $session->account->username }}">{{ $session->account->username }}</div>
                                <div class="table-meta break-all">{{ $session->account->email }}</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-shell-muted p-5">
                        <p class="app-shell-body-copy text-sm">{{ __('No account is associated with this session. This can happen when the related account has been removed.') }}</p>
                    </div>
                @endif
            </div>

            <div class="card-shell-muted space-y-5 p-6">
                <div>
                    <p class="section-kicker">{{ __('Relationship') }}</p>
                    <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('Related device') }}</h3>
                </div>

                @if ($session->device)
                    <div class="card-shell-muted p-5">
                        <div class="flex items-start gap-4">
                            <span class="card-icon-container icon-purple shrink-0">
                                <x-icon name="desktop" class="h-6 w-6" />
                            </span>

                            <div class="table-stack min-w-0 gap-3">
                                <div class="table-title break-words text-base" title="{{ $deviceDisplayName }}">{{ $deviceDisplayName }}</div>
                                <div class="table-meta">{{ __('Device ID:') }} {{ $session->device->id }}</div>
                                @if ($deviceHashPreview)
                                    <div class="min-w-0">
                                        <button
                                            type="button"
                                            class="badge badge-default table-inline-copy w-full max-w-full justify-start transition hover:border-cool-400 hover:text-cool-800 dark:hover:border-cool-500 dark:hover:text-cool-100"
                                            title="{{ $deviceHash }}"
                                            aria-label="{{ __('Copy full device hash') }}"
                                            data-copy-value="{{ $deviceHash }}"
                                            onclick="copySessionField(this)">
                                            <span class="table-truncate font-mono text-xs sm:text-sm">
                                                {{ $deviceHashPreview }}
                                            </span>
                                        </button>
                                    </div>
                                @endif
                                @if ($session->device->bound_at)
                                    <div class="table-meta">{{ __('Bound since:') }} {{ $session->device->bound_at->format('Y-m-d H:i:s') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-shell-muted p-5">
                        <p class="app-shell-body-copy text-sm">{{ __('No device is associated with this session. This may indicate a deleted device or an unbound session.') }}</p>
                    </div>
                @endif
            </div>
        </section>
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
