<aside id="app-sidebar" x-data x-cloak data-sidebar-shell
    :data-collapsed-desktop="(isDesktop && !$store.sidebar.open).toString()"
    class="sidebar h-screen fixed inset-y-0 left-0 z-50 transform transition-all duration-300 lg:z-40"
    :class="isDesktop
        ? 'translate-x-0 ' + ($store.sidebar.open ? 'w-72' : 'w-16')
        : (mobileSidebarOpen ? 'translate-x-0 w-72' : 'w-72 -translate-x-full')"
    :role="(!isDesktop && mobileSidebarOpen) ? 'dialog' : null"
    :aria-modal="(!isDesktop && mobileSidebarOpen) ? 'true' : null"
    aria-label="{{ __('Primary navigation') }}"
    :aria-hidden="(!isDesktop && !mobileSidebarOpen).toString()">

    <!-- Logo and Toggle -->
    <div class="sidebar-header">
        <!-- Logo Container -->
        <div class="sidebar-logo" :class="mobileSidebarOpen || $store.sidebar.open ? 'sidebar-content-visible' : 'sidebar-content-hidden'">
            <a href="{{ route('dashboard') }}" class="sidebar-brand-mark shrink-0" aria-label="{{ __('Go to dashboard') }}">
                <x-application-logo class="block h-8 w-auto fill-current" />
            </a>
            <div class="sidebar-brand-lockup">
                <span class="sidebar-brand-kicker">{{ __('Atelier OS') }}</span>
                <span id="app-sidebar-title" class="sidebar-brand-text truncate text-lg font-semibold">
                    {{ config('app.name', 'Laravel') }}
                </span>
            </div>
        </div>

        <!-- Desktop Collapse Toggle -->
        <button type="button" @click="$store.sidebar.toggle()"
            class="sidebar-toggle sidebar-toggle-desktop hidden lg:inline-flex" aria-label="{{ __('Toggle sidebar') }}">
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
    <nav class="sidebar-nav" :class="{ 'sidebar-nav-collapsed': isDesktop && !$store.sidebar.open }"
        aria-label="{{ __('Primary navigation') }}">
        <div class="sidebar-nav-panel sidebar-nav-section space-y-1.5" data-sidebar-nav-panel>
            <p class="sidebar-nav-heading" x-cloak x-show="isDesktop ? $store.sidebar.open : mobileSidebarOpen">
                {{ __('Workspace') }}
            </p>

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

            @if (Auth::user()->hasPrivilege(7))
                <x-sidebar-nav-link :href="route('api-signing-keys.index')" :active="request()->routeIs('api-signing-keys.*')" :icon="'shield'" @click="closeMobileSidebar()">
                    {{ __('Signing Keys') }}
                </x-sidebar-nav-link>
            @endif
        </div>
    </nav>

    <!-- User Profile Section -->
    <div class="sidebar-user" :class="{ 'sidebar-user-collapsed': isDesktop && !$store.sidebar.open }" data-sidebar-account>
        <div class="sidebar-account-surface" data-sidebar-utility-surface>
            <div class="sidebar-account-panel" x-cloak x-show="isDesktop ? $store.sidebar.open : mobileSidebarOpen">
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

                <div class="sidebar-account-divider" aria-hidden="true"></div>

                <x-dark-mode-toggle variant="sidebar-row" data-sidebar-theme-row />

                @php
                    $supportedLocales = (array) config('app.supported_locales', []);
                    $currentLocale = app()->getLocale();
                    $localeValues = array_keys($supportedLocales);
                    $initialLocaleIndex = array_search($currentLocale, $localeValues, true);
                    $initialLocaleIndex = is_int($initialLocaleIndex) ? $initialLocaleIndex : 0;
                @endphp

                <form method="POST" action="{{ route('profile.update-locale') }}" class="sidebar-account-language-form"
                    x-data="{
                        localeMenuOpen: false,
                        activeLocaleIndex: {{ $initialLocaleIndex }},
                        localeOptions() {
                            return Array.from(this.$el.querySelectorAll('[data-locale-option]'));
                        },
                        focusLocaleOption(index) {
                            const options = this.localeOptions();

                            if (!options.length) {
                                return;
                            }

                            const normalizedIndex = (index + options.length) % options.length;

                            this.activeLocaleIndex = normalizedIndex;

                            this.$nextTick(() => {
                                options[normalizedIndex]?.focus();
                            });
                        },
                        openLocaleMenu() {
                            this.localeMenuOpen = true;
                            this.focusLocaleOption(this.activeLocaleIndex);
                        },
                        closeLocaleMenu(returnFocus = true) {
                            this.localeMenuOpen = false;

                            if (returnFocus) {
                                this.$nextTick(() => {
                                    this.$refs.localeTrigger?.focus();
                                });
                            }
                        },
                        focusNextLocaleOption() {
                            this.focusLocaleOption(this.activeLocaleIndex + 1);
                        },
                        focusPreviousLocaleOption() {
                            this.focusLocaleOption(this.activeLocaleIndex - 1);
                        },
                        selectLocale(value, index) {
                            this.$refs.localeInput.value = value;
                            this.activeLocaleIndex = index;
                            this.closeLocaleMenu(false);
                            this.$el.submit();
                        }
                    }" @click.outside="closeLocaleMenu(false)" @keydown.escape.window="closeLocaleMenu()"
                    data-sidebar-language-row>
                    @csrf
                    @method('patch')

                    <span id="sidebar-locale-label" class="sidebar-account-language-label">{{ __('Language') }}</span>

                    <div class="sidebar-account-language-control">
                        <button type="button" class="sidebar-account-language-trigger" data-sidebar-language-trigger aria-haspopup="listbox"
                            x-ref="localeTrigger" :aria-expanded="localeMenuOpen.toString()" aria-labelledby="sidebar-locale-label"
                            aria-controls="sidebar-locale-menu"
                            @click="localeMenuOpen ? closeLocaleMenu(false) : openLocaleMenu()"
                            @keydown.down.prevent="openLocaleMenu()"
                            @keydown.up.prevent="openLocaleMenu()">
                            <span class="sidebar-account-language-current">
                                {{ $supportedLocales[$currentLocale] ?? strtoupper($currentLocale) }}
                            </span>
                            <x-icon name="chevron-down" class="sidebar-account-language-trigger-icon" />
                        </button>

                        <input x-ref="localeInput" id="sidebar-locale-select" type="hidden" name="locale"
                            value="{{ $currentLocale }}">
                        <div id="sidebar-locale-menu" class="sidebar-account-language-menu" data-sidebar-language-menu x-cloak x-show="localeMenuOpen"
                            x-transition.opacity.origin.bottom.right role="listbox" aria-labelledby="sidebar-locale-label"
                            @keydown.down.prevent="focusNextLocaleOption()" @keydown.up.prevent="focusPreviousLocaleOption()"
                            @keydown.home.prevent="focusLocaleOption(0)"
                            @keydown.end.prevent="focusLocaleOption(localeOptions().length - 1)">
                            @if (count($supportedLocales) === 0)
                                <button type="button" class="sidebar-account-language-option is-active" role="option" aria-selected="true" disabled>
                                    <span>{{ strtoupper($currentLocale) }}</span>
                                </button>
                            @endif
                            @foreach ($supportedLocales as $value => $label)
                                @php
                                    $index = array_search($value, $localeValues, true);
                                @endphp
                                <button type="button"
                                    class="sidebar-account-language-option {{ $currentLocale === $value ? 'is-active' : '' }}"
                                    data-locale-option role="option" :tabindex="activeLocaleIndex === {{ $index }} ? 0 : -1"
                                    :aria-selected="(activeLocaleIndex === {{ $index }}).toString()"
                                    @focus="activeLocaleIndex = {{ $index }}"
                                    @click="selectLocale('{{ $value }}', {{ $index }})"
                                    @keydown.enter.prevent="selectLocale('{{ $value }}', {{ $index }})"
                                    @keydown.space.prevent="selectLocale('{{ $value }}', {{ $index }})">
                                    <span>{{ $label }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>

            <a href="{{ route('profile.edit') }}" class="sidebar-account-collapsed" x-cloak
                x-show="isDesktop && !$store.sidebar.open" aria-label="{{ __('Profile') }}">
                <div class="user-avatar flex h-8 w-8 items-center justify-center text-sm font-semibold text-white"
                    :class="{ 'h-6 w-6 text-xs': !$store.sidebar.open && !mobileSidebarOpen }">
                    {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                </div>
            </a>
        </div>
    </div>
</aside>
