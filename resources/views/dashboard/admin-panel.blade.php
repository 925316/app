@php use App\Models\License; @endphp
<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <div>
        <div class="w-full">
            <div class="flex items-center justify-between mb-6">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Last updated:') }} {{ now()->format('M d, Y H:i') }}
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <x-stat-card :title="__('Total Accounts')" :value="$stats['total_accounts'] ?? 0" icon="users" iconColor="icon-blue" />
                <x-stat-card :title="__('Active Licenses')" :value="$stats['active_licenses'] ?? 0" icon="success" iconColor="icon-green" />
                <x-stat-card :title="__('Suspended Accounts')" :value="$stats['suspended_accounts'] ?? 0" icon="warning" iconColor="icon-red" />
                <x-stat-card :title="__('Expired Licenses')" :value="$stats['expired_licenses'] ?? 0" icon="error" iconColor="icon-yellow" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Recent Activity -->
                <div class="card-shell overflow-hidden">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-300" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            {{ __('Recent Activity (Last 7 Days)') }}
                        </h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">{{ __('New Accounts:') }}</span>
                                <span
                                    class="font-semibold text-gray-900 dark:text-white">{{ $recentActivity['new_accounts'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">{{ __('Active Sessions:') }}</span>
                                <span
                                    class="font-semibold text-gray-900 dark:text-white">{{ $recentActivity['active_sessions'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">{{ __('Login Events:') }}</span>
                                <span
                                    class="font-semibold text-gray-900 dark:text-white">{{ $recentActivity['login_events'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="card-shell overflow-hidden">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-slate-600 dark:text-zinc-300" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                </path>
                            </svg>
                            {{ __('System Health') }}
                        </h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">{{ __('Unverified Accounts:') }}</span>
                                <span
                                    class="font-semibold text-gray-900 dark:text-white">{{ $stats['unverified_accounts'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">{{ __('Total System Users:') }}</span>
                                <span
                                    class="font-semibold text-gray-900 dark:text-white">{{ $stats['total_accounts'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-300">{{ __('Active License Ratio:') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    @php
                                        $totalLicenses = $stats['total_licenses'] ?? 0;
                                        $activeRatio =
                                            $totalLicenses > 0
                                                ? round((($stats['active_licenses'] ?? 0) / $totalLicenses) * 100)
                                                : 0;
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
                    <svg class="w-5 h-5 mr-2 text-slate-600 dark:text-zinc-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                        </path>
                    </svg>
                    {{ __('Database System Status') }}
                </h4>

                @if (isset($databaseStatus['error']))
                    <div
                        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-red-600 dark:text-red-300">{{ $databaseStatus['error'] }}</span>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Database Info -->
                    <div class="card-shell overflow-hidden">
                        <div class="p-6">
                            <h5 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-slate-600 dark:text-zinc-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                                    </path>
                                </svg>
                                {{ __('Database Info') }}
                            </h5>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Name:') }}</span>
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['database']['name'] ?? __('Unknown') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Version:') }}</span>
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['database']['version'] ?? __('Unknown') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Size:') }}</span>
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white">{{ number_format($databaseStatus['database']['size_mb'] ?? 0, 2) }}
                                        {{ __('MB') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Connection:') }}</span>
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['database']['connection'] ?? __('Unknown') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Driver:') }}</span>
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['database']['driver'] ?? __('Unknown') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Connection Pool -->
                    <div class="card-shell overflow-hidden">
                        <div class="p-6">
                            <h5 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-slate-600 dark:text-zinc-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                {{ __('Connection Pool') }}
                            </h5>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Max Connections:') }}</span>
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['connections']['max_connections'] ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Threads Connected:') }}</span>
                                    <span
                                        class="font-semibold text-slate-600 dark:text-zinc-300">{{ $databaseStatus['connections']['threads_connected'] ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Threads Running:') }}</span>
                                    <span
                                        class="font-semibold text-green-600 dark:text-green-300">{{ $databaseStatus['connections']['threads_running'] ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Usage:') }}</span>
                                    <span
                                        class="font-semibold text-yellow-600 dark:text-yellow-300">{{ $databaseStatus['connections']['usage_percent'] ?? 0 }}%</span>
                                </div>
                                <div class="mt-4">
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-yellow-500 h-2 rounded-full transition-all duration-300 w-[calc(var(--usage-percent)*1%)]"
                                            style="--usage-percent: {{ min($databaseStatus['connections']['usage_percent'] ?? 0, 100) }};">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Queue Jobs -->
                    <div class="card-shell overflow-hidden">
                        <div class="p-6">
                            <h5 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-slate-600 dark:text-zinc-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('Queue Jobs') }}
                            </h5>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Pending Jobs:') }}</span>
                                    <span
                                        class="font-semibold text-slate-600 dark:text-zinc-300">{{ $databaseStatus['queues']['pending_jobs'] ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Failed Jobs:') }}</span>
                                    <span
                                        class="font-semibold text-red-600 dark:text-red-300">{{ $databaseStatus['queues']['failed_jobs'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Uptime & Cache -->
                    <div class="card-shell overflow-hidden">
                        <div class="p-6">
                            <h5 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('System Status') }}
                            </h5>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Database Uptime:') }}</span>
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['uptime']['formatted'] ?? __('Unknown') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Cache') }}
                                        ({{ $databaseStatus['cache']['type'] ?? __('Unknown') }}):</span>
                                    <span
                                        class="font-semibold {{ $databaseStatus['cache']['connected'] ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300' }}">
                                        {{ $databaseStatus['cache']['connected'] ? __('Connected') : __('Disconnected') }}
                                    </span>
                                </div>
                                @if (isset($databaseStatus['cache']['db_size']))
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-gray-600 dark:text-gray-300">{{ __('Cache Keys:') }}</span>
                                        <span
                                            class="font-semibold text-gray-900 dark:text-white">{{ $databaseStatus['cache']['db_size'] ?? 0 }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-sidebar-layout>
