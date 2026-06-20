<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Create Account') }}
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="card-shell">
            <div class="app-toolbar mb-6">
                <div>
                    <p class="section-kicker">{{ __('Account Provisioning') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Create New Account') }}</h2>
                    <p class="app-toolbar-subtitle">
                        {{ __('Set up user credentials and optionally pre-verify email status for onboarding.') }}
                    </p>
                </div>
                <x-secondary-button tag="a" href="{{ route('accounts.index') }}">{{ __('Back to Accounts') }}</x-secondary-button>
            </div>

            <form method="POST" action="{{ route('accounts.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="username" class="form-label">{{ __('Username') }}</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required class="form-input">
                    @error('username')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="form-label">{{ __('Email Address') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required class="form-input">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input type="password" name="password" id="password" required class="form-input">
                    <p class="form-note text-xs">{{ __('Password must contain at least one lowercase letter, one uppercase letter, and one number.') }}</p>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required class="form-input">
                </div>

                <div class="md:col-span-2 card-shell-muted">
                    <label class="flex items-center">
                        <input type="checkbox" name="email_verified" value="1" class="form-checkbox">
                        <span class="form-note ml-2 text-sm">{{ __('Mark email as verified') }}</span>
                    </label>
                    <p class="form-note mt-2 text-xs">
                        {{ __('Use this only when migrating trusted users or manually approved accounts.') }}
                    </p>
                </div>
            </div>

                <div class="form-divider flex justify-end gap-3">
                    <x-secondary-button tag="a" href="{{ route('accounts.index') }}">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Create Account') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

</x-app-sidebar-layout>
