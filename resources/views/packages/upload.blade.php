<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Add Package') }}
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="card-shell">
            <div class="app-toolbar mb-6">
                <div>
                    <p class="section-kicker">{{ __('Release Publishing') }}</p>
                    <h2 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Add Package') }}</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('Create a new release entry with version metadata, distribution link and security references.') }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('packages.store') }}" class="space-y-4">
                @csrf

            <!-- Version -->
            <div class="mb-4">
                <label for="version"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Version') }}</label>
                <input type="text" name="version" id="version" value="{{ old('version') }}" class="form-input form-pill"
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
                            <select name="release_channel" id="release_channel" class="form-select form-pill form-select-enhanced">
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
                <input type="url" name="download_url" id="download_url" value="{{ old('download_url') }}" class="form-input form-pill"
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
                <textarea name="virus_detection_url" id="virus_detection_url" rows="3" class="form-textarea"
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
                <textarea name="changelog" id="changelog" rows="6" class="form-textarea"
                    placeholder="{{ __('Describe the changes in this version...') }}">{{ old('changelog') }}</textarea>
                @error('changelog')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-3 border-t border-zinc-200/70 pt-6 dark:border-zinc-700/70">
                    <a href="{{ route('packages.index') }}" class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-secondary">
                        {{ __('Add Package') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-sidebar-layout>
