<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Software Packages') }}
        </h2>
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header with actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h3 class="text-lg font-medium">Available Software Packages</h3>

                        @if($isAdmin ?? false)
                            <div class="flex gap-2">
                                <a href="{{ route('packages.upload') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                    Add New Package
                                </a>
                                <a href="{{ route('packages.manage') }}" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
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
                                <div class="font-medium">{{ $stats['total_releases'] ?? 0 }}</div>
                            </div>
                            <div>
                                <div class="text-gray-600 dark:text-gray-300">Stable Releases:</div>
                                <div class="font-medium">{{ $stats['stable_releases'] ?? 0 }}</div>
                            </div>
                            <div>
                                <div class="text-gray-600 dark:text-gray-300">Dev Releases:</div>
                                <div class="font-medium">{{ $stats['dev_releases'] ?? 0 }}</div>
                            </div>
                            <div>
                                <div class="text-gray-600 dark:text-gray-300">Latest Stable:</div>
                                <div class="font-medium">{{ $stats['latest_stable']?->version ?? 'None' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Channel Filter -->
                    <div class="mb-6">
                        <form method="GET" action="{{ route('packages.index') }}" class="flex items-center gap-4">
                            <div>
                                <label for="channel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Release Channel</label>
                                <select name="channel" id="channel" onchange="this.form.submit()"
                                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option value="">All Channels</option>
                                    <option value="stable" {{ request('channel') === 'stable' ? 'selected' : '' }}>Stable</option>
                                    <option value="dev" {{ request('channel') === 'dev' ? 'selected' : '' }}>Development</option>
                                </select>
                            </div>
                            @if(request('channel'))
                                <a href="{{ route('packages.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition mt-auto">
                                    Reset Filter
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- Packages table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Version
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Channel
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Released
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Hash Verification
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($releases as $release)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $release->version }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                                {{ $release->release_channel === 'stable' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' }}">
                                                {{ ucfirst($release->release_channel) }}
                                            </span>
                                            @if($release->version === ($stats['latest_stable']?->version ?? null))
                                                <span class="ml-2 px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-xs font-medium">
                                                    Latest Stable
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $release->created_at ? $release->created_at->format('Y-m-d H:i:s') : 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            @if($release->virus_detection_url)
                                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                    Available
                                                </span>
                                            @else
                                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                                    None
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('packages.show', $release) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                                Details
                                            </a>
                                            @if($canDownload ?? false && $release->id)
                                                <span class="mx-2 text-gray-400">|</span>
                                                <a href="{{ route('packages.download', ['release' => $release->id]) }}" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300">
                                                    Download
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-300">
                                            No packages found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $releases->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
