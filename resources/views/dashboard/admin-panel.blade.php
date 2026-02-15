<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

<div class="py-7">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Accounts -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-500/20 rounded-full">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Accounts</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total_accounts'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Licenses -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-3 bg-green-500/20 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Licenses</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['active_licenses'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Suspended Accounts -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-3 bg-red-500/20 rounded-full">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Suspended Accounts</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['suspended_accounts'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Expired Licenses -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-500/20 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expired Licenses</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['expired_licenses'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Recent Activity (Last 7 Days)
                    </h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 dark:text-gray-300">New Accounts:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $recentActivity['new_accounts'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 dark:text-gray-300">Active Sessions:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $recentActivity['active_sessions'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 dark:text-gray-300">Login Events:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $recentActivity['login_events'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        System Health
                    </h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 dark:text-gray-300">Unverified Accounts:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $stats['unverified_accounts'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 dark:text-gray-300">Total System Users:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $stats['total_accounts'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
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
        </div>

        <!-- Database Status -->
        <div>
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                </svg>
                Database System Status
            </h4>
            
            @if(isset($databaseStatus['error']))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-red-700 dark:text-red-300">{{ $databaseStatus['error'] }}</span>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Database Info -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h5 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                            </svg>
                            Database Info
                        </h5>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Name:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['database']['name'] ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Version:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['database']['version'] ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Size:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($databaseStatus['database']['size_mb'] ?? 0, 2) }} MB</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Connection:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['database']['connection'] ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Driver:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['database']['driver'] ?? 'Unknown' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Connection Pool -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h5 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Connection Pool
                        </h5>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Max Connections:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['connections']['max_connections'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Threads Connected:</span>
                                <span class="font-semibold text-blue-700 dark:text-blue-300">{{ $databaseStatus['connections']['threads_connected'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Threads Running:</span>
                                <span class="font-semibold text-green-700 dark:text-green-300">{{ $databaseStatus['connections']['threads_running'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Usage:</span>
                                <span class="font-semibold text-yellow-700 dark:text-yellow-300">{{ $databaseStatus['connections']['usage_percent'] ?? 0 }}%</span>
                            </div>
                            <div class="mt-4">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full transition-all duration-300" style="width: {{ min($databaseStatus['connections']['usage_percent'] ?? 0, 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Queue Jobs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h5 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Queue Jobs
                        </h5>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Pending Jobs:</span>
                                <span class="font-semibold text-blue-700 dark:text-blue-300">{{ $databaseStatus['queues']['pending_jobs'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Failed Jobs:</span>
                                <span class="font-semibold text-red-700 dark:text-red-300">{{ $databaseStatus['queues']['failed_jobs'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Uptime & Cache -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h5 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            System Status
                        </h5>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Database Uptime:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['uptime']['formatted'] ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">Cache ({{ $databaseStatus['cache']['type'] ?? 'Unknown' }}):</span>
                                <span class="font-semibold {{ $databaseStatus['cache']['connected'] ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                    {{ $databaseStatus['cache']['connected'] ? 'Connected' : 'Disconnected' }}
                                </span>
                            </div>
                            @if(isset($databaseStatus['cache']['db_size']))
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">Cache Keys:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['cache']['db_size'] ?? 0 }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- <!-- Table Sizes -->
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <h5 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Table Sizes (Top 10)
                </h5>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Table Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rows</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Size (MB)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach(array_slice($databaseStatus['tables'] ?? [], 0, 10) as $table)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $table['name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ number_format($table['rows']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ number_format($table['size_mb'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            @if(empty($databaseStatus['tables'] ?? []))
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No tables found
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div> --}}
        </div>
    </div>
</div>
</x-app-sidebar-layout>