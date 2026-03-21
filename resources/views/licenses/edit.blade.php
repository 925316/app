@php use App\Enums\LicenseStatus; @endphp
<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Edit License') }}
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="card-shell">
            <div class="app-toolbar mb-6">
                <div>
                    <p class="section-kicker">{{ __('License Maintenance') }}</p>
                    <h2 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit License') }}</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('Adjust assignment, privilege and expiration while respecting state transition rules.') }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('licenses.update', $license) }}" class="space-y-4">
                @csrf
                @method('PUT')

            <!-- License Key (read-only) -->
            <div class="mb-4">
                <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('License Key') }}</label>
                <input type="text" name="key" id="key" value="{{ old('key', $license->key) }}" readonly
                    class="form-input bg-gray-100 dark:bg-gray-700 cursor-not-allowed">
            </div>

            <!-- Account -->
            <div class="mb-4">
                <label for="used_by" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Assign to Account') }}</label>
                <select name="used_by" id="used_by" class="form-select"
                    {{ $license->status !== LicenseStatus::UNUSED ? 'disabled' : '' }}>
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}"
                            {{ old('used_by', $license->used_by) == $account->id ? 'selected' : '' }}>
                            {{ $account->username }} ({{ $account->email }})
                        </option>
                    @endforeach
                </select>
                @if ($license->status !== LicenseStatus::UNUSED)
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Account assignment can only be changed for unused licenses.') }}
                    </p>
                @endif
                @error('used_by')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Privilege -->
            <div class="mb-4">
                <label for="privilege" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Privilege Level') }}</label>
                <select name="privilege" id="privilege" class="form-select">
                    @foreach ($privilegeOptions as $value => $label)
                        <option value="{{ $value }}"
                            {{ old('privilege', $license->privilege->value) == $value ? 'selected' : '' }}>
                            {{ ucfirst($label) }}
                        </option>
                    @endforeach
                </select>
                @error('privilege')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-4">
                <label for="status"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Status') }}</label>
                <select name="status" id="status" class="form-select">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}"
                            {{ old('status', $license->status->value) == $value ? 'selected' : '' }}>
                            {{ ucfirst($label) }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Expiration Date -->
            <div class="mb-4">
                <label for="expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Expiration Date') }}</label>
                <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at', $license->expires_at->format('Y-m-d')) }}" class="form-input">
                @error('expires_at')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label for="notes"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Notes') }}</label>
                <textarea name="notes" id="notes" rows="3" class="form-textarea">{{ old('notes', $license->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-3 border-t border-cool-200/70 pt-6 dark:border-cool-700/70">
                    <a href="{{ route('licenses.show', $license) }}" class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-blue">
                        {{ __('Update License') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-sidebar-layout>
