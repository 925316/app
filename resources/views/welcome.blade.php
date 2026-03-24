<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

    <!-- Early theme detection to prevent FOUC -->
    @include('components.theme-init-script')

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
        </style>
    @endif
</head>

<body class="min-h-screen overscroll-y-none bg-[rgb(var(--color-surface-page))] text-[rgb(var(--color-text-primary))] antialiased"
    style="background: rgb(var(--color-surface-page));">
    <div class="relative isolate min-h-screen overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute inset-0 opacity-[0.55] dark:opacity-[0.05]"
                style="background-image: linear-gradient(rgb(var(--color-border-subtle) / 0.48) 1px, transparent 1px), linear-gradient(90deg, rgb(var(--color-border-subtle) / 0.48) 1px, transparent 1px); background-size: 42px 42px;"></div>
            <div class="absolute inset-0"
                style="background-image: radial-gradient(70% 55% at 50% 0%, rgb(var(--color-border-strong) / 0.2) 0%, transparent 70%);"></div>
        </div>

        @auth
            <div class="fixed right-5 top-5 z-50">
                <button x-data="{
                    dark: document.documentElement.classList.contains('dark'),
                    toggle() {
                        this.dark = !this.dark;
                        document.documentElement.classList.toggle('dark');
                        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                    }
                }" x-cloak @click="toggle"
                    class="inline-flex items-center gap-2 rounded-full border border-[rgb(var(--color-border-subtle))/0.8] bg-[rgb(var(--color-surface-card))/0.78] px-3 py-2 text-sm font-medium text-[rgb(var(--color-text-secondary))] shadow-md backdrop-blur-xl transition hover:-translate-y-0.5 hover:border-[rgb(var(--color-brand))/0.45] hover:bg-[rgb(var(--color-surface-card))] dark:bg-[rgb(var(--color-surface-card))/0.75] dark:hover:bg-[rgb(var(--color-surface-card-muted))/0.72]"
                    aria-label="{{ __('Toggle dark mode') }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <span>{{ __('Theme') }}</span>
                </button>
            </div>
        @endauth

        <main class="relative mx-auto flex min-h-screen w-full max-w-7xl flex-col justify-between px-6 pb-8 pt-8 sm:px-10 lg:px-14">
            <header class="flex items-center justify-between border-b border-[rgb(var(--color-border-subtle))/0.55] pb-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-[2rem] text-white shadow-lg"
                        style="background: var(--gradient-brand);">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.32em] text-[rgb(var(--color-text-muted))]">{{ __('Software Command Center') }}</p>
                        <p class="text-lg font-semibold">{{ config('app.name') }}</p>
                    </div>
                </div>

                @if (Route::has('login'))
                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="inline-flex items-center gap-2 rounded-full border border-[rgb(var(--color-brand))/0.38] bg-[rgb(var(--color-brand))] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[rgb(var(--color-brand))/0.35] transition hover:-translate-y-0.5 hover:bg-[rgb(var(--color-brand-hover))] dark:border-[rgb(var(--color-brand))/0.48] dark:bg-[rgb(var(--color-brand-soft))/0.95] dark:text-[rgb(var(--color-text-primary))] dark:shadow-none dark:hover:bg-[rgb(var(--color-brand-soft))]">
                                {{ __('Go to Dashboard') }}
                                <span aria-hidden="true">→</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center rounded-full border border-[rgb(var(--color-border-subtle))] bg-[rgb(var(--color-surface-card))/0.82] px-5 py-2.5 text-sm font-semibold text-[rgb(var(--color-text-secondary))] backdrop-blur transition hover:-translate-y-0.5 hover:border-[rgb(var(--color-brand))/0.45] hover:text-[rgb(var(--color-brand-hover))] dark:bg-[rgb(var(--color-surface-card))/0.72] dark:hover:border-[rgb(var(--color-brand))/0.5] dark:hover:text-[rgb(var(--color-text-primary))]">
                                {{ __('Sign In') }}
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                    class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5"
                                    style="background: var(--gradient-brand);">
                                    {{ __('Get Started') }}
                                    <span aria-hidden="true">→</span>
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </header>

            <section class="grid flex-1 items-center gap-12 py-12 lg:grid-cols-[1.1fr,0.9fr]">
                <div class="space-y-8">
                    <p class="inline-flex items-center rounded-full border border-[rgb(var(--color-brand))/0.32] bg-[rgb(var(--color-surface-card))/0.76] px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-[rgb(var(--color-brand-hover))] backdrop-blur dark:border-[rgb(var(--color-brand))/0.42] dark:bg-[rgb(var(--color-brand-soft))/0.62] dark:text-[rgb(var(--color-brand-hover))]">
                        {{ __('Release control, but make it elegant') }}
                    </p>

                    <div class="space-y-6">
                        <h1 class="text-4xl font-semibold leading-tight sm:text-5xl lg:text-6xl">
                            {{ __('Run licenses, devices, and package delivery from one decisive surface.') }}
                        </h1>
                        <p class="max-w-2xl text-base leading-relaxed text-[rgb(var(--color-text-secondary))] sm:text-lg">
                            {{ __('Built for teams that want strict control with less operational drag. Move from tracking to action in minutes, not meetings.') }}
                        </p>
                    </div>

                    <dl class="grid max-w-2xl grid-cols-1 gap-6 border-y border-[rgb(var(--color-border-subtle))/0.7] py-6 sm:grid-cols-3">
                        <div>
                            <dt class="text-xs uppercase tracking-[0.2em] text-[rgb(var(--color-text-muted))]">{{ __('License Ops') }}</dt>
                            <dd class="mt-2 text-2xl font-semibold">{{ __('Realtime') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.2em] text-[rgb(var(--color-text-muted))]">{{ __('Device State') }}</dt>
                            <dd class="mt-2 text-2xl font-semibold">{{ __('Traceable') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.2em] text-[rgb(var(--color-text-muted))]">{{ __('Package Rollout') }}</dt>
                            <dd class="mt-2 text-2xl font-semibold">{{ __('Versioned') }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="relative">
                    <div class="absolute -inset-8 rounded-[2.5rem] bg-zinc-400/20 blur-3xl dark:bg-zinc-100/6"></div>
                    <div class="relative overflow-hidden rounded-[2rem] border border-[rgb(var(--color-border-subtle))/0.7] bg-[rgb(var(--color-surface-card))/0.88] p-8 shadow-2xl backdrop-blur-xl dark:bg-[rgb(var(--color-surface-card))/0.88]">
                        <p class="text-xs uppercase tracking-[0.24em] text-[rgb(var(--color-text-muted))]">{{ __('Operational Snapshot') }}</p>

                        <div class="mt-6 space-y-5">
                            <div class="space-y-2">
                                <div class="flex items-end justify-between text-sm text-[rgb(var(--color-text-secondary))]">
                                    <span>{{ __('License health') }}</span>
                                    <span class="font-semibold text-emerald-500">98.4%</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-200/80 dark:bg-slate-700/70">
                                    <div class="h-full rounded-full bg-emerald-500" style="width: 98.4%"></div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-end justify-between text-sm text-[rgb(var(--color-text-secondary))]">
                                    <span>{{ __('Online devices') }}</span>
                                    <span class="font-semibold text-[rgb(var(--color-brand))]">1,248</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-200/80 dark:bg-slate-700/70">
                                    <div class="h-full rounded-full bg-[rgb(var(--color-brand))]" style="width: 84%"></div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-end justify-between text-sm text-[rgb(var(--color-text-secondary))]">
                                    <span>{{ __('Deployment velocity') }}</span>
                                    <span class="font-semibold text-[rgb(var(--color-brand))]">+32%</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-200/80 dark:bg-slate-700/70">
                                    <div class="h-full rounded-full bg-[rgb(var(--color-brand))]" style="width: 72%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 grid gap-4 border-t border-[rgb(var(--color-border-subtle))/0.7] pt-5 text-sm text-[rgb(var(--color-text-secondary))] sm:grid-cols-2">
                            <p>{{ __('Ship updates with controlled access and full audit visibility.') }}</p>
                            <p>{{ __('Make ownership, expiration, and distribution status obvious at a glance.') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <footer class="border-t border-[rgb(var(--color-border-subtle))/0.6] pt-6 text-sm text-[rgb(var(--color-text-muted))]">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }} · {{ __('Built with Laravel & Tailwind CSS.') }}</p>
            </footer>
        </main>
    </div>
</body>

</html>
