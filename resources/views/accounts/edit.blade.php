<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Edit Account') }}
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="card-shell">
            <div class="app-toolbar mb-6">
                <div>
                    <p class="section-kicker">{{ __('Account Maintenance') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Edit Account') }}</h2>
                    <p class="app-toolbar-subtitle">
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
                    <label for="username" class="form-label">{{ __('Username') }}</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $account->username) }}" required class="form-input">
                    @error('username')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="form-label">{{ __('Email Address') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $account->email) }}" required class="form-input">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="form-label">{{ __('New Password (Optional)') }}</label>
                    <input type="password" name="password" id="password" class="form-input">
                    <p class="form-note text-xs">{{ __('Leave blank to keep current password. Password must contain at least one lowercase letter, one uppercase letter, and one number.') }}</p>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-input">
                </div>
            </div>

                <div class="form-divider flex justify-end gap-3">
                    <x-secondary-button tag="a" href="{{ route('accounts.show', $account) }}">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Update Account') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

</x-app-sidebar-layout>
