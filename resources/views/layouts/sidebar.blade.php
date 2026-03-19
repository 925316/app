<aside id="app-sidebar" x-data x-cloak
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
            <a href="{{ route('dashboard') }}" class="shrink-0">
                <x-application-logo class="block h-8 w-auto fill-current text-gray-800 dark:text-gray-200" />
            </a>
            <span class="text-lg font-semibold text-gray-800 dark:text-gray-200 truncate">
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
    <nav class="flex-1 overflow-y-auto py-4">
        <div class="space-y-1 px-2">
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
    <div class="border-t border-cool-200/50 dark:border-cool-700/50 p-4">
        <!-- Profile Header - Only avatar visible when collapsed -->
        <div class="mb-4 flex items-center justify-between" :class="mobileSidebarOpen || $store.sidebar.open ? 'justify-between' : 'justify-center'">
            <!-- User Avatar -->
            <div class="shrink-0">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-sm font-semibold text-white"
                    :class="{ 'h-6 w-6 text-xs': !$store.sidebar.open && !mobileSidebarOpen }">
                    {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                </div>
            </div>

            <!-- User Info - Hidden when collapsed -->
            <div class="ml-3 min-w-0 flex-1" x-show="mobileSidebarOpen || $store.sidebar.open" x-transition>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                    {{ Auth::user()->username }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ Auth::user()->email }}
                </p>
            </div>

            <!-- Dark Mode Toggle - Hidden when collapsed -->
            <div class="ml-3 shrink-0" x-show="mobileSidebarOpen || $store.sidebar.open" x-transition>
                <x-dark-mode-toggle />
            </div>
        </div>

        <!-- User Actions - Hidden when collapsed -->
        <div x-show="mobileSidebarOpen || $store.sidebar.open" x-transition class="space-y-2">
            <a href="{{ route('profile.edit') }}"
                class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors duration-150">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ __('Profile') }}
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center w-full px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors duration-150">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</aside>
