<x-guest-layout>
    <div class="space-y-8" data-page="auth-reset-password">
        <x-auth-header title="{{ __('Reset Password') }}" :subtitle="__('Set a new password for your account')" />

        <x-auth-card>
            <form method="POST" action="{{ route('password.store') }}" class="form-stack" data-auth-form="reset-password">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}" />

                <div class="form-field">
                    <x-input-label for="email" :value="__('Email Address')" class="form-label" />
                    <x-input-with-icon id="email" name="email" type="email" :value="old('email', $request->email)" required autofocus
                        autocomplete="username" :placeholder="__('your@email.com')" icon="mail" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="form-field">
                    <x-input-label for="password" :value="__('New Password')" class="form-label" />
                    <x-input-with-icon id="password" name="password" type="password" required autocomplete="new-password"
                        placeholder="••••••••" icon="lock" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="form-field">
                    <x-input-label for="password_confirmation" :value="__('Confirm New Password')" class="form-label" />
                    <x-input-with-icon id="password_confirmation" name="password_confirmation" type="password" required
                        autocomplete="new-password" placeholder="••••••••" icon="lock" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <x-primary-button class="w-full justify-center gap-2 py-3">
                    <x-icon name="reset" class="h-4 w-4" />
                    {{ __('Reset Password') }}
                </x-primary-button>
            </form>

            <div class="auth-card-shell-footer text-center" data-auth-footer>
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
