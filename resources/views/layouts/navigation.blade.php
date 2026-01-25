<nav x-data="{ open: false }"
    class="bg-white/90 dark:bg-cool-900/80 backdrop-blur-sm border-b border-cool-200/50 dark:border-cool-700/50 shadow-sm relative z-[100]">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @if (Auth::user()->hasPrivilege(7))
                        <x-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')">
                            {{ __('Accounts') }}
                        </x-nav-link>
                    @endif
                    <x-nav-link :href="route('licenses.index')" :active="request()->routeIs('licenses.*')">
                        {{ __('Licenses') }}
                    </x-nav-link>
                    @if (Auth::user()->hasPrivilege(1))
                        <x-nav-link :href="route('devices.index')" :active="request()->routeIs('devices.*')">
                            {{ __('Devices') }}
                        </x-nav-link>
                    @endif
                    @if (Auth::user()->hasPrivilege(1))
                        <x-nav-link :href="route('packages.index')" :active="request()->routeIs('packages.*')">
                            {{ __('Packages') }}
                        </x-nav-link>
                    @endif
                    @if (Auth::user()->hasPrivilege(7))
                        <x-nav-link :href="route('logs.index')" :active="request()->routeIs('logs.*')">
                            {{ __('Logs') }}
                        </x-nav-link>
                    @endif
                </div>

            </div>

            <!-- User Info and Actions -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 z-50">
                <!-- Username Link -->
                <a href="{{ route('profile.edit') }}"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white transition-colors duration-150">
                    <span>{{ Auth::user()->username }}</span>
                </a>

                <!-- Logout Form -->
                <form method="POST" action="{{ route('logout') }}" class="ml-4">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-300 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-100 focus:outline-none transition ease-in-out duration-150">
                        {{ __('Log Out') }}
                    </button>
                </form>

                <!-- Dark Mode Toggle -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dark-mode-toggle />
                </div>
            </div>



            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden z-10">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-300 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if (Auth::user()->hasPrivilege(7))
                <x-responsive-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')">
                    {{ __('Accounts') }}
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('licenses.index')" :active="request()->routeIs('licenses.*')">
                {{ __('Licenses') }}
            </x-responsive-nav-link>
            @if (Auth::user()->hasPrivilege(1))
                <x-responsive-nav-link :href="route('devices.index')" :active="request()->routeIs('devices.*')">
                    {{ __('Devices') }}
                </x-responsive-nav-link>
            @endif
            @if (Auth::user()->hasPrivilege(1))
                <x-responsive-nav-link :href="route('packages.index')" :active="request()->routeIs('packages.*')">
                    {{ __('Packages') }}
                </x-responsive-nav-link>
            @endif
            @if (Auth::user()->hasPrivilege(7))
                <x-responsive-nav-link :href="route('logs.index')" :active="request()->routeIs('logs.*')">
                    {{ __('Logs') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-cool-200/50 dark:border-cool-700/50">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-100">{{ Auth::user()->username }}</div>
                <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Dark Mode Toggle for Mobile -->
                <div class="px-4 py-2">
                    <x-dark-mode-toggle />
                </div>

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
