<x-app-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Devices') }}
        </h2>
    </x-slot>

    @php
        $unbindDeviceConfirmation = __('Unbind this device?');
    @endphp

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Current Device Status -->
                    @if ($currentDevice)
                        <div
                            class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/50 dark:to-blue-800/50 rounded-lg border border-blue-200/50 dark:border-blue-700/50">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 bg-blue-500/20 rounded-full">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-blue-800 dark:text-blue-200">{{ __('Currently Bound Device') }}</h4>
                                        <div class="text-sm text-blue-600 dark:text-blue-300">
                                            <span title="{{ $currentDevice->hwid_hash }}" class="cursor-help">
                                                {{ substr($currentDevice->hwid_hash, 0, 8) }}...
                                            </span>
                                            | {{ $currentDevice->ip_address }}
                                            | {{ $currentDevice->country_code ?? __('Unknown') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <span
                                        class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-xs font-medium">
                                        {{ __('Bound since') }} {{ $currentDevice->bound_at->format('Y-m-d') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div
                            class="mb-6 p-4 bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/50 dark:to-yellow-800/50 rounded-lg border border-yellow-200/50 dark:border-yellow-700/50">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-yellow-500/20 rounded-full">
                                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-yellow-800 dark:text-yellow-200">{{ __('No Device Bound') }}</h4>
                                    <div class="text-sm text-yellow-600 dark:text-yellow-300">
                                        {{ __('You have not bound any device to your account yet.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Device Table -->
                    <x-table :headers="[__('HWID'), __('IP / Country'), __('First / Last'), __('Status'), __('Actions')]" :emptyColspan="5">
                        @forelse($devices as $device)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        @if ($device->hwid_hash)
                                            <span title="{{ $device->hwid_hash }}" class="cursor-help">
                                                {{ substr($device->hwid_hash, 0, 8) }}...
                                            </span>
                                        @else
                                            {{ __('N/A') }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <span class="text-gray-900 dark:text-gray-100">{{ $device->ip_address }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ $device->country_code ?? __('N/A') }})</span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <span class="text-gray-900 dark:text-gray-100" title="{{ __('First:') }} {{ $device->first_seen_at->format('Y-m-d H:i:s') }} | {{ __('Last:') }} {{ $device->last_seen_at->format('Y-m-d H:i:s') }}">
                                        {{ $device->first_seen_at->format('m-d') }} / {{ $device->last_seen_at->format('m-d') }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    @if ($device->bound_at && !$device->unbound_at)
                                        <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs font-medium">
                                            {{ __('Currently Bound') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-xs font-medium">
                                            {{ __('Historical') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <div class="flex gap-2">
                                        @if ($device->isBound())
                                            <form method="POST" action="{{ route('devices.unbind') }}" class="inline"
                                                onsubmit="return confirm('{{ $unbindDeviceConfirmation }}');">
                                                @csrf
                                                <button type="submit"
                                                    class="px-2 py-1 bg-yellow-600 text-white text-xs rounded hover:bg-yellow-700 transition">
                                                    {{ __('Unbind') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                                    {{ __('No device history found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </x-table>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <x-pagination :paginator="$devices" />
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-sidebar-layout>
