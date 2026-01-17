<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Devices') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header with actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h3 class="text-lg font-medium">Device History</h3>
                        <a href="{{ route('devices.manage') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            Manage Devices
                        </a>
                    </div>

                    <!-- Current Device Status -->
                    @if($currentDevice)
                        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/50 rounded-lg">
                            <h4 class="font-medium mb-2 text-green-800 dark:text-green-200">Currently Bound Device</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <div class="text-gray-600 dark:text-gray-300">HWID Hash:</div>
                                    <div class="font-medium text-green-700 dark:text-green-300 break-all">
                                        {{ $currentDevice->hwid_hash }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-600 dark:text-gray-300">IP Address:</div>
                                    <div class="font-medium">{{ $currentDevice->ip_address }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-600 dark:text-gray-300">Country:</div>
                                    <div class="font-medium">{{ $currentDevice->country_code ?? 'Unknown' }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-600 dark:text-gray-300">Bound At:</div>
                                    <div class="font-medium">{{ $currentDevice->bound_at->format('Y-m-d H:i:s') }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/50 rounded-lg">
                            <h4 class="font-medium mb-2 text-yellow-800 dark:text-yellow-200">No Device Bound</h4>
                            <p class="text-yellow-700 dark:text-yellow-300">You haven't bound any device to your account yet.</p>
                        </div>
                    @endif

                    <!-- Device History Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        HWID Hash
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        IP Address
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Country
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        First Seen
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Last Seen
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($devices as $device)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 break-all">
                                            {{ $device->hwid_hash }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $device->ip_address }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $device->country_code ?? 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $device->first_seen_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $device->last_seen_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($device->bound_at && !$device->unbound_at)
                                                <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-xs font-medium">
                                                    Currently Bound
                                                </span>
                                            @else
                                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-full text-xs font-medium">
                                                    Historical
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-300">
                                            No device history found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $devices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
