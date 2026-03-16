<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-xl rounded-2xl border border-cool-200/70 bg-white/80 p-8 text-center shadow-lg backdrop-blur dark:border-cool-700/60 dark:bg-cool-900/60">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-200">
                <x-icon name="warning" class="h-7 w-7" />
            </div>
            <h1 class="mt-6 text-2xl font-semibold text-gray-900 dark:text-white">Service Temporarily Unavailable</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">The system encountered an error. Please try again later.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-cool-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-cool-700">
                        Back to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-cool-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-cool-700">
                        Back to Login
                    </a>
                @endauth
                <a href="{{ url('/') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-cool-200 px-4 py-2 text-sm font-medium text-cool-700 transition hover:bg-cool-50 dark:border-cool-700 dark:text-cool-200 dark:hover:bg-cool-800">
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
