<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-xl border border-gray-200 dark:border-gray-700">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                System Overview
            </h3>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Last updated: {{ now()->format('M d, Y H:i') }}
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Accounts -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/50 dark:to-blue-800/50 p-6 rounded-xl border border-blue-200/50 dark:border-blue-700/50 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-300">Total Accounts</p>
                        <p class="text-3xl font-bold text-blue-800 dark:text-blue-200 mt-1">{{ $stats['total_accounts'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-500/10 rounded-full">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Licenses -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/50 dark:to-green-800/50 p-6 rounded-xl border border-green-200/50 dark:border-green-700/50 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 dark:text-green-300">Active Licenses</p>
                        <p class="text-3xl font-bold text-green-800 dark:text-green-200 mt-1">{{ $stats['active_licenses'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-500/10 rounded-full">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Suspended Accounts -->
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/50 dark:to-yellow-800/50 p-6 rounded-xl border border-yellow-200/50 dark:border-yellow-700/50 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-600 dark:text-yellow-300">Suspended Accounts</p>
                        <p class="text-3xl font-bold text-yellow-800 dark:text-yellow-200 mt-1">{{ $stats['suspended_accounts'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-yellow-500/10 rounded-full">
                        <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Expired Licenses -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/50 dark:to-red-800/50 p-6 rounded-xl border border-red-200/50 dark:border-red-700/50 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-600 dark:text-red-300">Expired Licenses</p>
                        <p class="text-3xl font-bold text-red-800 dark:text-red-200 mt-1">{{ $stats['expired_licenses'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-red-500/10 rounded-full">
                        <svg class="w-8 h-8 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Recent Activity -->
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-600/50 p-6 rounded-xl border border-gray-200/50 dark:border-gray-600/50 shadow-sm">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Recent Activity (Last 7 Days)
                </h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200/50 dark:border-gray-700/50">
                        <div class="text-sm text-gray-600 dark:text-gray-300 mb-1">New Accounts</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $recentActivity['new_accounts'] ?? 0 }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200/50 dark:border-gray-700/50">
                        <div class="text-sm text-gray-600 dark:text-gray-300 mb-1">New Licenses</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $recentActivity['new_licenses'] ?? 0 }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200/50 dark:border-gray-700/50">
                        <div class="text-sm text-gray-600 dark:text-gray-300 mb-1">Active Sessions</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $recentActivity['active_sessions'] ?? 0 }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200/50 dark:border-gray-700/50">
                        <div class="text-sm text-gray-600 dark:text-gray-300 mb-1">Login Events</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $recentActivity['login_events'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/50 dark:to-purple-800/50 p-6 rounded-xl border border-purple-200/50 dark:border-purple-700/50 shadow-sm">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    System Health
                </h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200/50 dark:border-gray-700/50">
                        <span class="text-gray-600 dark:text-gray-300">Unverified Accounts:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $stats['unverified_accounts'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200/50 dark:border-gray-700/50">
                        <span class="text-gray-600 dark:text-gray-300">Total System Users:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $stats['total_accounts'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200/50 dark:border-gray-700/50">
                        <span class="text-gray-600 dark:text-gray-300">Active License Ratio:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            @php
                                $totalLicenses = \App\Models\License::count();
                                $activeRatio = $totalLicenses > 0 ? round(($stats['active_licenses'] ?? 0) / $totalLicenses * 100) : 0;
                            @endphp
                            {{ $activeRatio }}%
                        </span>
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
                <a href="{{ route('licenses.create') }}" class="group p-6 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 border border-blue-400/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h5 class="font-semibold text-lg mb-1">Create License</h5>
                            <p class="text-blue-100 text-sm">Generate new license keys</p>
                        </div>
                        <svg class="w-8 h-8 opacity-80 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </a>
                <a href="{{ route('packages.upload') }}" class="group p-6 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 border border-green-400/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h5 class="font-semibold text-lg mb-1">Upload Package</h5>
                            <p class="text-green-100 text-sm">Add new software packages</p>
                        </div>
                        <svg class="w-8 h-8 opacity-80 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                </a>
                <a href="{{ route('licenses.index') }}" class="group p-6 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 border border-purple-400/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h5 class="font-semibold text-lg mb-1">Manage Licenses</h5>
                            <p class="text-purple-100 text-sm">View and manage all licenses</p>
                        </div>
                        <svg class="w-8 h-8 opacity-80 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </a>
                <a href="{{ route('logs.index') }}" class="group p-6 bg-gradient-to-br from-gray-500 to-gray-600 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 border border-gray-400/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h5 class="font-semibold text-lg mb-1">View Logs</h5>
                            <p class="text-gray-100 text-sm">System activity logs</p>
                        </div>
                        <svg class="w-8 h-8 opacity-80 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
