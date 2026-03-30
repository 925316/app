<x-guest-layout>
    <div class="space-y-8" data-page="auth-login">
        <x-auth-header title="{{ __('Welcome Back') }}" :subtitle="__('Sign in to your account to continue')" />

        <x-auth-card :showStatus="true">
            <form method="POST" action="{{ route('login') }}" class="space-y-6" data-auth-form="login">
                @csrf

                <div class="space-y-2">
                    <x-input-label for="email" :value="__('Email Address')" class="form-label" />
                    <x-input-with-icon id="email" name="email" type="email" :value="old('email')" required autofocus
                        autocomplete="username" :placeholder="__('Email address')" icon="mail" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="password" :value="__('Password')" class="form-label" />
                    <x-input-with-icon id="password" name="password" type="password" required
                        autocomplete="current-password" :placeholder="__('Password')" icon="lock" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <label for="remember_me" class="form-note flex items-center gap-3 text-sm">
                        <input id="remember_me" type="checkbox" class="form-checkbox" name="remember">
                        <span>{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="form-link text-sm font-medium"
                            href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <x-primary-button class="w-full justify-center gap-2 py-3">
                    <x-icon name="login" class="h-4 w-4" />
                    {{ __('Sign in') }}
                </x-primary-button>
            </form>

            <div class="mt-6 text-center" data-auth-footer>
                <p class="form-note text-sm">
                    {{ __("Don't have an account?") }}
                    <a href="{{ route('register') }}" class="form-link font-medium">
                        {{ __('Sign up for free') }}
                    </a>
                </p>
            </div>
        </x-auth-card>
    </div>
</x-guest-layout>
