<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="card-glass error-card w-full max-w-xl p-8 text-center">
            <div class="error-card-icon mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[rgb(var(--shell-accent)/0.16)] text-[rgb(var(--shell-accent))]">
                <x-icon name="warning" class="h-7 w-7" />
            </div>
            <h1 class="app-shell-heading mt-6 text-2xl font-semibold">{{ __('Page Not Found') }}</h1>
            <p class="app-shell-prose mt-2 text-sm">{{ __('The page you are looking for might have been removed or moved.') }}</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        {{ __('Back to Dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        {{ __('Back to Login') }}
                    </a>
                @endauth
                <a href="{{ url('/') }}" class="btn btn-secondary">
                    {{ __('Back to Home') }}
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
