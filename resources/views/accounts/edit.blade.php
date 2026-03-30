<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Edit Account') }}
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="card-shell">
            <div class="app-toolbar mb-6">
                <div>
                    <p class="section-kicker">{{ __('Account Maintenance') }}</p>
                    <h2 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit Account') }}</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('Update identity and credentials while preserving access history and related records.') }}
                    </p>
                </div>
                <x-secondary-button tag="a" href="{{ route('accounts.show', $account) }}">{{ __('Back to Account') }}</x-secondary-button>
            </div>

            <form method="POST" action="{{ route('accounts.update', $account) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="username"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Username') }}</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $account->username) }}" required class="form-input">
                    @error('username')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Email Address') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $account->email) }}" required class="form-input">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('New Password (Optional)') }}</label>
                    <input type="password" name="password" id="password" class="form-input">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Leave blank to keep current password. Password must contain at least one lowercase letter, one uppercase letter, and one number.') }}</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Confirm New Password') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-input">
                </div>
            </div>

                <div class="flex justify-end gap-3 border-t border-cool-200/70 pt-6 dark:border-cool-700/70">
                    <x-secondary-button tag="a" href="{{ route('accounts.show', $account) }}">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Update Account') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

</x-app-sidebar-layout>
