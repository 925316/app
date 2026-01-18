<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Package Details') }}
        </h2>
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Package Header -->
                    <div class="mb-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <h3 class="text-lg font-medium">Package: {{ $release->version }}</h3>
                                <div class="mt-2 flex items-center gap-4">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium
                                        {{ $release->release_channel === 'stable' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' }}">
                                        {{ ucfirst($release->release_channel) }} Release
                                    </span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                @if($isAdmin ?? false)
                                    <a href="{{ route('packages.versions') }}" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                                        Manage Versions
                                    </a>
                                @endif
                                <a href="{{ route('packages.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Package Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Basic Information</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Version:</span>
                                    <span class="font-medium">{{ $release->version }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Release Channel:</span>
                                    <span class="font-medium">{{ ucfirst($release->release_channel) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Released At:</span>
                                    <span class="font-medium">{{ $release->created_at->format('Y-m-d H:i:s') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">SHA256 Checksum:</span>
                                    <span class="font-medium break-all">{{ $release->checksum_sha256 }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">File Information</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Download URL:</span>
                                    <span class="font-medium break-all">{{ $release->download_url }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">File Size:</span>
                                    <span class="font-medium">
                                        @php
                                            $filePath = storage_path('app/public/' . $release->download_url);
                                            $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                                            $formattedSize = $fileSize > 0 ? \App\Services\PackageService::formatFileSize($fileSize) : 'Unknown';
                                        @endphp
                                        {{ $formattedSize }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Checksum Verified:</span>
                                    <span class="font-medium">
                                        @if(\App\Services\PackageService::verifyChecksum($release))
                                            <span class="text-green-600 dark:text-green-400">✓ Valid</span>
                                        @else
                                            <span class="text-red-600 dark:text-red-400">✗ Invalid</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Changelog -->
                    @if($release->changelog)
                        <div class="mb-6">
                            <h4 class="font-medium mb-2 text-gray-800 dark:text-gray-200">Changelog</h4>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="prose dark:prose-invert max-w-none text-sm">
                                    {!! nl2br(e($release->changelog)) !!}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Download Section -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Download</h4>
                        <div class="flex flex-col sm:flex-row gap-3">
                            @if($canDownload ?? false)
                                <a href="{{ route('packages.download', $release) }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-center">
                                    Download Package
                                </a>
                            @else
                                <button disabled class="px-4 py-2 bg-gray-400 text-white rounded-md cursor-not-allowed">
                                    Download Requires Valid License
                                </button>
                            @endif

                            @if($isAdmin ?? false)
                                <button onclick="showChangelogModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                                    Edit Changelog
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Admin Actions -->
                    @if($isAdmin ?? false)
                        <div class="mb-6">
                            <h4 class="font-medium mb-3 text-gray-800 dark:text-gray-200">Admin Actions</h4>
                            <div class="flex flex-wrap gap-2">
                                <form action="{{ route('packages.destroy', $release) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this package release? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                                        Delete Release
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
    @if($isAdmin ?? false)
        <div id="changelogModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg max-w-2xl w-full mx-4">
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Edit Changelog</h3>
                <form action="{{ route('packages.update-changelog', $release) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="changelog" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Changelog</label>
                        <textarea name="changelog" id="changelog" rows="8"
                                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ old('changelog', $release->changelog) }}</textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="hideChangelogModal()" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                            Update Changelog
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
</x-app-layout>
