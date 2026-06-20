<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|atkinson-hyperlegible:400,700&display=swap" rel="stylesheet" />

    <!-- Early theme detection to prevent FOUC -->
    @include('components.theme-init-script')

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

<body class="app-shell-page app-shell-grid shell-atelier shell-atelier--sidebar font-sans antialiased min-h-screen overflow-x-hidden transition-colors duration-300"
    data-shell-theme="atelier" data-shell-variant="sidebar">
    <a href="#app-main-content" class="shell-atelier__skip-link">
        {{ __('Skip to main content') }}
    </a>

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
        <div class="app-shell-body relative z-0 ml-0 flex min-w-0 flex-1 flex-col transition-all duration-300"
            :class="{ 'lg:ml-72': $store.sidebar.open, 'lg:ml-16': !$store.sidebar.open }"
            :inert="mobileSidebarOpen && !isDesktop" :aria-hidden="(mobileSidebarOpen && !isDesktop).toString()" x-cloak>
            <div class="app-shell-canvas" aria-hidden="true"></div>

            <!-- Top Header -->
            <header class="app-shell-header">
                <div class="app-shell-header-bar">
                    <!-- Page Title -->
                    <div class="app-shell-header-main">
                        <button type="button"
                            class="app-shell-mobile-toggle inline-flex items-center justify-center rounded-md p-2 transition-colors duration-150 focus:outline-none focus:ring-2 lg:hidden"
                            @click="openMobileSidebar($el)" :aria-expanded="mobileSidebarOpen.toString()"
                            aria-controls="app-sidebar" aria-label="{{ __('Open navigation menu') }}">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div class="app-shell-header-pivot min-w-0">
                            <div class="min-w-0 app-shell-page-lead" data-app-shell-header-copy>
                                <div class="app-shell-page-meta app-shell-page-eyebrow flex min-w-0 items-center gap-2.5">
                                    <span class="app-shell-page-dot" aria-hidden="true"></span>
                                    <p class="app-shell-page-kicker shrink-0 text-xs font-semibold uppercase tracking-[0.2em]">{{ __('Atelier surface') }}</p>
                                @isset($subheader)
                                    <span class="app-shell-page-meta-separator" aria-hidden="true">&middot;</span>
                                    <p class="app-shell-page-subtitle min-w-0 truncate text-sm">
                                        {{ $subheader }}
                                    </p>
                                @endisset
                                </div>
                                <h1 class="app-shell-page-title truncate text-2xl font-bold sm:text-[2.15rem]">
                                    @isset($header)
                                        {{ $header }}
                                    @else
                                        {{ __('Dashboard') }}
                                    @endisset
                                </h1>
                            </div>
                            <div class="app-shell-header-rule" aria-hidden="true"></div>
                        </div>
                    </div>

                    <!-- Additional Header Actions (if any) -->
                    <div class="app-toolbar-actions"></div>
                </div>
            </header>

            <!-- Page Content -->
            <main id="app-main-content" class="flex-1 min-w-0 overflow-y-auto overflow-x-hidden" aria-label="{{ __('Application content') }}">
                <div class="app-shell-content-wrap">
                    <div class="app-shell-content mx-auto w-full sm:px-6 lg:px-8">
                        <div class="app-shell-stack">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="app-shell-footer px-4 py-4 sm:px-6 lg:px-8">
                <div class="app-shell-footer-copy text-center text-sm">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. {{ __('All rights reserved.') }}
                </div>
            </footer>
        </div>
    </div>
</body>

</html>
