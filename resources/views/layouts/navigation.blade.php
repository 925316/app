<nav x-data="{ open: false }"
    class="relative z-[100] border-b border-[rgb(var(--color-border-subtle))/0.65] bg-[rgb(var(--color-surface-elevated))/0.82] shadow-sm backdrop-blur-xl dark:bg-[rgb(var(--color-surface-page))/0.72]">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-[rgb(var(--color-brand))] dark:text-[rgb(var(--color-brand-hover))]" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div
                    class="hidden items-center gap-3 rounded-full border border-[rgb(var(--color-border-subtle))/0.8] bg-[rgb(var(--color-surface-card))/0.85] px-3 py-1.5 shadow-sm dark:bg-[rgb(var(--color-surface-card))/0.78] sm:flex">
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
            <div class="z-50 hidden items-center gap-3 sm:flex sm:ms-6">
                <!-- Username Link -->
                <a href="{{ route('profile.edit') }}"
                    class="inline-flex items-center rounded-full border border-transparent px-3 py-2 text-sm font-semibold text-[rgb(var(--color-text-secondary))] transition hover:border-[rgb(var(--color-brand))/0.45] hover:bg-[rgb(var(--color-surface-card))/0.78] hover:text-[rgb(var(--color-text-primary))] dark:hover:bg-[rgb(var(--color-surface-card-muted))/0.72]">
                    <span>{{ Auth::user()->username }}</span>
                </a>

                <!-- Logout Form -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center rounded-full border border-[rgb(var(--color-border-subtle))/0.9] bg-[rgb(var(--color-surface-card))/0.85] px-4 py-2 text-sm font-semibold text-[rgb(var(--color-text-secondary))] shadow-sm transition hover:-translate-y-0.5 hover:border-[rgb(var(--color-brand))/0.45] hover:text-[rgb(var(--color-brand-hover))] dark:bg-[rgb(var(--color-surface-card))/0.78] dark:hover:border-[rgb(var(--color-brand))/0.52] dark:hover:text-[rgb(var(--color-text-primary))]">
                        {{ __('Log Out') }}
                    </button>
                </form>

                <!-- Dark Mode Toggle -->
                <div class="hidden sm:flex sm:items-center">
                    <x-dark-mode-toggle />
                </div>
            </div>


            <!-- Hamburger -->
            <div class="-me-2 z-10 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center rounded-xl border border-[rgb(var(--color-border-subtle))] bg-[rgb(var(--color-surface-card))/0.85] p-2 text-[rgb(var(--color-text-secondary))] transition duration-150 ease-in-out hover:border-[rgb(var(--color-brand))/0.45] hover:text-[rgb(var(--color-brand-hover))] dark:bg-[rgb(var(--color-surface-card))/0.78] dark:hover:text-[rgb(var(--color-text-primary))]">
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
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden border-t border-[rgb(var(--color-border-subtle))/0.7] bg-[rgb(var(--color-surface-card))/0.92] backdrop-blur-xl dark:bg-[rgb(var(--color-surface-page))/0.9] sm:hidden">
        <div class="space-y-1 px-2 pb-3 pt-3">
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
        <div class="border-t border-[rgb(var(--color-border-subtle))/0.7] pb-2 pt-4">
            <div class="px-4">
                <div class="text-base font-semibold text-[rgb(var(--color-text-primary))]">{{ Auth::user()->username }}</div>
                <div class="text-sm text-[rgb(var(--color-text-muted))]">{{ Auth::user()->email }}</div>
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
