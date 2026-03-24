<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-xl rounded-[2rem] border border-zinc-200/70 bg-white/80 p-8 text-center shadow-lg backdrop-blur dark:border-zinc-700/60 dark:bg-zinc-900/60">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-600 dark:bg-zinc-800/60 dark:text-zinc-200">
                <x-icon name="lock" class="h-7 w-7" />
            </div>
            <h1 class="mt-6 text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Access Denied') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('You do not have permission to view this page. Please contact an administrator.') }}</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-zinc-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700">
                        {{ __('Back to Dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-zinc-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700">
                        {{ __('Back to Login') }}
                    </a>
                @endauth
                <a href="{{ url('/') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                    {{ __('Back to Home') }}
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
