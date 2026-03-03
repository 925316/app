<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Software Packages') }}
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            @php
                $showDevStats = Auth::user()->hasPrivilege(6) || Auth::user()->hasPrivilege(7);
                $gridCols = $showDevStats ? 'lg:grid-cols-4' : 'lg:grid-cols-3';
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 {{ $gridCols }} gap-4 mb-6">
                <x-stat-card title="Total Releases" :value="$stats['total_releases'] ?? 0" icon="cube" iconColor="icon-blue" />
                <x-stat-card title="Stable" :value="$stats['stable_releases'] ?? 0" icon="success" iconColor="icon-green" />
                @if ($showDevStats)
                    <x-stat-card title="Dev" :value="$stats['dev_releases'] ?? 0" icon="lightning" iconColor="icon-purple" />
                @endif
                <x-stat-card title="Latest Stable" :value="$stats['latest_stable']?->version ?? 'None'" icon="cloud" iconColor="icon-yellow" />
            </div>

            <!-- Latest Stable Release - For All Users -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Latest Stable Release</h3>

                    @if ($stats['latest_stable'] ?? null)
                        <div class="space-y-4">
                            <!-- Version Badge -->
                            <div class="flex flex-wrap items-center gap-3">
                                <span
                                    class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm font-medium">
                                    Version {{ $stats['latest_stable']->version }}
                                </span>
                                <span
                                    class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-medium">
                                    Stable
                                </span>
                                <span
                                    class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-full text-sm font-medium">
                                    Latest
                                </span>
                            </div>

                            <!-- Release Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Release Date</h4>
                                    <p class="text-gray-900 dark:text-gray-100">
                                        {{ $stats['latest_stable']->created_at ? $stats['latest_stable']->created_at->format('Y-m-d H:i:s') : 'Unknown' }}
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Security
                                        Verification</h4>
                                    <p class="text-gray-900 dark:text-gray-100">
                                        @if ($stats['latest_stable']->virus_detection_url)
                                            <span class="text-green-600 dark:text-green-400">
                                                ✓ Verified
                                            </span>
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">
                                                Not available
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Changelog -->
                            @if ($stats['latest_stable']->changelog)
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Changelog</h4>
                                    <div
                                        class="prose dark:prose-invert max-w-none text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                        {!! nl2br(e($stats['latest_stable']->changelog)) !!}
                                    </div>
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap gap-3 mt-6">
                                <a href="{{ route('packages.show', $stats['latest_stable']) }}"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    View Details
                                </a>
                                @if ($canDownload ?? false)
                                    <a href="{{ route('packages.download', ['release' => $stats['latest_stable']->id]) }}"
                                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                            </path>
                                        </svg>
                                        Download
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">
                                No stable releases available yet.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            @if (Auth::user()->hasPrivilege(7))
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Header with actions -->
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">All Package Releases</h3>

                            @if ($isAdmin ?? false)
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('packages.upload') }}"
                                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                        Add New Package
                                    </a>
                                    <a href="{{ route('packages.manage') }}"
                                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                                        Manage Packages
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Statistics -->
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Package Statistics</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <div class="text-gray-600 dark:text-gray-300">Total Releases:</div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $stats['total_releases'] ?? 0 }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-600 dark:text-gray-300">Stable Releases:</div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $stats['stable_releases'] ?? 0 }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-600 dark:text-gray-300">Dev Releases:</div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $stats['dev_releases'] ?? 0 }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-600 dark:text-gray-300">Latest Stable:</div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $stats['latest_stable']?->version ?? 'None' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Channel Filter -->
                        <div class="mb-6">
                            <form method="GET" action="{{ route('packages.index') }}"
                                class="flex items-center gap-4">
                                <div>
                                    <label for="channel"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Release
                                        Channel</label>
                                    <select name="channel" id="channel" onchange="this.form.submit()"
                                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        <option value="">All Channels</option>
                                        <option value="stable"
                                            {{ request('channel') === 'stable' ? 'selected' : '' }}>
                                            Stable
                                        </option>
                                        <option value="dev" {{ request('channel') === 'dev' ? 'selected' : '' }}>
                                            Development
                                        </option>
                                    </select>
                                </div>
                                @if (request('channel'))
                                    <a href="{{ route('packages.index') }}"
                                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition mt-auto">
                                        Reset Filter
                                    </a>
                                @endif
                            </form>
                        </div>

                        <!-- Packages table -->
                        <x-table :headers="['Version', 'Channel', 'Released', 'Hash', 'Actions']" :emptyColspan="5">
                            @forelse($releases as $release)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $release->version }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium
                                            {{ $release->release_channel === 'stable' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' }}">
                                            {{ ucfirst($release->release_channel) }}
                                        </span>
                                        @if ($release->version === ($stats['latest_stable']?->version ?? null))
                                            <span class="ml-1 px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">
                                                Latest
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $release->created_at ? $release->created_at->format('Y-m-d H:i') : 'Unknown' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        @if ($release->virus_detection_url)
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                Available
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                                None
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('packages.show', $release) }}"
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                            Details
                                        </a>
                                        @if ($canDownload ?? false && $release->id)
                                            <span class="mx-1 text-gray-400">|</span>
                                            <a href="{{ route('packages.download', ['release' => $release->id]) }}"
                                                class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300">
                                                Download
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-300">
                                        No packages found.
                                    </td>
                                </tr>
                            @endforelse
                        </x-table>

                        <!-- Pagination -->
                        <div class="mt-4">
                            <x-pagination :paginator="$releases" />
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-sidebar-layout>
