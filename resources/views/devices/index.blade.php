<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('My Devices') }}
    </x-slot>

    @php
        $unbindDeviceConfirmation = __('Unbind this device?');
    @endphp

    <div class="space-y-6" data-page="devices-index">
        <div class="card-shell">
                    {{-- Current Device Status --}}
                    @if ($currentDevice)
                        <div class="card-shell-muted mb-6">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div>
                                    <p class="section-kicker">{{ __('Currently Bound Device') }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        <span title="{{ $currentDevice->hwid_hash }}" class="cursor-help font-mono">{{ substr($currentDevice->hwid_hash, 0, 8) }}...</span>
                                        &middot; {{ $currentDevice->ip_address }}
                                        &middot; {{ $currentDevice->country_code ?? __('Unknown') }}
                                    </p>
                                </div>
                                <x-status-badge status="active" :text="__('Bound since') . ' ' . $currentDevice->bound_at->format('Y-m-d')" />
                            </div>
                        </div>
                    @else
                        <div class="card-shell-muted mb-6">
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
                                    <x-status-badge status="warning" :text="__('No Device Bound')" />
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ __('You have not bound any device to your account yet.') }}</p>
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

</x-app-sidebar-layout>
