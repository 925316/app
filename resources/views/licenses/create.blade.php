<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Create License') }}
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="card-shell">
            <div class="app-toolbar mb-6">
                <div>
                    <p class="section-kicker">{{ __('License Issuance') }}</p>
                    <h2 class="card-form-title mt-1 text-2xl font-bold">{{ __('Create License') }}</h2>
                    <p class="card-form-copy mt-2 text-sm">
                        {{ __('Generate or register a license key, assign privilege level, and set lifecycle defaults.') }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('licenses.store') }}" class="space-y-4">
                @csrf

            <!-- License Key -->
            <div class="mb-4">
                <label for="key" class="form-label mb-1">{{ __('License Key') }}</label>
                <input type="text" name="key" id="key" value="{{ old('key') }}" class="form-input"
                    placeholder="{{ __('Leave blank to auto-generate (Format: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX)') }}">
                @error('key')
                    <p class="form-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Account -->
            <div class="mb-4">
                <label for="used_by" class="form-label mb-1">{{ __('Assign to Account') }}</label>
                <select name="used_by" id="used_by" class="form-select">
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('used_by') == $account->id ? 'selected' : '' }}>
                            {{ $account->username }} ({{ $account->email }})
                        </option>
                    @endforeach
                </select>
                @error('used_by')
                    <p class="form-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Privilege -->
            <div class="mb-4">
                <label for="privilege" class="form-label mb-1">{{ __('Privilege Level') }}</label>
                <select name="privilege" id="privilege" class="form-select">
                    @foreach ($privilegeOptions as $value => $label)
                        <option value="{{ $value }}" {{ old('privilege', 0) == $value ? 'selected' : '' }}>
                            {{ ucfirst($label) }}
                        </option>
                    @endforeach
                </select>
                @error('privilege')
                    <p class="form-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-4">
                <label for="status" class="form-label mb-1">{{ __('Initial Status') }}</label>
                <select name="status" id="status" class="form-select">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ old('status', 0) == $value ? 'selected' : '' }}>
                            {{ ucfirst($label) }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <p class="form-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Expiration Date -->
            <div class="mb-4">
                <label for="expires_at" class="form-label mb-1">{{ __('Expiration Date') }}</label>
                <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at', now()->addYear()->format('Y-m-d')) }}" class="form-input">
                @error('expires_at')
                    <p class="form-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label for="notes" class="form-label mb-1">{{ __('Notes') }}</label>
                <textarea name="notes" id="notes" rows="3" class="form-textarea">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="form-error mt-1">{{ $message }}</p>
                @enderror
            </div>

                <!-- Form Actions -->
                <div class="app-shell-divider flex justify-end gap-3 border-t pt-6">
                    <x-secondary-button tag="a" href="{{ route('licenses.index') }}">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Create License') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

</x-app-sidebar-layout>
