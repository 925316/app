<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('My Devices') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Review your current binding, keep unbind behavior intact, and browse device history through the shared cinematic table system.') }}
    </x-slot>

    @php
        $unbindDeviceConfirmation = __('Unbind this device?');
        $historyTotal = $devices->total();
        $visibleHistoryCount = $devices->count();
        $currentDeviceShortHwid = $currentDevice?->hwid_hash
            ? \Illuminate\Support\Str::limit($currentDevice->hwid_hash, 18, '...')
            : null;
    @endphp

    <div class="space-y-8" data-page="devices-index">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3" aria-label="{{ __('Device statistics') }}">
            <x-stat-card :title="__('Total History Entries')" :value="$historyTotal" icon="desktop" iconColor="icon-blue" />
            <x-stat-card :title="__('Visible On This Page')" :value="$visibleHistoryCount" icon="info" iconColor="icon-purple" />
            <x-stat-card :title="__('Current Binding')" :value="$currentDevice ? __('Bound') : __('None')" :icon="$currentDevice ? 'success' : 'warning'" :iconColor="$currentDevice ? 'icon-green' : 'icon-yellow'" />
        </section>

        <section class="card-shell space-y-6" data-devices-panel>
            <div class="app-toolbar" data-devices-toolbar>
                <div>
                    <p class="section-kicker">{{ __('Binding History') }}</p>
                    <h2 class="app-toolbar-title">{{ __('My device history') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Surface the same current binding and unbind controls with the shared summary, action, and empty-state language.') }}</p>
                </div>

                <div class="app-toolbar-actions">
                    <a href="{{ route('devices.manage') }}" class="btn btn-primary btn-sm gap-2">
                        <x-icon name="desktop" class="h-4 w-4" />
                        {{ __('Manage Device') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(18rem,1fr)]">
                <div class="card-shell-muted space-y-5 p-6">
                    @if ($currentDevice)
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-status-badge status="active" :text="__('Currently Bound')" />
                                    <span class="badge badge-info">{{ __('Live binding') }}</span>
                                </div>

                                <div>
                                    <p class="section-kicker">{{ __('Current device') }}</p>
                                    <h3 class="card-heading text-2xl font-semibold text-gray-900 dark:text-white">
                                        <span title="{{ $currentDevice->hwid_hash }}" class="cursor-help font-mono">
                                            {{ $currentDeviceShortHwid }}
                                        </span>
                                    </h3>
                                </div>
                            </div>

                            <div class="card-shell-muted min-w-[14rem] space-y-2 self-start p-4">
                                <p class="section-kicker">{{ __('Bound since') }}</p>
                                <p class="card-heading text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $currentDevice->bound_at ? $currentDevice->bound_at->format('Y-m-d H:i') : __('Unknown') }}
                                </p>
                                @if ($currentDevice->bound_at)
                                    <p class="app-shell-body-copy text-sm">{{ $currentDevice->bound_at->diffForHumans() }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="card-shell-muted space-y-2 p-4">
                                <p class="section-kicker">{{ __('IP address') }}</p>
                                <p class="card-heading text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $currentDevice->ip_address ?? __('Unknown') }}
                                </p>
                            </div>

                            <div class="card-shell-muted space-y-2 p-4">
                                <p class="section-kicker">{{ __('Region') }}</p>
                                <p class="card-heading text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $currentDevice->country_code ?? __('Unknown') }}
                                </p>
                            </div>

                            <div class="card-shell-muted space-y-2 p-4">
                                <p class="section-kicker">{{ __('Last seen') }}</p>
                                <p class="card-heading text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $currentDevice->last_seen_at ? $currentDevice->last_seen_at->format('Y-m-d H:i') : __('Unknown') }}
                                </p>
                                @if ($currentDevice->last_seen_at)
                                    <p class="app-shell-body-copy text-sm">{{ $currentDevice->last_seen_at->diffForHumans() }}</p>
                                @endif
                            </div>
                        </div>

                        <form action="{{ route('devices.unbind') }}" method="POST" onsubmit="return confirm('{{ $unbindDeviceConfirmation }}')">
                            @csrf

                            <div class="form-actions-cluster justify-start">
                                <x-danger-button class="btn-sm">
                                    {{ __('Unbind Current Device') }}
                                </x-danger-button>
                            </div>
                        </form>
                    @else
                        <div class="flex items-start gap-4">
                            <span class="card-icon-container icon-yellow shrink-0">
                                <x-icon name="warning" class="h-6 w-6" />
                            </span>

                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-status-badge status="warning" :text="__('No Device Bound')" />
                                </div>

                                <div>
                                    <p class="section-kicker">{{ __('Binding status') }}</p>
                                    <h3 class="card-heading text-xl font-semibold text-gray-900 dark:text-white">{{ __('No active device binding') }}</h3>
                                </div>

                                <p class="app-shell-body-copy text-sm">
                                    {{ __('You have not bound any device to your account yet. Use the management surface to bind a device without changing the current routes or ownership rules.') }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <aside class="card-shell-muted flex flex-col justify-between gap-6 p-6">
                    <div class="space-y-3">
                        <p class="section-kicker">{{ __('Control surface') }}</p>
                        <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('Binding actions') }}</h3>
                        <p class="app-shell-body-copy text-sm">
                            {{ $currentDevice
                                ? __('Open device management to review the full binding details, reset availability, and the same unbind controls in one place.')
                                : __('Open device management to bind your first device and review reset availability from the same shared control surface.') }}
                        </p>
                    </div>

                    <div class="grid gap-3">
                        <a href="{{ route('devices.manage') }}" class="btn btn-primary btn-sm justify-center gap-2">
                            <x-icon name="desktop" class="h-4 w-4" />
                            {{ __('Open Device Management') }}
                        </a>

                        <a href="{{ route('devices.index') }}" class="btn btn-secondary btn-sm justify-center gap-2">
                            <x-icon name="info" class="h-4 w-4" />
                            {{ __('Refresh History') }}
                        </a>
                    </div>
                </aside>
            </div>

            <x-table :headers="[__('HWID'), __('IP / Country'), __('First / Last Seen'), __('Status'), __('Actions')]" :emptyColspan="5" ariaLabel="{{ __('Device history table') }}">
                @forelse ($devices as $device)
                    <tr class="table-row">
                        <td class="table-cell-primary whitespace-nowrap">
                            @if ($device->hwid_hash)
                                <div class="table-stack table-stack-tight">
                                    <div class="table-title text-sm font-mono">
                                        <span title="{{ $device->hwid_hash }}" class="cursor-help">
                                            {{ \Illuminate\Support\Str::limit($device->hwid_hash, 18, '...') }}
                                        </span>
                                    </div>
                                    <div class="table-meta">{{ __('Device ID:') }} {{ $device->id }}</div>
                                </div>
                            @else
                                <span class="table-meta">{{ __('N/A') }}</span>
                            @endif
                        </td>

                        <td class="table-cell whitespace-nowrap">
                            <div class="table-stack table-stack-tight">
                                <div>{{ $device->ip_address ?? __('Unknown') }}</div>
                                <div class="table-meta">{{ $device->country_code ?? __('Unknown') }}</div>
                            </div>
                        </td>

                        <td class="table-cell whitespace-nowrap">
                            <div class="table-stack table-stack-tight">
                                <div>{{ __('First:') }} {{ $device->first_seen_at ? $device->first_seen_at->format('Y-m-d H:i') : __('Unknown') }}</div>
                                <div class="table-meta">{{ __('Last:') }} {{ $device->last_seen_at ? $device->last_seen_at->format('Y-m-d H:i') : __('Unknown') }}</div>
                            </div>
                        </td>

                        <td class="table-cell whitespace-nowrap">
                            @if ($device->bound_at && ! $device->unbound_at)
                                <x-status-badge status="active" :text="__('Currently Bound')" />
                            @else
                                <x-status-badge status="default" :text="__('Historical')" />
                            @endif
                        </td>

                        <td class="table-cell whitespace-nowrap text-right">
                            @if ($device->isBound())
                                <div class="table-actions table-actions--nowrap">
                                    <form method="POST" action="{{ route('devices.unbind') }}" class="inline" onsubmit="return confirm('{{ $unbindDeviceConfirmation }}');">
                                        @csrf

                                        <button type="submit" class="table-action table-action--danger">
                                            {{ __('Unbind') }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="table-meta">{{ __('No actions') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="5" class="table-empty">
                            <div class="table-empty-state">
                                <x-icon name="desktop" class="table-empty-icon" />
                                <p class="table-empty-title">{{ __('No device history found.') }}</p>
                                <p class="table-empty-copy">{{ __('Bind a device to start building a visible history on this surface.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div>
                <x-pagination :paginator="$devices" />
            </div>
        </section>
    </div>
</x-app-sidebar-layout>
