<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Device Management') }}
    </x-slot>

    @php
        $unbindDeviceConfirmation = __('Are you sure you want to unbind this device?');
        $resetHwidConfirmation = __('Are you sure you want to reset your HWID? This will allow you to bind a new device.');
    @endphp

    <div class="mx-auto max-w-4xl space-y-6" data-page="devices-manage">
        <div class="card-shell-muted">
            <p class="section-kicker">{{ __('Device Management') }}</p>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                {{ __('Manage the devices bound to your account. You can only bind one device at a time.') }}
            </p>
        </div>

        {{-- Current Device Status --}}
        @if ($currentDevice)
            <div class="card-shell">
                <div class="app-toolbar mb-4">
                    <div>
                        <p class="section-kicker">{{ __('Active Binding') }}</p>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('Currently Bound Device') }}</h3>
                    </div>
                    <x-status-badge status="active" :text="__('Bound')" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm mb-3">
                    <div>
                        <div class="text-gray-600 dark:text-gray-300 text-xs">{{ __('HWID Hash:') }}</div>
                        <div class="font-medium text-green-600 dark:text-green-300 break-all text-xs">
                            {{ $currentDevice->hwid_hash }}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-300 text-xs">{{ __('IP Address:') }}</div>
                        <div class="font-medium text-xs text-gray-900 dark:text-white">{{ $currentDevice->ip_address }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-300 text-xs">{{ __('Country:') }}</div>
                        <div class="font-medium text-xs text-gray-900 dark:text-white">{{ $currentDevice->country_code ?? __('Unknown') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-300 text-xs">{{ __('Bound At:') }}</div>
                        <div class="font-medium text-xs text-gray-900 dark:text-white">{{ $currentDevice->bound_at->format('Y-m-d H:i') }}</div>
                    </div>
                </div>

                {{-- Unbind Action --}}
                <form action="{{ route('devices.unbind') }}" method="POST"
                    onsubmit="return confirm('{{ $unbindDeviceConfirmation }}')">
                    @csrf
                    <x-danger-button type="submit">{{ __('Unbind This Device') }}</x-danger-button>
                </form>
            </div>
        @else
            <div class="card-shell-muted">
                <div class="flex items-center gap-3">
                    <x-status-badge status="warning" :text="__('No Binding')" />
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('You have not bound any device to your account yet.') }}</p>
                </div>
            </div>
        @endif

        {{-- Bind New Device Form --}}
        @if (!$currentDevice)
            <div class="card-shell">
                <p class="section-kicker mb-3">{{ __('Bind New Device') }}</p>
                <form method="POST" action="{{ route('devices.bind') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="hwid"
                            class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('HWID') }}
                        </label>
                        <input type="text" name="hwid" id="hwid" value="{{ old('hwid') }}"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 text-sm"
                            placeholder="{{ __('Enter your device HWID string') }}">
                        @error('hwid')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Your raw HWID string will be hashed on the server side and stored as an irreversible SHA-256 hash.') }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <label for="ip_address"
                            class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('IP Address') }}</label>
                        <input type="text" name="ip_address" id="ip_address"
                            value="{{ old('ip_address', request()->ip()) }}"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 text-sm"
                            placeholder="{{ __('Your current IP address') }}">
                        @error('ip_address')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="country_code"
                            class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Country Code (Optional)') }}</label>
                        <input type="text" name="country_code" id="country_code" value="{{ old('country_code') }}"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 text-sm"
                            placeholder="{{ __('e.g., US, CN, JP') }}" maxlength="2">
                        @error('country_code')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm">
                        {{ __('Bind Device') }}
                    </button>
                </form>
            </div>
        @endif

        {{-- HWID Reset Information --}}
        <div class="card-shell-muted mb-4">
            <p class="section-kicker mb-2">{{ __('HWID Reset Information') }}</p>
            <div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('HWID Reset Count:') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $hwidResetCount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('Last HWID Reset:') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $hwidLastReset ? $hwidLastReset->format('Y-m-d H:i') : __('Never') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('Can Reset HWID:') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            @if ($canResetHwid)
                                <span class="text-green-600 dark:text-green-400">{{ __('Yes') }}</span>
                            @else
                                <span class="text-red-600 dark:text-red-400">{{ __('No') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                @if ($canResetHwid)
                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                            {{ __('You can reset your HWID to bind a new device. This is useful if you have changed hardware.') }}
                        </p>
                        <form action="{{ route('devices.reset-hwid') }}" method="POST"
                            onsubmit="return confirm('{{ $resetHwidConfirmation }}')">
                            @csrf
                            <button type="submit"
                                class="px-3 py-1.5 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition text-xs">
                                {{ __('Reset HWID') }}
                            </button>
                        </form>
                    </div>
                @else
                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                        <p class="text-xs text-yellow-600 dark:text-yellow-400">
                            {{ __('You can only reset HWID every 72 hours. Please wait until') }}
                            {{ $hwidLastReset ? $hwidLastReset->addHours(72)->format('Y-m-d H:i') : __('your first reset') }}
                            .
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Back to Devices List --}}
        <div class="flex justify-end">
            <x-secondary-button tag="a" href="{{ route('devices.index') }}">
                {{ __('Back to Device History') }}
            </x-secondary-button>
        </div>
        </div>
    </div>

</x-app-sidebar-layout>
