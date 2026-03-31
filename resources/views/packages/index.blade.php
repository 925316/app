<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Software Packages') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Review release channels, surface the latest stable build, and keep package actions unchanged.') }}
    </x-slot>

    @php
        $showDevStats = Auth::user()->hasPrivilege(6) || Auth::user()->hasPrivilege(7);
        $gridCols = $showDevStats ? 'xl:grid-cols-4' : 'xl:grid-cols-3';
        $latestStable = $stats['latest_stable'] ?? null;
        $hasChannelFilter = filled(request('channel'));
    @endphp

    <div class="space-y-8" data-page="packages-index">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 {{ $gridCols }}" aria-label="{{ __('Package statistics') }}">
            <x-stat-card :title="__('Total Releases')" :value="$stats['total_releases'] ?? 0" icon="cube" iconColor="icon-blue" />
            <x-stat-card :title="__('Stable')" :value="$stats['stable_releases'] ?? 0" icon="success" iconColor="icon-green" />
            @if ($showDevStats)
                <x-stat-card :title="__('Dev')" :value="$stats['dev_releases'] ?? 0" icon="lightning" iconColor="icon-purple" />
            @endif
            <x-stat-card :title="__('Latest Stable')" :value="$latestStable?->version ?? __('None')" icon="cloud" iconColor="icon-yellow" />
        </section>

        <section class="card-shell space-y-6" data-latest-package-panel>
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Release spotlight') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Latest stable release') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Keep the primary download path front and center while preserving the current routes and permissions.') }}</p>
                </div>
            </div>

            @if ($latestStable)
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(18rem,1fr)]">
                    <div class="card-shell-muted space-y-5 p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-status-badge status="active" :text="__('Stable')" />
                                    <span class="badge badge-info">{{ __('Latest') }}</span>
                                </div>

                                <div>
                                    <p class="section-kicker">{{ __('Release version') }}</p>
                                    <h3 class="card-heading text-2xl font-semibold">{{ __('Version') }} {{ $latestStable->version }}</h3>
                                </div>
                            </div>

                            <div class="card-shell-muted min-w-[14rem] space-y-2 self-start p-4">
                                <p class="section-kicker">{{ __('Security verification') }}</p>
                                @if ($latestStable->virus_detection_url)
                                    <x-status-badge status="verified" :text="__('Verified')" />
                                    <p class="app-shell-body-copy text-sm">{{ __('Virus detection details are available for this build.') }}</p>
                                @else
                                    <span class="badge badge-default">{{ __('Not available') }}</span>
                                    <p class="app-shell-body-copy text-sm">{{ __('No external verification link was published for this release.') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="card-shell-muted space-y-2 p-4">
                                <p class="section-kicker">{{ __('Released') }}</p>
                                <p class="card-heading text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $latestStable->created_at ? $latestStable->created_at->format('Y-m-d H:i:s') : __('Unknown') }}
                                </p>
                                @if ($latestStable->created_at)
                                    <p class="app-shell-body-copy text-sm">{{ $latestStable->created_at->diffForHumans() }}</p>
                                @endif
                            </div>

                            <div class="card-shell-muted space-y-2 p-4">
                                <p class="section-kicker">{{ __('Channel') }}</p>
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-status-badge status="active" :text="ucfirst($latestStable->release_channel)" />
                                    <span class="badge badge-stable">{{ __('Production ready') }}</span>
                                </div>
                                <p class="app-shell-body-copy text-sm">{{ __('This is the release currently surfaced as the latest stable build.') }}</p>
                            </div>
                        </div>

                        @if ($latestStable->changelog)
                            <div class="space-y-3">
                                <div>
                                    <p class="section-kicker">{{ __('Release notes') }}</p>
                                    <h4 class="card-heading text-base font-semibold text-gray-900 dark:text-white">{{ __('Changelog') }}</h4>
                                </div>

                                <div class="card-shell-muted p-4">
                                    <div class="prose max-w-none text-sm text-gray-700 dark:prose-invert dark:text-gray-300">
                                        {!! nl2br(e($latestStable->changelog)) !!}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <aside class="card-shell-muted flex flex-col justify-between gap-6 p-6">
                        <div class="space-y-3">
                            <p class="section-kicker">{{ __('Primary actions') }}</p>
                            <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('Inspect or download') }}</h3>
                            <p class="app-shell-body-copy text-sm">
                                {{ __('Use the same detail and download endpoints while presenting them through the shared button system.') }}
                            </p>
                        </div>

                        <div class="grid gap-3">
                            <a href="{{ route('packages.show', $latestStable) }}" class="btn btn-primary btn-sm justify-center gap-2">
                                <x-icon name="info" class="h-4 w-4" />
                                {{ __('View Details') }}
                            </a>

                            @if ($canDownload ?? false)
                                <a href="{{ route('packages.download', ['release' => $latestStable->id]) }}" class="btn btn-secondary btn-sm justify-center gap-2">
                                    <x-icon name="cloud" class="h-4 w-4" />
                                    {{ __('Download') }}
                                </a>
                            @endif
                        </div>
                    </aside>
                </div>
            @else
                <div class="table-empty-state px-6 py-12 text-center">
                    <x-icon name="cube" class="table-empty-icon" />
                    <p class="table-empty-title">{{ __('No stable releases available yet.') }}</p>
                    <p class="table-empty-copy">{{ __('Publish a stable build to populate this spotlight surface.') }}</p>
                </div>
            @endif
        </section>

        @if (Auth::user()->hasPrivilege(7))
            <section class="card-shell space-y-6" data-packages-admin-panel>
                <div class="app-toolbar">
                    <div>
                        <p class="section-kicker">{{ __('Release inventory') }}</p>
                        <h2 class="app-toolbar-title">{{ __('All package releases') }}</h2>
                        <p class="app-toolbar-subtitle">{{ __('Preserve the staff-only package list and channel filter while aligning it to the shared data-surface system.') }}</p>
                    </div>

                    @if ($isAdmin ?? false)
                        <div class="app-toolbar-actions">
                            <a href="{{ route('packages.upload') }}" class="btn btn-primary btn-sm gap-2">
                                <x-icon name="plus" class="h-4 w-4" />
                                {{ __('Add New Package') }}
                            </a>
                            <a href="{{ route('packages.manage') }}" class="btn btn-secondary btn-sm gap-2">
                                {{ __('Manage Packages') }}
                            </a>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Admin package statistics') }}">
                    <div class="card-shell-muted space-y-2 p-4">
                        <p class="section-kicker">{{ __('Total releases') }}</p>
                        <p class="card-heading text-xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_releases'] ?? 0 }}</p>
                    </div>
                    <div class="card-shell-muted space-y-2 p-4">
                        <p class="section-kicker">{{ __('Stable releases') }}</p>
                        <p class="card-heading text-xl font-semibold text-gray-900 dark:text-white">{{ $stats['stable_releases'] ?? 0 }}</p>
                    </div>
                    <div class="card-shell-muted space-y-2 p-4">
                        <p class="section-kicker">{{ __('Dev releases') }}</p>
                        <p class="card-heading text-xl font-semibold text-gray-900 dark:text-white">{{ $stats['dev_releases'] ?? 0 }}</p>
                    </div>
                    <div class="card-shell-muted space-y-2 p-4">
                        <p class="section-kicker">{{ __('Latest stable') }}</p>
                        <p class="card-heading text-xl font-semibold text-gray-900 dark:text-white">{{ $latestStable?->version ?? __('None') }}</p>
                    </div>
                </div>

                <x-filter-box :action="route('packages.index')" :title="__('Filter releases')">
                    <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-12">
                        <div class="md:col-span-5">
                            <x-filter-dropdown
                                id="channel"
                                name="channel"
                                :label="__('Release Channel')"
                                :value="request('channel')"
                                :submit-on-select="true"
                                :options="[
                                    '' => __('All Channels'),
                                    'stable' => __('Stable'),
                                    'dev' => __('Development'),
                                ]"
                            />
                        </div>

                        <div class="space-y-2 md:col-span-7 filter-box-actions">
                            <span class="form-label text-transparent">{{ __('Actions') }}</span>
                            <div class="form-actions-cluster">
                                <button type="submit" class="btn btn-primary btn-sm flex-1 justify-center gap-2 md:flex-none">
                                    <x-icon name="search" class="h-4 w-4" />
                                    {{ __('Apply Filter') }}
                                </button>

                                @if ($hasChannelFilter)
                                    <a href="{{ route('packages.index') }}" class="btn btn-secondary btn-sm justify-center gap-2">
                                        <x-icon name="reset" class="h-4 w-4" />
                                        {{ __('Reset Filter') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($hasChannelFilter)
                        <div class="active-filters" data-active-filters>
                            <div class="active-filters__header">
                                <div class="active-filters__copy">
                                    <p class="active-filters__title">{{ __('Active Filters') }}</p>
                                    <p class="active-filters__subtitle">{{ __('Clear the current channel view without changing staff-only access or pagination behavior.') }}</p>
                                </div>
                                <a href="{{ route('packages.index') }}" class="active-filters__clear">
                                    {{ __('Clear All') }}
                                </a>
                            </div>

                            <div class="active-filters__list">
                                <x-filter-badge :label="__('Channel:').' '.(request('channel') === 'dev' ? __('Development') : __('Stable'))" color="green" :removeUrl="request()->fullUrlWithQuery(['channel' => null])" />
                            </div>
                        </div>
                    @endif
                </x-filter-box>

                <x-table :headers="[__('Version'), __('Channel'), __('Released'), __('Hash'), __('Actions')]" :emptyColspan="5" compact="true" ariaLabel="{{ __('Packages table') }}">
                    @forelse ($releases as $release)
                        <tr class="table-row">
                            <td class="table-cell-primary">
                                <div class="table-stack table-stack-tight">
                                    <div class="table-title text-sm">{{ $release->version }}</div>
                                    <div class="table-meta">{{ __('Release ID:') }} {{ $release->id }}</div>
                                </div>
                            </td>

                            <td class="table-cell">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-status-badge :status="$release->release_channel === 'stable' ? 'active' : 'info'" :text="ucfirst($release->release_channel)" />

                                    @if ($release->version === ($latestStable?->version ?? null))
                                        <span class="badge badge-info">{{ __('Latest') }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="table-cell">
                                @if ($release->created_at)
                                    <div class="table-stack table-stack-tight">
                                        <div>{{ $release->created_at->format('Y-m-d H:i') }}</div>
                                        <div class="table-meta">{{ $release->created_at->diffForHumans() }}</div>
                                    </div>
                                @else
                                    <span class="table-meta">{{ __('Unknown') }}</span>
                                @endif
                            </td>

                            <td class="table-cell">
                                @if ($release->virus_detection_url)
                                    <div class="table-stack table-stack-tight">
                                        <x-status-badge status="verified" :text="__('Available')" />
                                        <div class="table-meta">{{ __('Verification link published') }}</div>
                                    </div>
                                @else
                                    <span class="badge badge-default">{{ __('None') }}</span>
                                @endif
                            </td>

                            <td class="table-cell text-right">
                                <div class="table-actions">
                                    <a href="{{ route('packages.show', $release) }}" class="table-action table-action--primary">
                                        {{ __('Details') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="5" class="table-empty">
                                <div class="table-empty-state">
                                    <x-icon name="cube" class="table-empty-icon" />
                                    <p class="table-empty-title">{{ __('No packages found.') }}</p>
                                    <p class="table-empty-copy">{{ __('Try removing the current channel filter to surface more releases.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </x-table>

                <div>
                    <x-pagination :paginator="$releases" />
                </div>
            </section>
        @endif
    </div>
</x-app-sidebar-layout>
