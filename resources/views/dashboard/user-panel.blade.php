<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Stay on top of your license access, devices, and usage history.') }}
    </x-slot>

    <div class="space-y-8" data-page="dashboard-user">
        <section class="card-shell flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between" data-dashboard-summary>
            <div class="space-y-2" data-dashboard-section-title-group>
                <p class="section-kicker">{{ __('Account overview') }}</p>
                <h2 class="dashboard-section-title text-2xl font-semibold">{{ __('Your current access') }}</h2>
                <p class="dashboard-meta-text max-w-2xl text-sm">
                    {{ __('Review your active license posture, device readiness, and the usage signals that matter most.') }}
                </p>
            </div>

            <div class="card-shell-muted flex items-center gap-3 self-start lg:self-auto" data-dashboard-summary-chip>
                <span class="card-icon-container icon-indigo h-11 w-11 shrink-0">
                    <x-icon name="document" class="h-6 w-6" />
                </span>

                <div>
                    <p class="section-kicker">{{ __('Last updated') }}</p>
                    <p class="dashboard-meta-text text-sm font-medium">{{ now()->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </section>

        <section class="space-y-4" data-dashboard-section="license-status">
            <div class="space-y-1" data-dashboard-section-title-group>
                <p class="section-kicker">{{ __('License') }}</p>
                <h2 class="dashboard-section-title text-xl font-semibold">{{ __('License status') }}</h2>
            </div>

            @if ($activeLicense)
                <article class="card-shell space-y-6" data-license-state="active">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-4">
                            <div class="flex flex-wrap gap-2">
                                <x-status-badge status="active" :text="$activeLicense->getStatusTextAttribute()" />
                                <x-status-badge status="upgrade" :text="$activeLicense->getPrivilegeTextAttribute()" />
                            </div>

                            <div>
                                <p class="section-kicker">{{ __('License key') }}</p>
                                <p class="app-shell-chip app-shell-chip-strong mt-2 rounded-xl px-4 py-3 font-mono text-lg font-semibold">
                                    {{ $activeLicense->key }}
                                </p>
                            </div>
                        </div>

                        <span class="card-icon-container icon-green h-14 w-14 shrink-0">
                            <x-icon name="success" class="h-7 w-7" />
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="card-shell-muted">
                            <p class="section-kicker">{{ __('Expires') }}</p>
                            <p class="dashboard-stat-number mt-2 text-lg font-semibold">{{ $activeLicense->expires_at->format('Y-m-d') }}</p>
                        </div>
                        <div class="card-shell-muted">
                            <p class="section-kicker">{{ __('Days remaining') }}</p>
                            <p class="mt-2 text-lg font-semibold text-green-600 dark:text-green-300">{{ $activeLicense->daysUntilExpiry() }} {{ __('days') }}</p>
                        </div>
                    </div>
                </article>
            @else
                <article class="card-shell space-y-5" data-license-state="inactive">
                    <div class="flex items-start gap-4">
                        <span class="card-icon-container icon-yellow h-12 w-12 shrink-0">
                            <x-icon name="warning" class="h-6 w-6" />
                        </span>

                        <div class="space-y-2">
                            <h3 class="dashboard-section-title text-lg font-semibold">{{ __('No Active License') }}</h3>
                            <p class="dashboard-metric-label text-sm">
                                {{ __('You do not have an active license. Please contact support or purchase a license to access premium features.') }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('licenses.index') }}" class="btn btn-primary btn-sm gap-2">
                            <x-icon name="plus" class="h-4 w-4" />
                            {{ __('View Available Licenses') }}
                        </a>
                    </div>
                </article>
            @endif
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2" data-dashboard-details>
            <article class="card-shell space-y-6" data-dashboard-card="device-status">
                <header class="flex items-start gap-4">
                    <span class="card-icon-container icon-blue shrink-0">
                        <x-icon name="desktop" class="h-6 w-6" />
                    </span>

                    <div class="space-y-1">
                        <p class="section-kicker">{{ __('Devices') }}</p>
                        <h3 class="dashboard-section-title text-lg font-semibold">{{ __('Device status') }}</h3>
                    </div>
                </header>

                @if ($boundDevices > 0)
                    <div class="space-y-4">
                        <div class="card-shell-muted flex items-center justify-between gap-4">
                            <div>
                                <p class="section-kicker">{{ __('Bound devices') }}</p>
                                <p class="dashboard-stat-number mt-2 text-lg font-semibold">{{ $boundDevices }} {{ __('Device(s) Bound') }}</p>
                            </div>

                            <x-status-badge status="bound" :text="__('Ready')" />
                        </div>

                        <p class="dashboard-metric-label text-sm">
                            {{ __('Your devices are successfully bound to your account and can access licensed software.') }}
                        </p>

                        <div>
                            <a href="{{ route('devices.manage') }}" class="btn btn-primary btn-sm gap-2">
                                <x-icon name="edit" class="h-4 w-4" />
                                {{ __('Manage Devices') }}
                            </a>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div class="card-shell-muted flex items-center justify-between gap-4">
                            <div>
                                <p class="section-kicker">{{ __('Bound devices') }}</p>
                                <p class="dashboard-stat-number mt-2 text-lg font-semibold">{{ __('No Bound Device') }}</p>
                            </div>

                            <x-status-badge status="default" :text="__('Needs setup')" />
                        </div>

                        <p class="dashboard-metric-label text-sm">
                            {{ __('You have not bound any device to your account yet. Bind a device to start using licensed software.') }}
                        </p>

                        <div>
                            <a href="{{ route('devices.manage') }}" class="btn btn-primary btn-sm gap-2">
                                <x-icon name="plus" class="h-4 w-4" />
                                {{ __('Bind a Device') }}
                            </a>
                        </div>
                    </div>
                @endif
            </article>

            <article class="card-shell space-y-6" data-dashboard-card="usage-statistics">
                <header class="flex items-start gap-4">
                    <span class="card-icon-container icon-purple shrink-0">
                        <x-icon name="lightning" class="h-6 w-6" />
                    </span>

                    <div class="space-y-1">
                        <p class="section-kicker">{{ __('Usage') }}</p>
                        <h3 class="dashboard-section-title text-lg font-semibold">{{ __('Usage statistics') }}</h3>
                    </div>
                </header>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="card-shell-muted space-y-2">
                        <p class="section-kicker">{{ __('Total usage time') }}</p>
                        <p class="text-3xl font-bold text-purple-700 dark:text-purple-300">{{ $usageTimeFormatted }}</p>
                    </div>

                    <div class="card-shell-muted space-y-2">
                        <p class="section-kicker">{{ __('Login count') }}</p>
                        <p class="text-3xl font-bold text-indigo-700 dark:text-indigo-300">{{ $userStats['login_count'] ?? 0 }}</p>
                    </div>
                </div>
            </article>
        </section>
    </div>
</x-app-sidebar-layout>
