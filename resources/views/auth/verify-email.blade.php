<x-guest-layout>
    <div class="space-y-8" data-page="auth-verify-email">
        <x-auth-header title="{{ __('Verify Your Email') }}" :subtitle="__('Please verify your email address to get started')" />

        <x-auth-card>
            <div class="space-y-6" data-auth-form="verify-email">
                <div class="card-shell-muted form-note text-sm">
                    {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                </div>

                <x-auth-session-status :status="session('status') === 'verification-link-sent'
                    ? __('A new verification link has been sent to the email address you provided during registration.')
                    : null" />

                <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                    @csrf

                    <x-primary-button class="w-full justify-center gap-2 py-3">
                        <x-icon name="mail" class="h-4 w-4" />
                        {{ __('Resend Verification Email') }}
                    </x-primary-button>
                </form>

                <div class="relative" aria-hidden="true">
                    <div class="absolute inset-0 flex items-center">
                        <span class="app-shell-divider w-full border-t"></span>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase tracking-[0.2em]">
                        <span class="app-shell-chip px-3">{{ __('Or') }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf

                    <x-secondary-button type="submit" class="w-full justify-center gap-2 py-3">
                        <x-icon name="logout" class="h-4 w-4" />
                        {{ __('Log Out') }}
                    </x-secondary-button>
                </form>
            </div>
        </x-auth-card>
    </div>
</x-guest-layout>
