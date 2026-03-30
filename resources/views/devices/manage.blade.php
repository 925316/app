<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Device Management') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Keep binding, unbinding, and HWID reset behavior intact while moving the controls into the shared cinematic form system.') }}
    </x-slot>

    @php
        $unbindDeviceConfirmation = __('Are you sure you want to unbind this device?');
        $resetHwidConfirmation = __('Are you sure you want to reset your HWID? This will allow you to bind a new device.');
        $currentDeviceShortHwid = $currentDevice?->hwid_hash
            ? \Illuminate\Support\Str::limit($currentDevice->hwid_hash, 32, '...')
            : null;
    @endphp

    <div class="mx-auto max-w-5xl space-y-8" data-page="devices-manage">
        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Binding Control') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Device management') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Manage the same one-device-at-a-time binding flow without changing any routes, validations, or ownership semantics.') }}</p>
                </div>

                <div class="app-toolbar-actions">
                    <a href="{{ route('devices.index') }}" class="btn btn-secondary btn-sm gap-2">
                        <x-icon name="desktop" class="h-4 w-4" />
                        {{ __('View History') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(18rem,1fr)]">
                <div class="card-shell-muted space-y-5 p-6">
                    @if ($currentDevice)
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-status-badge status="active" :text="__('Bound')" />
                                    <span class="badge badge-info">{{ __('Active binding') }}</span>
                                </div>

                                <div>
                                    <p class="section-kicker">{{ __('Currently bound device') }}</p>
                                    <h3 class="card-heading text-2xl font-semibold text-gray-900 dark:text-white">
                                        <span title="{{ $currentDevice->hwid_hash }}" class="cursor-help font-mono break-all">
                                            {{ $currentDeviceShortHwid }}
                                        </span>
                                    </h3>
                                </div>
                            </div>

                            <div class="card-shell-muted min-w-[14rem] space-y-2 self-start p-4">
                                <p class="section-kicker">{{ __('Bound at') }}</p>
                                <p class="card-heading text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $currentDevice->bound_at ? $currentDevice->bound_at->format('Y-m-d H:i') : __('Unknown') }}
                                </p>
                                @if ($currentDevice->bound_at)
                                    <p class="app-shell-body-copy text-sm">{{ $currentDevice->bound_at->diffForHumans() }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="card-shell-muted space-y-2 p-4">
                                <p class="section-kicker">{{ __('IP address') }}</p>
                                <p class="card-heading text-base font-semibold text-gray-900 dark:text-white">{{ $currentDevice->ip_address ?? __('Unknown') }}</p>
                            </div>

                            <div class="card-shell-muted space-y-2 p-4">
                                <p class="section-kicker">{{ __('Country') }}</p>
                                <p class="card-heading text-base font-semibold text-gray-900 dark:text-white">{{ $currentDevice->country_code ?? __('Unknown') }}</p>
                            </div>
                        </div>

                        <form action="{{ route('devices.unbind') }}" method="POST" onsubmit="return confirm('{{ $unbindDeviceConfirmation }}')">
                            @csrf

                            <div class="form-actions-cluster justify-start">
                                <x-danger-button class="btn-sm">
                                    {{ __('Unbind This Device') }}
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
                                    <x-status-badge status="warning" :text="__('No Binding')" />
                                </div>

                                <div>
                                    <p class="section-kicker">{{ __('Binding status') }}</p>
                                    <h3 class="card-heading text-xl font-semibold text-gray-900 dark:text-white">{{ __('No device currently attached') }}</h3>
                                </div>

                                <p class="app-shell-body-copy text-sm">
                                    {{ __('You have not bound any device to your account yet. Use the form below to register a single device without changing the current validation or ownership behavior.') }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <aside class="card-shell-muted flex flex-col justify-between gap-6 p-6">
                    <div class="space-y-3">
                        <p class="section-kicker">{{ __('Binding policy') }}</p>
                        <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('One device at a time') }}</h3>
                        <p class="app-shell-body-copy text-sm">
                            {{ __('This account can only keep one active binding at a time. Unbind or reset the current HWID before attaching replacement hardware.') }}
                        </p>
                    </div>

                    <div class="grid gap-3">
                        <a href="{{ route('devices.index') }}" class="btn btn-secondary btn-sm justify-center gap-2">
                            <x-icon name="info" class="h-4 w-4" />
                            {{ __('Review Device History') }}
                        </a>
                    </div>
                </aside>
            </div>
        </section>

        @if (! $currentDevice)
            <section class="card-shell space-y-6">
                <div class="app-toolbar">
                    <div>
                        <p class="section-kicker">{{ __('Bind New Device') }}</p>
                        <h2 class="app-toolbar-title">{{ __('Attach a device') }}</h2>
                        <p class="app-toolbar-subtitle">{{ __('Submit the same HWID, IP, and optional country details through the shared form field and action language.') }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('devices.bind') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-2 md:col-span-2">
                            <label for="hwid" class="form-label">{{ __('HWID') }}</label>
                            <input type="text" name="hwid" id="hwid" value="{{ old('hwid') }}" class="form-input w-full" placeholder="{{ __('Enter your device HWID string') }}">
                            <x-input-error :messages="$errors->get('hwid')" />
                            <p class="form-note text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Your raw HWID string will be hashed on the server side and stored as an irreversible SHA-256 hash.') }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="ip_address" class="form-label">{{ __('IP Address') }}</label>
                            <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address', request()->ip()) }}" class="form-input w-full" placeholder="{{ __('Your current IP address') }}">
                            <x-input-error :messages="$errors->get('ip_address')" />
                        </div>

                        <div class="space-y-2">
                            <label for="country_code" class="form-label">{{ __('Country Code (Optional)') }}</label>
                            <input type="text" name="country_code" id="country_code" value="{{ old('country_code') }}" class="form-input w-full uppercase" placeholder="{{ __('e.g., US, CN, JP') }}" maxlength="2">
                            <x-input-error :messages="$errors->get('country_code')" />
                        </div>
                    </div>

                    <div class="form-actions-cluster justify-end">
                        <a href="{{ route('devices.index') }}" class="btn btn-secondary btn-sm gap-2">
                            <x-icon name="reset" class="h-4 w-4" />
                            {{ __('Cancel') }}
                        </a>

                        <x-primary-button class="btn-sm">
                            {{ __('Bind Device') }}
                        </x-primary-button>
                    </div>
                </form>
            </section>
        @endif

        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Recovery') }}</p>
                    <h2 class="app-toolbar-title">{{ __('HWID reset controls') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Keep the same reset eligibility, cooldown timing, and confirmation wording while presenting the data in the shared card system.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="card-shell-muted space-y-2 p-4">
                    <p class="section-kicker">{{ __('HWID reset count') }}</p>
                    <p class="card-heading text-xl font-semibold text-gray-900 dark:text-white">{{ $hwidResetCount }}</p>
                </div>

                <div class="card-shell-muted space-y-2 p-4">
                    <p class="section-kicker">{{ __('Last HWID reset') }}</p>
                    <p class="card-heading text-base font-semibold text-gray-900 dark:text-white">
                        {{ $hwidLastReset ? $hwidLastReset->format('Y-m-d H:i') : __('Never') }}
                    </p>
                    @if ($hwidLastReset)
                        <p class="app-shell-body-copy text-sm">{{ $hwidLastReset->diffForHumans() }}</p>
                    @endif
                </div>

                <div class="card-shell-muted space-y-2 p-4">
                    <p class="section-kicker">{{ __('Can reset HWID') }}</p>
                    <div>
                        <x-status-badge :status="$canResetHwid ? 'active' : 'warning'" :text="$canResetHwid ? __('Yes') : __('No')" />
                    </div>
                </div>
            </div>

            <div class="card-shell-muted space-y-4 p-6">
                @if ($canResetHwid)
                    <div class="space-y-2">
                        <p class="section-kicker">{{ __('Reset availability') }}</p>
                        <p class="app-shell-body-copy text-sm">
                            {{ __('You can reset your HWID to bind a new device. This is useful if you have changed hardware.') }}
                        </p>
                    </div>

                    <form action="{{ route('devices.reset-hwid') }}" method="POST" onsubmit="return confirm('{{ $resetHwidConfirmation }}')">
                        @csrf

                        <div class="form-actions-cluster justify-start">
                            <button type="submit" class="btn btn-secondary btn-sm gap-2">
                                <x-icon name="reset" class="h-4 w-4" />
                                {{ __('Reset HWID') }}
                            </button>
                        </div>
                    </form>
                @else
                    <div class="space-y-3">
                        <x-status-badge status="warning" :text="__('Cooldown active')" />
                        <p class="app-shell-body-copy text-sm text-yellow-700 dark:text-yellow-300">
                            {{ __('You can only reset HWID every 72 hours. Please wait until') }}
                            {{ $hwidLastReset ? $hwidLastReset->addHours(72)->format('Y-m-d H:i') : __('your first reset') }}.
                        </p>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-app-sidebar-layout>
