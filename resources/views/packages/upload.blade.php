<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Package') }}
        </h2>
    </x-slot>

    <div class="py-7">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('packages.store') }}">
                        @csrf

                        <!-- Version -->
                        <div class="mb-4">
                            <label for="version" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Version</label>
                            <input type="text" name="version" id="version" value="{{ old('version') }}"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                   placeholder="e.g., 1.0.0 (Semantic Versioning)">
                            @error('version')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Please use semantic versioning format (e.g., 1.0.0, 2.3.1-beta.1)
                            </p>
                        </div>

                        <!-- Release Channel -->
                        <div class="mb-4">
                            <label for="release_channel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Release Channel</label>
                            <select name="release_channel" id="release_channel" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                <option value="stable" {{ old('release_channel') === 'stable' ? 'selected' : '' }}>Stable</option>
                                <option value="dev" {{ old('release_channel') === 'dev' ? 'selected' : '' }}>Development</option>
                            </select>
                            @error('release_channel')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Download URL -->
                        <div class="mb-4">
                            <label for="download_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Download URL</label>
                            <input type="url" name="download_url" id="download_url" value="{{ old('download_url') }}"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                   placeholder="https://example.com/downloads/package.zip">
                            @error('download_url')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Enter the direct download URL for the package file.
                            </p>
                        </div>

                        <!-- Virus Detection Link -->
                        <div class="mb-4">
                            <label for="virus_detection_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Virus Detection Link (Optional)</label>
                            <textarea name="virus_detection_link" id="virus_detection_link" rows="3"
                                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                      placeholder="https://www.virustotal.com/gui/file/abc123&#10;https://www.hybrid-analysis.com/sample/def456&#10;https://www.joesandbox.com/analysis/789">{{ old('virus_detection_link') }}</textarea>
                            @error('virus_detection_link')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Optional: Add virus scanning or hash verification links (one per line). These will be displayed as clickable links for users to verify package safety.
                            </p>
                        </div>

                        <!-- Changelog -->
                        <div class="mb-4">
                            <label for="changelog" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Changelog</label>
                            <textarea name="changelog" id="changelog" rows="6"
                                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                      placeholder="Describe the changes in this version...">{{ old('changelog') }}</textarea>
                            @error('changelog')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-3 mt-6">
                            <a href="{{ route('packages.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                Add Package
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
