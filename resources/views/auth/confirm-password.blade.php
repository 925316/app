<x-guest-layout>
    <div class="space-y-8" data-page="auth-confirm-password">
        <x-auth-header title="{{ __('Confirm Password') }}" :subtitle="__('Please confirm your password to continue')" />

        <x-auth-card>
            <form method="POST" action="{{ route('password.confirm') }}" class="form-stack" data-auth-form="confirm-password">
                @csrf

                <div class="card-shell-muted form-note text-sm">
                    {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                </div>

                <div class="form-field">
                    <x-input-label for="password" :value="__('Password')" class="form-label" />
                    <x-input-with-icon id="password" name="password" type="password" required autocomplete="current-password"
                        placeholder="••••••••" icon="lock" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <x-primary-button class="w-full justify-center gap-2 py-3">
                    <x-icon name="check" class="h-4 w-4" />
                    {{ __('Confirm Password') }}
                </x-primary-button>
            </form>
        </x-auth-card>
    </div>
</x-guest-layout>
