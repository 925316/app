@php use App\Enums\LicenseStatus; @endphp
<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Edit License') }}
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div
            class="bg-white/80 dark:bg-cool-800/80 backdrop-blur-sm rounded-xl shadow-sm border border-cool-200/50 dark:border-cool-700/50 p-6">
            <form method="POST" action="{{ route('licenses.update', $license) }}">
            @csrf
            @method('PUT')

            <!-- License Key (read-only) -->
            <div class="mb-4">
                <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('License Key') }}</label>
                <input type="text" name="key" id="key" value="{{ old('key', $license->key) }}" readonly
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 cursor-not-allowed">
            </div>

            <!-- Account -->
            <div class="mb-4">
                <label for="used_by" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Assign to Account') }}</label>
                <select name="used_by" id="used_by"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
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
                <select name="privilege" id="privilege"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
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
                <select name="status" id="status"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
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
                <input type="date" name="expires_at" id="expires_at"
                    value="{{ old('expires_at', $license->expires_at->format('Y-m-d')) }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                @error('expires_at')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label for="notes"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Notes') }}</label>
                <textarea name="notes" id="notes" rows="3"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ old('notes', $license->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('licenses.show', $license) }}"
                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    {{ __('Update License') }}
                </button>
            </div>
            </form>
        </div>
    </div>

</x-app-sidebar-layout>
