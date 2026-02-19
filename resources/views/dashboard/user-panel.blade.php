<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Last updated: {{ now()->format('M d, Y H:i') }}
                </div>
            </div>

            <!-- License Status -->
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    License Status
                </h4>
                @if ($activeLicense)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center space-x-3 mb-4">
                                <span
                                    class="px-3 py-1 text-sm font-medium rounded-full bg-green-500/20 text-green-800 dark:text-green-200 border border-green-500/30">
                                    {{ $activeLicense->getStatusTextAttribute() }}
                                </span>
                                <span
                                    class="px-3 py-1 text-sm font-medium rounded-full bg-purple-500/20 text-purple-800 dark:text-purple-200 border border-purple-500/30">
                                    {{ $activeLicense->getPrivilegeTextAttribute() }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-300 mb-2">License Key</div>
                            <div
                                class="text-lg font-mono font-bold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg border border-gray-200 dark:border-gray-600">
                                {{ $activeLicense->key }}
                            </div>
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div
                                    class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                                    <div class="text-sm text-gray-600 dark:text-gray-300 mb-1">Expires</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $activeLicense->expires_at->format('Y-m-d') }}
                                    </div>
                                </div>
                                <div
                                    class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                                    <div class="text-sm text-gray-600 dark:text-gray-300 mb-1">Days Remaining</div>
                                    <div class="text-lg font-semibold text-green-600 dark:text-green-300">
                                        {{ $activeLicense->daysUntilExpiry() }} days
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-start space-x-4">
                                <div class="p-3 bg-yellow-500/20 rounded-full flex-shrink-0">
                                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Active
                                        License</h5>
                                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                                        You don't have an active license. Please contact support or purchase a license
                                        to access
                                        premium features.
                                    </p>
                                    <a href="{{ route('licenses.index') }}"
                                        class="btn btn-blue btn-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        View Available Licenses
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Device Status -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Device Status
                    </h4>
                    @if ($boundDevices > 0)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <span
                                            class="px-3 py-1 text-sm font-medium rounded-full bg-blue-500/20 text-blue-800 dark:text-blue-200 border border-blue-500/30">
                                            {{ $boundDevices }} Device(s) Bound
                                        </span>
                                    </div>
                                    <div class="p-3 bg-blue-500/20 rounded-full">
                                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">
                                    Your devices are successfully bound to your account and can access licensed
                                    software.
                                </p>
                                <a href="{{ route('devices.manage') }}"
                                    class="btn btn-blue btn-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Manage Devices
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-start space-x-4">
                                    <div class="p-3 bg-gray-500/20 rounded-full flex-shrink-0">
                                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Bound
                                            Device</h5>
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                                            You haven't bound any device to your account yet. Bind a device to start
                                            using
                                            licensed software.
                                        </p>
                                        <a href="{{ route('devices.manage') }}"
                                            class="btn btn-blue btn-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Bind a Device
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Usage Statistics -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-300" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0h2m4 0h2a2 2 0 002-2v-6a2 2 0 00-2-2h-2a2 2 0 00-2 2v6a2 2 0 002 2zm0 0h2m4 0h2a2 2 0 002-2v-6a2 2 0 00-2-2h-2a2 2 0 00-2 2v6a2 2 0 002 2z">
                            </path>
                        </svg>
                        Usage Statistics
                    </h4>
                    <div class="space-y-4">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-purple-600 dark:text-purple-300 mb-1">Total Usage
                                            Time
                                        </div>
                                        <div class="text-2xl font-bold text-purple-800 dark:text-purple-200">
                                            {{ $usageTimeFormatted }}</div>
                                    </div>
                                    <div class="p-3 bg-purple-500/20 rounded-full">
                                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-indigo-600 dark:text-indigo-300 mb-1">Login Count
                                        </div>
                                        <div class="text-2xl font-bold text-indigo-800 dark:text-indigo-200">
                                            {{ $userStats['login_count'] ?? 0 }}</div>
                                    </div>
                                    <div class="p-3 bg-indigo-500/20 rounded-full">
                                        <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-sidebar-layout>
