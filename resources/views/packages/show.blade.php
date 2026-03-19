<x-app-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Package Details') }}
        </h2>
    </x-slot>

    @php
        $deletePackageReleaseConfirmation = __('Are you sure you want to delete this package release? This action cannot be undone.');
    @endphp

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Package Header -->
                    <div class="mb-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Package:') }} {{ $release->version }}</h3>
                                <div class="mt-2 flex items-center gap-4">
                                    <span
                                        class="px-3 py-1 rounded-full text-sm font-medium
                                        {{ $release->release_channel === 'stable' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' }}">
                                        {{ ucfirst($release->release_channel) }} {{ __('Release') }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                @if ($isAdmin ?? false)
                                    <a href="{{ route('packages.manage') }}"
                                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                                        {{ __('Manage Packages') }}
                                    </a>
                                @endif
                                <a href="{{ route('packages.index') }}"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                                    {{ __('Back to List') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Package Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">{{ __('Basic Information') }}</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Version:') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $release->version }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Release Channel:') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ ucfirst($release->release_channel) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Released At:') }}</span>
                                    <span
                                        class="font-medium text-gray-900 dark:text-white">{{ $release->created_at ? $release->created_at->format('Y-m-d H:i:s') : __('Unknown') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">{{ __('File Information') }}</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Download URL:') }}</span>
                                    <span class="font-medium break-all text-gray-900 dark:text-white">{{ $release->download_url }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('File Size:') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ __('Remote file (size not available)') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Checksum Verified:') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('Not applicable for remote files') }}</span>
                                    </span>
                                </div>
                                @if ($release->virus_detection_url)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-300">{{ __('Virus Detection:') }}</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            <span class="text-gray-600 dark:text-gray-400">{{ __('Available') }}</span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Changelog -->
                    @if ($release->changelog)
                        <div class="mb-6">
                            <h4 class="font-medium mb-2 text-gray-800 dark:text-gray-200">{{ __('Changelog') }}</h4>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="prose dark:prose-invert max-w-none text-sm">
                                    {!! nl2br(e($release->changelog)) !!}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Virus Detection Links -->
                    @if ($release->virus_detection_url)
                        <div class="mb-6">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">{{ __('Virus Detection') }}</h4>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">{{ __('Verify package safety using these links:') }}</p>
                                <div class="space-y-2">
                                    @foreach (explode("\n", $release->virus_detection_url) as $url)
                                        @if (trim($url))
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 00-5.656-5.656l-1.1 1.1">
                                                    </path>
                                                </svg>
                                                <a href="{{ trim($url) }}" target="_blank" rel="noopener noreferrer"
                                                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline break-all">
                                                    {{ trim($url) }}
                                                </a>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Download Section -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">{{ __('Download') }}</h4>
                        <div class="flex flex-col sm:flex-row gap-3">
                            @if ($canDownload ?? false && $release->id)
                                <a href="{{ route('packages.download', ['release' => $release->id]) }}"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-center">
                                    {{ __('Download Package') }}
                                </a>
                            @else
                                <button disabled class="px-4 py-2 bg-gray-400 text-white rounded-md cursor-not-allowed">
                                    {{ __('Download Requires Valid License') }}
                                </button>
                            @endif

                            @if ($isAdmin ?? false)
                                <button onclick="showChangelogModal()"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                                    {{ __('Edit Changelog') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Admin Actions -->
                    @if ($isAdmin ?? false)
                        <div class="mb-6">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">{{ __('Admin Actions') }}</h4>
                            <div class="flex flex-wrap gap-2">
                                <form action="{{ route('packages.destroy', $release) }}" method="POST"
                                    onsubmit="return confirm('{{ $deletePackageReleaseConfirmation }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                                        {{ __('Delete Release') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Changelog Modal -->
    @if ($isAdmin ?? false)
        <div id="changelogModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg max-w-2xl w-full mx-4">
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">{{ __('Edit Changelog') }}</h3>
                <form action="{{ route('packages.update-changelog', $release) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="changelog"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Changelog') }}</label>
                        <textarea name="changelog" id="changelog" rows="8"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ old('changelog', $release->changelog) }}</textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="hideChangelogModal()"
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                            {{ __('Update Changelog') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function showChangelogModal() {
                document.getElementById('changelogModal').classList.remove('hidden');
                document.getElementById('changelogModal').classList.add('flex');
            }

            function hideChangelogModal() {
                document.getElementById('changelogModal').classList.add('hidden');
                document.getElementById('changelogModal').classList.remove('flex');
            }

            // Close modal when clicking outside
            document.getElementById('changelogModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideChangelogModal();
                }
            });
        </script>
    @endif

</x-app-sidebar-layout>
