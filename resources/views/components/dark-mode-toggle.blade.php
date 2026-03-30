@props([
    'variant' => 'icon',
    'label' => __('Theme'),
])

<button x-data="{
    dark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);

        if (this.dark) {
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
    }
}" x-cloak type="button" @click="toggle()"
    {{ $attributes->class([
        'topnav-utility-link p-2 rounded-full transition-colors duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-400 dark:focus-visible:ring-gray-500 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900' => $variant === 'icon',
        'sidebar-account-toggle flex w-full items-center justify-between gap-3 rounded-2xl px-1 py-2.5 text-left transition-colors duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2' => $variant === 'sidebar-row',
    ]) }}
    x-bind:aria-label="dark ? @js(__('Switch to light mode')) : @js(__('Switch to dark mode'))"
    x-bind:aria-pressed="dark ? 'true' : 'false'">
    @if ($variant === 'sidebar-row')
        <span class="sidebar-account-toggle-copy flex items-center gap-3">
            <span class="sidebar-account-label text-sm font-medium">{{ $label }}</span>
        </span>
    @endif

    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
    </svg>
</button>
