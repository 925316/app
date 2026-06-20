<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Add Package') }}
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="card-shell">
            <div class="app-toolbar mb-6">
                <div>
                    <p class="section-kicker">{{ __('Release Publishing') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Add Package') }}</h2>
                    <p class="app-toolbar-subtitle">
                        {{ __('Create a new release entry with version metadata, distribution link and security references.') }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('packages.store') }}" class="space-y-4">
                @csrf

            <!-- Version -->
            <div class="mb-4">
                <label for="version" class="form-label">{{ __('Version') }}</label>
                <input type="text" name="version" id="version" value="{{ old('version') }}" class="form-input"
                    placeholder="{{ __('e.g., 1.0.0 (semantic versioning)') }}">
                @error('version')
                    <p class="form-error">{{ $message }}</p>
                @enderror
                <p class="form-note text-xs">
                    {{ __('Please use semantic versioning format (e.g., 1.0.0, 2.3.1-beta.1)') }}
                </p>
            </div>

            <!-- Release Channel -->
            <div class="mb-4">
                <label for="release_channel" class="form-label">{{ __('Release Channel') }}</label>
                <select name="release_channel" id="release_channel" class="form-select">
                    <option value="stable" {{ old('release_channel') === 'stable' ? 'selected' : '' }}>{{ __('Stable') }}</option>
                    <option value="dev" {{ old('release_channel') === 'dev' ? 'selected' : '' }}>{{ __('Development') }}</option>
                </select>
                @error('release_channel')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Download URL -->
            <div class="mb-4">
                <label for="download_url" class="form-label">{{ __('Download URL') }}</label>
                <input type="url" name="download_url" id="download_url" value="{{ old('download_url') }}" class="form-input"
                    placeholder="{{ __('https://example.com/downloads/package.zip') }}">
                @error('download_url')
                    <p class="form-error">{{ $message }}</p>
                @enderror
                <p class="form-note text-xs">
                    {{ __('Enter the direct download URL for the package file.') }}
                </p>
            </div>

            <!-- Virus Detection Link -->
            <div class="mb-4">
                <label for="virus_detection_url" class="form-label">{{ __('Virus Detection Link (Optional)') }}</label>
                <textarea name="virus_detection_url" id="virus_detection_url" rows="3" class="form-textarea"
                    placeholder="{{ __('https://www.virustotal.com/gui/file/abc123') }}&#10;{{ __('https://www.hybrid-analysis.com/sample/def456') }}&#10;{{ __('https://www.joesandbox.com/analysis/789') }}">{{ old('virus_detection_url') }}</textarea>
                @error('virus_detection_url')
                    <p class="form-error">{{ $message }}</p>
                @enderror
                <p class="form-note text-xs">
                    {{ __('Optional: Add virus scanning or hash verification links (one per line). These will be displayed as clickable links for users to verify package safety.') }}
                </p>
            </div>

            <!-- Changelog -->
            <div class="mb-4">
                <label for="changelog" class="form-label">{{ __('Changelog') }}</label>
                <textarea name="changelog" id="changelog" rows="6" class="form-textarea"
                    placeholder="{{ __('Describe the changes in this version...') }}">{{ old('changelog') }}</textarea>
                @error('changelog')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

                <!-- Form Actions -->
                <div class="form-divider flex justify-end gap-3">
                    <x-secondary-button tag="a" href="{{ route('packages.index') }}">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Add Package') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

</x-app-sidebar-layout>
