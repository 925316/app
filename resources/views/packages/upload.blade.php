<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Add Package') }}
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div
            class="bg-white/80 dark:bg-cool-800/80 backdrop-blur-sm rounded-xl shadow-sm border border-cool-200/50 dark:border-cool-700/50 p-6">
            <form method="POST" action="{{ route('packages.store') }}">
            @csrf

            <!-- Version -->
            <div class="mb-4">
                <label for="version"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Version') }}</label>
                <input type="text" name="version" id="version" value="{{ old('version') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                    placeholder="{{ __('e.g., 1.0.0 (semantic versioning)') }}">
                @error('version')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Please use semantic versioning format (e.g., 1.0.0, 2.3.1-beta.1)') }}
                </p>
            </div>

            <!-- Release Channel -->
            <div class="mb-4">
                <label for="release_channel"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Release Channel') }}</label>
                <select name="release_channel" id="release_channel"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    <option value="stable" {{ old('release_channel') === 'stable' ? 'selected' : '' }}>{{ __('Stable') }}</option>
                    <option value="dev" {{ old('release_channel') === 'dev' ? 'selected' : '' }}>{{ __('Development') }}</option>
                </select>
                @error('release_channel')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Download URL -->
            <div class="mb-4">
                <label for="download_url"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Download URL') }}</label>
                <input type="url" name="download_url" id="download_url" value="{{ old('download_url') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                    placeholder="{{ __('https://example.com/downloads/package.zip') }}">
                @error('download_url')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Enter the direct download URL for the package file.') }}
                </p>
            </div>

            <!-- Virus Detection Link -->
            <div class="mb-4">
                <label for="virus_detection_url"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Virus Detection Link (Optional)') }}</label>
                <textarea name="virus_detection_url" id="virus_detection_url" rows="3"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                    placeholder="{{ __('https://www.virustotal.com/gui/file/abc123') }}&#10;{{ __('https://www.hybrid-analysis.com/sample/def456') }}&#10;{{ __('https://www.joesandbox.com/analysis/789') }}">{{ old('virus_detection_url') }}</textarea>
                @error('virus_detection_url')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Optional: Add virus scanning or hash verification links (one per line). These will be displayed as clickable links for users to verify package safety.') }}
                </p>
            </div>

            <!-- Changelog -->
            <div class="mb-4">
                <label for="changelog"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Changelog') }}</label>
                <textarea name="changelog" id="changelog" rows="6"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                    placeholder="{{ __('Describe the changes in this version...') }}">{{ old('changelog') }}</textarea>
                @error('changelog')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('packages.index') }}"
                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                    {{ __('Add Package') }}
                </button>
            </div>
            </form>
        </div>
    </div>

</x-app-sidebar-layout>
