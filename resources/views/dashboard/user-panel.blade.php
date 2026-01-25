<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-xl border border-gray-200 dark:border-gray-700">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                My Account Overview
            </h3>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Last updated: {{ now()->format('M d, Y H:i') }}
            </div>
        </div>

        <!-- License Status -->
        <div class="mb-8">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                License Status
            </h4>
            @if($activeLicense)
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/50 dark:to-green-800/50 p-6 rounded-xl border border-green-200/50 dark:border-green-700/50 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-3">
                                <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-500/20 text-green-800 dark:text-green-200 border border-green-500/30">
                                    {{ $activeLicense->getStatusTextAttribute() }}
                                </span>
                                <span class="px-3 py-1 text-sm font-medium rounded-full bg-purple-500/20 text-purple-800 dark:text-purple-200 border border-purple-500/30">
                                    {{ $activeLicense->getPrivilegeTextAttribute() }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-300 mb-2">License Key</div>
                            <div class="text-lg font-mono font-bold text-gray-900 dark:text-white bg-white/50 dark:bg-gray-700/50 p-3 rounded-lg border border-gray-200/50 dark:border-gray-600/50">
                                {{ $activeLicense->key }}
                            </div>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-white/50 dark:bg-gray-700/50 p-3 rounded-lg border border-gray-200/50 dark:border-gray-600/50">
                                    <div class="text-sm text-gray-600 dark:text-gray-300">Expires</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $activeLicense->expires_at->format('Y-m-d') }}
                                    </div>
                                </div>
                                <div class="bg-white/50 dark:bg-gray-700/50 p-3 rounded-lg border border-gray-200/50 dark:border-gray-600/50">
                                    <div class="text-sm text-gray-600 dark:text-gray-300">Days Remaining</div>
                                    <div class="text-lg font-semibold text-green-600 dark:text-green-300">
                                        {{ $activeLicense->daysUntilExpiry() }} days
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/50 dark:to-yellow-800/50 p-6 rounded-xl border border-yellow-200/50 dark:border-yellow-700/50 shadow-sm">
                    <div class="flex items-start space-x-4">
                        <div class="p-3 bg-yellow-500/20 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h5 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-2">No Active License</h5>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                You don't have an active license. Please contact support or purchase a license to access premium features.
                            </p>
                            <a href="{{ route('licenses.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                View Available Licenses
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Device Status -->
            <div>
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Device Status
                </h4>
                @if($boundDevices > 0)
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/50 dark:to-blue-800/50 p-6 rounded-xl border border-blue-200/50 dark:border-blue-700/50 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center space-x-3 mb-3">
                                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-500/20 text-blue-800 dark:text-blue-200 border border-blue-500/30">
                                        {{ $boundDevices }} Device(s) Bound
                                    </span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300">
                                    Your devices are successfully bound to your account and can access licensed software.
                                </p>
                            </div>
                            <div class="p-3 bg-blue-500/20 rounded-full">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('devices.manage') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Manage Devices
                            </a>
                        </div>
                    </div>
                @else
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-600/50 p-6 rounded-xl border border-gray-200/50 dark:border-gray-600/50 shadow-sm">
                        <div class="flex items-start space-x-4">
                            <div class="p-3 bg-gray-500/20 rounded-full">
                                <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Bound Device</h5>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">
                                    You haven't bound any device to your account yet. Bind a device to start using licensed software.
                                </p>
                                <a href="{{ route('devices.manage') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 shadow-md">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Bind a Device
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Usage Statistics -->
            <div>
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0h2m4 0h2a2 2 0 002-2v-6a2 2 0 00-2-2h-2a2 2 0 00-2 2v6a2 2 0 002 2zm0 0h2m4 0h2a2 2 0 002-2v-6a2 2 0 00-2-2h-2a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                    </svg>
                    Usage Statistics
                </h4>
                <div class="space-y-4">
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/50 dark:to-purple-800/50 p-6 rounded-xl border border-purple-200/50 dark:border-purple-700/50 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-purple-600 dark:text-purple-300 mb-1">Total Usage Time</div>
                                <div class="text-2xl font-bold text-purple-800 dark:text-purple-200">{{ $usageTimeFormatted }}</div>
                            </div>
                            <div class="p-3 bg-purple-500/20 rounded-full">
                                <svg class="w-8 h-8 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/50 dark:to-indigo-800/50 p-6 rounded-xl border border-indigo-200/50 dark:border-indigo-700/50 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-indigo-600 dark:text-indigo-300 mb-1">Login Count</div>
                                <div class="text-2xl font-bold text-indigo-800 dark:text-indigo-200">{{ $userStats['login_count'] ?? 0 }}</div>
                            </div>
                            <div class="p-3 bg-indigo-500/20 rounded-full">
                                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div>
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Quick Actions
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('licenses.index') }}" class="group p-6 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 border border-blue-400/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h5 class="font-semibold text-lg mb-1">My Licenses</h5>
                            <p class="text-blue-100 text-sm">View your license details</p>
                        </div>
                        <svg class="w-8 h-8 opacity-80 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </a>
                @if (Auth::user()->hasPrivilege(1))
                <a href="{{ route('devices.index') }}" class="group p-6 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 border border-green-400/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h5 class="font-semibold text-lg mb-1">My Devices</h5>
                            <p class="text-green-100 text-sm">Manage your devices</p>
                        </div>
                        <svg class="w-8 h-8 opacity-80 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </a>
                @endif
                @if (Auth::user()->hasPrivilege(1))
                <a href="{{ route('packages.index') }}" class="group p-6 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 border border-purple-400/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h5 class="font-semibold text-lg mb-1">Download Software</h5>
                            <p class="text-purple-100 text-sm">Get latest packages</p>
                        </div>
                        <svg class="w-8 h-8 opacity-80 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                </a>
                @endif
                <a href="{{ route('profile.edit') }}" class="group p-6 bg-gradient-to-br from-gray-500 to-gray-600 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 border border-gray-400/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h5 class="font-semibold text-lg mb-1">Edit Profile</h5>
                            <p class="text-gray-100 text-sm">Update your information</p>
                        </div>
                        <svg class="w-8 h-8 opacity-80 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
