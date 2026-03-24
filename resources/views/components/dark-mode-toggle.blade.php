<button x-data="{
    dark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark');

        if (this.dark) {
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
    }
}" x-cloak @click="toggle"
    class="inline-flex items-center justify-center rounded-full border border-[rgb(var(--color-border-subtle))/0.9] bg-[rgb(var(--color-surface-card))/0.85] p-2 text-[rgb(var(--color-text-secondary))] shadow-sm backdrop-blur transition duration-300 hover:-translate-y-0.5 hover:border-[rgb(var(--color-brand))/0.45] hover:text-[rgb(var(--color-brand-hover))] dark:bg-[rgb(var(--color-surface-card))/0.78] dark:text-[rgb(var(--color-text-secondary))] dark:hover:border-[rgb(var(--color-brand))/0.52] dark:hover:text-[rgb(var(--color-text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--color-brand))/0.45] focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-[rgb(var(--color-brand))/0.5] dark:focus:ring-offset-[rgb(var(--color-surface-page))]"
    aria-label="{{ __('Toggle dark mode') }}">
    <!-- Single icon that works in both themes -->
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
    </svg>
</button>
