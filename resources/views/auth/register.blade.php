<x-guest-layout>
    <div class="space-y-8" data-page="auth-register">
        <x-auth-header title="{{ __('Create Account') }}" :subtitle="__('Join us today and get started')" />

        <x-auth-card>
            <form method="POST" action="{{ route('register') }}" class="form-stack" data-auth-form="register">
                @csrf

                <div class="form-field">
                    <x-input-label for="username" :value="__('Username')" class="form-label" />
                    <x-input-with-icon id="username" name="username" type="text" :value="old('username')" required autofocus
                        autocomplete="username" :placeholder="__('Username')" icon="user" />
                    <x-input-error :messages="$errors->get('username')" class="mt-2" />
                </div>

                <div class="form-field">
                    <x-input-label for="email" :value="__('Email Address')" class="form-label" />
                    <x-input-with-icon id="email" name="email" type="email" :value="old('email')" required
                        autocomplete="username" :placeholder="__('Email address')" icon="mail" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="form-field">
                    <x-input-label for="password" :value="__('Password')" class="form-label" />
                    <x-input-with-icon id="password" name="password" type="password" required
                        autocomplete="new-password" :placeholder="__('Create a password')" icon="lock" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="form-field">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="form-label" />
                    <x-input-with-icon id="password_confirmation" name="password_confirmation" type="password" required
                        autocomplete="new-password" :placeholder="__('Confirm password')" icon="lock" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <x-primary-button class="w-full justify-center gap-2 py-3">
                    <x-icon name="plus" class="h-4 w-4" />
                    {{ __('Create Account') }}
                </x-primary-button>
            </form>

            <div class="auth-card-shell-footer text-center" data-auth-footer>
                <p class="form-note text-sm">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}" class="form-link font-medium">
                        {{ __('Sign in here') }}
                    </a>
                </p>
            </div>
        </x-auth-card>
    </div>
</x-guest-layout>
