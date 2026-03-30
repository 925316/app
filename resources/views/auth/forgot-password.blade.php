<x-guest-layout>
    <div class="space-y-8" data-page="auth-forgot-password">
        <x-auth-header title="{{ __('Forgot Password') }}" :subtitle="__('Reset your password in a few steps')" />

        <x-auth-card :showStatus="true">
            <form method="POST" action="{{ route('password.email') }}" class="space-y-6" data-auth-form="forgot-password">
                @csrf

                <div class="card-shell-muted form-note text-sm">
                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                </div>

                <div class="space-y-2">
                    <x-input-label for="email" :value="__('Email Address')" class="form-label" />
                    <x-input-with-icon id="email" name="email" type="email" :value="old('email')" required autofocus
                        :placeholder="__('your@email.com')" icon="mail" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <x-primary-button class="w-full justify-center gap-2 py-3">
                    <x-icon name="mail" class="h-4 w-4" />
                    {{ __('Send Reset Link') }}
                </x-primary-button>
            </form>

            <div class="mt-6 text-center" data-auth-footer>
                <p class="form-note text-sm">
                    {{ __('Remember your password?') }}
                    <a href="{{ route('login') }}" class="form-link font-medium">
                        {{ __('Sign in here') }}
                    </a>
                </p>
            </div>
        </x-auth-card>
    </div>
</x-guest-layout>
