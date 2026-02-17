<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Create License') }}
    </x-slot>

    <div
        class="bg-white/80 dark:bg-cool-800/80 backdrop-blur-sm rounded-xl shadow-sm border border-cool-200/50 dark:border-cool-700/50 p-6">
        <form method="POST" action="{{ route('licenses.store') }}">
            @csrf

            <!-- License Key -->
            <div class="mb-4">
                <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">License
                    Key</label>
                <input type="text" name="key" id="key" value="{{ old('key') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                    placeholder="Leave blank to auto-generate (Format: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX)">
                @error('key')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Account -->
            <div class="mb-4">
                <label for="used_by" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assign to
                    Account</label>
                <select name="used_by" id="used_by"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    <option value="">Unassigned</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('used_by') == $account->id ? 'selected' : '' }}>
                            {{ $account->username }} ({{ $account->email }})
                        </option>
                    @endforeach
                </select>
                @error('used_by')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Privilege -->
            <div class="mb-4">
                <label for="privilege" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Privilege
                    Level</label>
                <select name="privilege" id="privilege"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    @foreach ($privilegeOptions as $value => $label)
                        <option value="{{ $value }}" {{ old('privilege', 0) == $value ? 'selected' : '' }}>
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
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Initial
                    Status</label>
                <select name="status" id="status"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ old('status', 0) == $value ? 'selected' : '' }}>
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
                <label for="expires_at"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiration
                    Date</label>
                <input type="date" name="expires_at" id="expires_at"
                    value="{{ old('expires_at', now()->addYear()->format('Y-m-d')) }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                @error('expires_at')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label for="notes"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea name="notes" id="notes" rows="3"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('licenses.index') }}"
                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    Create License
                </button>
            </div>
        </form>
    </div>

</x-app-sidebar-layout>
