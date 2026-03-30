<aside id="app-sidebar" x-data x-cloak data-sidebar-shell
    class="sidebar h-screen fixed inset-y-0 left-0 z-50 transform transition-all duration-300 lg:z-40"
    :class="isDesktop
        ? 'translate-x-0 ' + ($store.sidebar.open ? 'w-64' : 'w-16')
        : (mobileSidebarOpen ? 'translate-x-0 w-64' : 'w-64 -translate-x-full')"
    :role="(!isDesktop && mobileSidebarOpen) ? 'dialog' : null"
    :aria-modal="(!isDesktop && mobileSidebarOpen) ? 'true' : null"
    :aria-hidden="(!isDesktop && !mobileSidebarOpen).toString()">

    <!-- Logo and Toggle -->
    <div class="sidebar-header">
        <!-- Logo Container -->
        <div class="sidebar-logo" :class="mobileSidebarOpen || $store.sidebar.open ? 'sidebar-content-visible' : 'sidebar-content-hidden'">
            <a href="{{ route('dashboard') }}" class="sidebar-brand-mark shrink-0" aria-label="{{ __('Go to dashboard') }}">
                <x-application-logo class="block h-8 w-auto fill-current" />
            </a>
            <span class="sidebar-brand-text truncate text-lg font-semibold">
                {{ config('app.name', 'Laravel') }}
            </span>
        </div>

        <!-- Desktop Collapse Toggle -->
        <button type="button" @click="$store.sidebar.toggle()"
            class="sidebar-toggle hidden lg:inline-flex"
            :class="{ 'right-3': $store.sidebar.open, 'right-4': !$store.sidebar.open }" aria-label="{{ __('Toggle sidebar') }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path :class="{ 'hidden': !$store.sidebar.open, 'inline-flex': $store.sidebar.open }"
                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                <path :class="{ 'hidden': $store.sidebar.open, 'inline-flex': !$store.sidebar.open }"
                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Mobile Close Button -->
        <button type="button" x-ref="mobileSidebarCloseButton" @click="closeMobileSidebar()"
            class="sidebar-toggle right-3 inline-flex lg:hidden" aria-label="{{ __('Close navigation menu') }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav" aria-label="{{ __('Primary navigation') }}">
        <div class="space-y-1.5 px-3">
            <!-- Dashboard -->
            <x-sidebar-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" :icon="'home'" @click="closeMobileSidebar()">
                {{ __('Dashboard') }}
            </x-sidebar-nav-link>

            <!-- Accounts -->
            @if (Auth::user()->hasPrivilege(7))
                <x-sidebar-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')" :icon="'users'" @click="closeMobileSidebar()">
                    {{ __('Accounts') }}
                </x-sidebar-nav-link>
            @endif

            <!-- Licenses -->
            <x-sidebar-nav-link :href="route('licenses.index')" :active="request()->routeIs('licenses.*')" :icon="'key'" @click="closeMobileSidebar()">
                {{ __('Licenses') }}
            </x-sidebar-nav-link>

            <!-- Devices -->
            @if (Auth::user()->hasPrivilege(1))
                <x-sidebar-nav-link :href="route('devices.index')" :active="request()->routeIs('devices.*')" :icon="'desktop'" @click="closeMobileSidebar()">
                    {{ __('Devices') }}
                </x-sidebar-nav-link>
            @endif

            <!-- Packages -->
            @if (Auth::user()->hasPrivilege(1))
                <x-sidebar-nav-link :href="route('packages.index')" :active="request()->routeIs('packages.*')" :icon="'package'" @click="closeMobileSidebar()">
                    {{ __('Packages') }}
                </x-sidebar-nav-link>
            @endif

            <!-- Sessions -->
            @if (Auth::user()->hasPrivilege(7))
                <x-sidebar-nav-link :href="route('sessions.index')" :active="request()->routeIs('sessions.*')" :icon="'server'" @click="closeMobileSidebar()">
                    {{ __('Sessions') }}
                </x-sidebar-nav-link>
            @endif

            <!-- Logs -->
            @if (Auth::user()->hasPrivilege(7))
                <x-sidebar-nav-link :href="route('logs.index')" :active="request()->routeIs('logs.*')" :icon="'document-text'" @click="closeMobileSidebar()">
                    {{ __('Logs') }}
                </x-sidebar-nav-link>
            @endif
        </div>
    </nav>

    <!-- User Profile Section -->
    <div class="sidebar-user" data-sidebar-account>
        <div class="sidebar-account-panel" x-show="mobileSidebarOpen || $store.sidebar.open" x-transition>
            <div class="sidebar-account-row" data-sidebar-profile-row>
                <a href="{{ route('profile.edit') }}"
                    class="sidebar-account-entry flex min-w-0 flex-1 items-center gap-3 px-1 py-1 text-sm font-medium transition-colors duration-150">
                    <div class="user-avatar flex h-8 w-8 shrink-0 items-center justify-center text-sm font-semibold text-white">
                        {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="sidebar-user-name truncate text-sm font-medium">
                            {{ Auth::user()->username }}
                        </p>
                        <p class="sidebar-user-meta truncate text-xs">
                            {{ Auth::user()->email }}
                        </p>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="sidebar-account-icon" aria-label="{{ __('Log out') }}">
                        <x-icon name="logout" class="h-4 w-4" />
                    </button>
                </form>
            </div>

            <x-dark-mode-toggle variant="sidebar-row" data-sidebar-theme-row />

            @php
                $supportedLocales = (array) config('app.supported_locales', []);
                $currentLocale = app()->getLocale();
            @endphp

            <form method="POST" action="{{ route('profile.update-locale') }}" class="sidebar-account-language-form"
                data-sidebar-language-row>
                @csrf
                @method('patch')

                <label class="sidebar-account-language-label" for="sidebar-locale-select">{{ __('Language') }}</label>
                <select id="sidebar-locale-select" name="locale" class="sidebar-account-language-select"
                    onchange="this.form.submit()">
                    @if (count($supportedLocales) === 0)
                        <option value="{{ $currentLocale }}" selected>
                            {{ strtoupper($currentLocale) }}
                        </option>
                    @endif
                    @foreach ($supportedLocales as $value => $label)
                        <option value="{{ $value }}" {{ $currentLocale === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <a href="{{ route('profile.edit') }}" class="sidebar-account-collapsed mx-auto"
            x-show="!mobileSidebarOpen && !$store.sidebar.open" x-transition aria-label="{{ __('Profile') }}">
            <div class="user-avatar flex h-8 w-8 items-center justify-center text-sm font-semibold text-white"
                :class="{ 'h-6 w-6 text-xs': !$store.sidebar.open && !mobileSidebarOpen }">
                {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
            </div>
        </a>
    </div>
</aside>
