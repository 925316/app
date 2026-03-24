<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#fafafa">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Early theme detection to prevent FOUC -->
    @include('components.theme-init-script')

    <script>
        (function () {
            const metaThemeColor = document.querySelector('meta[name="theme-color"]');
            if (!metaThemeColor) {
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            metaThemeColor.setAttribute('content', isDark ? '#000000' : '#fafafa');
        })();
    </script>

    <!-- x-cloak styles to hide elements until Alpine.js initializes -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- Initialize Alpine store for sidebar state -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', {
                open: localStorage.getItem('sidebar-collapsed') === 'true' ? false : true,
                toggle() {
                    this.open = !this.open;
                    localStorage.setItem('sidebar-collapsed', !this.open);
                }
            });
        });
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="app-shell-page min-h-screen overflow-x-hidden overscroll-y-none font-sans antialiased">
    <div class="flex min-h-screen w-full overflow-x-hidden" x-data="{
        mobileSidebarOpen: false,
        isDesktop: false,
        mobileSidebarTrigger: null,
        openMobileSidebar(triggerElement = null) {
            this.mobileSidebarTrigger = triggerElement;
            this.mobileSidebarOpen = true;

            this.$nextTick(() => {
                this.$refs.mobileSidebarCloseButton?.focus();
            });
        },
        closeMobileSidebar() {
            this.mobileSidebarOpen = false;

            if (!this.isDesktop) {
                this.$nextTick(() => {
                    this.mobileSidebarTrigger?.focus();
                });
            }
        }
    }" x-init="
        const desktopMediaQuery = window.matchMedia('(min-width: 1024px)');
        isDesktop = desktopMediaQuery.matches;

        const handleDesktopViewport = (event) => {
            isDesktop = event.matches;

            if (event.matches) {
                closeMobileSidebar();
                document.body.classList.remove('overflow-y-hidden');
            }
        };

        if (desktopMediaQuery.addEventListener) {
            desktopMediaQuery.addEventListener('change', handleDesktopViewport);
        } else if (desktopMediaQuery.addListener) {
            desktopMediaQuery.addListener(handleDesktopViewport);
        }

        $watch('mobileSidebarOpen', (isOpen) => {
            document.body.classList.toggle('overflow-y-hidden', isOpen && !isDesktop);
        });
    " @keydown.escape.window="closeMobileSidebar()">
        <!-- Sidebar Navigation -->
        @include('layouts.sidebar')

        <!-- Mobile Sidebar Overlay -->
        <div x-cloak x-show="mobileSidebarOpen && !isDesktop" x-transition.opacity @click="closeMobileSidebar()" class="sidebar-overlay"
            aria-hidden="true"></div>

        <!-- Main Content Area -->
        <div class="relative z-0 flex flex-col w-full min-w-0 transition-all duration-300 ml-0"
            :class="{ 'lg:ml-64 lg:w-[calc(100%-16rem)]': $store.sidebar.open, 'lg:ml-16 lg:w-[calc(100%-4rem)]': !$store.sidebar.open }"
            :inert="mobileSidebarOpen && !isDesktop" :aria-hidden="(mobileSidebarOpen && !isDesktop).toString()">
            <!-- Top Header -->
            <header class="app-shell-header">
                <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <!-- Page Title -->
                    <div class="flex items-center gap-3">
                        <button type="button" class="inline-flex items-center justify-center rounded-2xl border border-[rgb(var(--color-border-subtle))] bg-[rgb(var(--color-surface-card))/0.8] p-2 text-[rgb(var(--color-text-secondary))] shadow-sm transition duration-150 hover:border-white/20 hover:bg-white/10 hover:text-[rgb(var(--color-text-primary))] focus:outline-none focus:ring-2 focus:ring-white/30 lg:hidden"
                            @click="openMobileSidebar($el)" :aria-expanded="mobileSidebarOpen.toString()"
                            aria-controls="app-sidebar" aria-label="{{ __('Open navigation menu') }}">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div>
                            <h1 class="text-2xl font-bold text-[rgb(var(--color-text-primary))]">
                                @isset($header)
                                    {{ $header }}
                                @else
                                    {{ __('Dashboard') }}
                                @endisset
                            </h1>
                            @isset($subheader)
                                <p class="mt-1 text-sm text-[rgb(var(--color-text-muted))]">
                                    {{ $subheader }}
                                </p>
                            @endisset
                        </div>
                    </div>

                    <!-- Additional Header Actions (if any) -->
                    <div class="app-toolbar-actions"></div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 min-w-0 overflow-y-auto overflow-x-hidden">
                <div class="app-shell-content-wrap">
                    <div class="app-shell-content mx-auto w-full sm:px-6 lg:px-8">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="app-shell-footer py-4 px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-[rgb(var(--color-text-muted))]">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. {{ __('All rights reserved.') }}
                </div>
            </footer>
        </div>
    </div>
</body>

</html>
