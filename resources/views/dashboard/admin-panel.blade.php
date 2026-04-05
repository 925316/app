@php use App\Models\License; @endphp

<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Monitor accounts, activity, and infrastructure health from one place.') }}
    </x-slot>

    @php
        $totalLicenses = $stats['total_licenses'] ?? 0;
        $activeRatio = $totalLicenses > 0 ? round((($stats['active_licenses'] ?? 0) / $totalLicenses) * 100) : 0;
        $cacheConnected = $databaseStatus['cache']['connected'] ?? false;
        $connectionUsage = min($databaseStatus['connections']['usage_percent'] ?? 0, 100);
    @endphp

    <div class="space-y-8" data-page="dashboard-admin">
        <section class="card-shell flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between" data-dashboard-summary>
            <div class="space-y-2" data-dashboard-section-title-group>
                <p class="section-kicker">{{ __('Operations snapshot') }}</p>
                <h2 class="dashboard-section-title text-2xl font-semibold">{{ __('Administrative overview') }}</h2>
                <p class="dashboard-meta-text max-w-2xl text-sm">
                    {{ __('Track account health, recent platform activity, and core service status without changing any existing data contracts.') }}
                </p>
            </div>

            <div class="card-shell-muted flex items-center gap-3 self-start lg:self-auto" data-dashboard-summary-chip>
                <span class="card-icon-container icon-indigo h-11 w-11 shrink-0">
                    <x-icon name="server" class="h-6 w-6" />
                </span>

                <div>
                    <p class="section-kicker">{{ __('Last updated') }}</p>
                    <p class="dashboard-meta-text text-sm font-medium">{{ now()->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Dashboard statistics') }}" data-dashboard-stat-grid data-dashboard-grid="metrics">
            <x-stat-card :title="__('Total Accounts')" :value="$stats['total_accounts'] ?? 0" icon="users" iconColor="icon-blue" />
            <x-stat-card :title="__('Active Licenses')" :value="$stats['active_licenses'] ?? 0" icon="success" iconColor="icon-green" />
            <x-stat-card :title="__('Suspended Accounts')" :value="$stats['suspended_accounts'] ?? 0" icon="warning" iconColor="icon-red" />
            <x-stat-card :title="__('Expired Licenses')" :value="$stats['expired_licenses'] ?? 0" icon="error" iconColor="icon-yellow" />
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2" data-dashboard-panels>
            <article class="card-shell space-y-6" data-dashboard-card="recent-activity">
                <header class="flex items-start gap-4">
                    <span class="card-icon-container icon-blue shrink-0">
                        <x-icon name="lightning" class="h-6 w-6" />
                    </span>

                    <div class="space-y-1">
                        <p class="section-kicker">{{ __('Last 7 days') }}</p>
                        <h3 class="dashboard-section-title text-lg font-semibold">{{ __('Recent Activity') }}</h3>
                    </div>
                </header>

                <dl class="space-y-3">
                    <div class="card-shell-muted flex items-center justify-between gap-4">
                        <dt class="dashboard-metric-label text-sm">{{ __('New Accounts') }}</dt>
                        <dd class="dashboard-stat-number text-lg font-semibold">{{ $recentActivity['new_accounts'] ?? 0 }}</dd>
                    </div>
                    <div class="card-shell-muted flex items-center justify-between gap-4">
                        <dt class="dashboard-metric-label text-sm">{{ __('Active Sessions') }}</dt>
                        <dd class="dashboard-stat-number text-lg font-semibold">{{ $recentActivity['active_sessions'] ?? 0 }}</dd>
                    </div>
                    <div class="card-shell-muted flex items-center justify-between gap-4">
                        <dt class="dashboard-metric-label text-sm">{{ __('Login Events') }}</dt>
                        <dd class="dashboard-stat-number text-lg font-semibold">{{ $recentActivity['login_events'] ?? 0 }}</dd>
                    </div>
                </dl>
            </article>

            <article class="card-shell space-y-6" data-dashboard-card="system-health">
                <header class="flex items-start gap-4">
                    <span class="card-icon-container icon-purple shrink-0">
                        <x-icon name="shield" class="h-6 w-6" />
                    </span>

                    <div class="space-y-1">
                        <p class="section-kicker">{{ __('System health') }}</p>
                        <h3 class="dashboard-section-title text-lg font-semibold">{{ __('Operational posture') }}</h3>
                    </div>
                </header>

                <dl class="space-y-3">
                    <div class="card-shell-muted flex items-center justify-between gap-4">
                        <dt class="dashboard-metric-label text-sm">{{ __('Unverified Accounts') }}</dt>
                        <dd class="dashboard-stat-number text-lg font-semibold">{{ $stats['unverified_accounts'] ?? 0 }}</dd>
                    </div>
                    <div class="card-shell-muted flex items-center justify-between gap-4">
                        <dt class="dashboard-metric-label text-sm">{{ __('Total System Users') }}</dt>
                        <dd class="dashboard-stat-number text-lg font-semibold">{{ $stats['total_accounts'] ?? 0 }}</dd>
                    </div>
                    <div class="card-shell-muted flex items-center justify-between gap-4">
                        <dt class="dashboard-metric-label text-sm">{{ __('Active License Ratio') }}</dt>
                        <dd class="dashboard-stat-number flex items-center gap-2 text-lg font-semibold">
                            <x-status-badge :status="$activeRatio >= 70 ? 'stable' : ($activeRatio >= 40 ? 'warning' : 'inactive')" :text="$activeRatio.'%'" />
                        </dd>
                    </div>
                </dl>
            </article>
        </section>

        <section class="space-y-6" data-dashboard-database>
            <div class="space-y-2" data-dashboard-section-title-group>
                <p class="section-kicker">{{ __('Infrastructure') }}</p>
                <h2 class="dashboard-section-title text-xl font-semibold">{{ __('Database system status') }}</h2>
            </div>

            @if (isset($databaseStatus['error']))
                <div class="card-shell border-red-200/70 text-red-700 dark:border-red-800 dark:text-red-300" data-dashboard-error>
                    <div class="flex items-start gap-3">
                        <span class="card-icon-container icon-red h-11 w-11 shrink-0">
                            <x-icon name="error" class="h-6 w-6" />
                        </span>

                        <div class="space-y-1">
                            <p class="section-kicker">{{ __('Attention') }}</p>
                            <p class="font-medium">{{ $databaseStatus['error'] }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <article class="card-shell space-y-6" data-dashboard-card="database-info">
                    <header class="flex items-start gap-4">
                        <span class="card-icon-container icon-blue shrink-0">
                            <x-icon name="server" class="h-6 w-6" />
                        </span>

                        <div class="space-y-1">
                            <p class="section-kicker">{{ __('Database info') }}</p>
                            <h3 class="dashboard-section-title text-lg font-semibold">{{ __('Connection details') }}</h3>
                        </div>
                    </header>

                    <dl class="space-y-3">
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Name') }}</dt><dd class="dashboard-stat-number text-sm font-semibold">{{ $databaseStatus['database']['name'] ?? __('Unknown') }}</dd></div>
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Version') }}</dt><dd class="dashboard-stat-number text-sm font-semibold">{{ $databaseStatus['database']['version'] ?? __('Unknown') }}</dd></div>
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Size') }}</dt><dd class="dashboard-stat-number text-sm font-semibold">{{ number_format($databaseStatus['database']['size_mb'] ?? 0, 2) }} {{ __('MB') }}</dd></div>
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Connection') }}</dt><dd class="dashboard-stat-number text-sm font-semibold">{{ $databaseStatus['database']['connection'] ?? __('Unknown') }}</dd></div>
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Driver') }}</dt><dd class="dashboard-stat-number text-sm font-semibold">{{ $databaseStatus['database']['driver'] ?? __('Unknown') }}</dd></div>
                    </dl>
                </article>

                <article class="card-shell space-y-6" data-dashboard-card="connection-pool">
                    <header class="flex items-start gap-4">
                        <span class="card-icon-container icon-purple shrink-0">
                            <x-icon name="lightning" class="h-6 w-6" />
                        </span>

                        <div class="space-y-1">
                            <p class="section-kicker">{{ __('Connections') }}</p>
                            <h3 class="dashboard-section-title text-lg font-semibold">{{ __('Connection pool') }}</h3>
                        </div>
                    </header>

                    <dl class="space-y-3">
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Max Connections') }}</dt><dd class="dashboard-stat-number text-sm font-semibold">{{ $databaseStatus['connections']['max_connections'] ?? 0 }}</dd></div>
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Threads Connected') }}</dt><dd class="dashboard-stat-number text-sm font-semibold">{{ $databaseStatus['connections']['threads_connected'] ?? 0 }}</dd></div>
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Threads Running') }}</dt><dd class="dashboard-stat-number text-sm font-semibold">{{ $databaseStatus['connections']['threads_running'] ?? 0 }}</dd></div>
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Usage') }}</dt><dd><x-status-badge :status="$connectionUsage > 80 ? 'warning' : 'info'" :text="$connectionUsage.'%'" /></dd></div>
                    </dl>

                    <div class="space-y-2">
                        <div class="dashboard-metric-label flex items-center justify-between text-xs uppercase tracking-wide">
                            <span>{{ __('Utilization') }}</span>
                            <span>{{ $connectionUsage }}%</span>
                        </div>
                        <div class="app-shell-chip h-2 overflow-hidden rounded-full p-0">
                            <div class="h-full w-[var(--usage-width)] rounded-full bg-yellow-500 transition-all duration-300"
                                style="--usage-width: {{ $connectionUsage }}%"></div>
                        </div>
                    </div>
                </article>

                <article class="card-shell space-y-6" data-dashboard-card="queue-jobs">
                    <header class="flex items-start gap-4">
                        <span class="card-icon-container icon-orange shrink-0">
                            <x-icon name="document" class="h-6 w-6" />
                        </span>

                        <div class="space-y-1">
                            <p class="section-kicker">{{ __('Queues') }}</p>
                            <h3 class="dashboard-section-title text-lg font-semibold">{{ __('Queue jobs') }}</h3>
                        </div>
                    </header>

                    <dl class="space-y-3">
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Pending Jobs') }}</dt><dd class="dashboard-stat-number text-sm font-semibold">{{ $databaseStatus['queues']['pending_jobs'] ?? 0 }}</dd></div>
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Failed Jobs') }}</dt><dd class="dashboard-stat-number text-sm font-semibold">{{ $databaseStatus['queues']['failed_jobs'] ?? 0 }}</dd></div>
                    </dl>
                </article>

                <article class="card-shell space-y-6" data-dashboard-card="system-status">
                    <header class="flex items-start gap-4">
                        <span class="card-icon-container icon-green shrink-0">
                            <x-icon name="success" class="h-6 w-6" />
                        </span>

                        <div class="space-y-1">
                            <p class="section-kicker">{{ __('Availability') }}</p>
                            <h3 class="dashboard-section-title text-lg font-semibold">{{ __('System status') }}</h3>
                        </div>
                    </header>

                    <dl class="space-y-3">
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Database Uptime') }}</dt><dd class="dashboard-stat-number text-sm font-semibold">{{ $databaseStatus['uptime']['formatted'] ?? __('Unknown') }}</dd></div>
                        <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Cache').' ('.($databaseStatus['cache']['type'] ?? __('Unknown')).')' }}</dt><dd><x-status-badge :status="$cacheConnected ? 'active' : 'inactive'" :text="$cacheConnected ? __('Connected') : __('Disconnected')" /></dd></div>
                        @if (isset($databaseStatus['cache']['db_size']))
                            <div class="card-shell-muted flex items-center justify-between gap-4"><dt class="dashboard-metric-label text-sm">{{ __('Cache Keys') }}</dt><dd class="dashboard-stat-number text-sm font-semibold">{{ $databaseStatus['cache']['db_size'] ?? 0 }}</dd></div>
                        @endif
                    </dl>
                </article>
            </div>
        </section>
    </div>
</x-app-sidebar-layout>
