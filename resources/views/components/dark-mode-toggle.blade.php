<button
    x-data="{
        dark: false,
        init() {
            // Check for saved theme preference or default to light mode
            const savedTheme = localStorage.getItem('theme');
            const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

            // Apply theme based on preference
            if (savedTheme === 'dark' || (savedTheme === null && prefersDarkScheme.matches)) {
                this.dark = true;
                document.documentElement.classList.add('dark');
            } else {
                this.dark = false;
                document.documentElement.classList.remove('dark');
            }

            // Listen for system theme changes
            prefersDarkScheme.addEventListener('change', (e) => {
                const currentTheme = localStorage.getItem('theme');
                if (currentTheme === null) {
                    this.dark = e.matches;
                    if (e.matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            });
        },
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark');

            if (this.dark) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        }
    }"
    @click="toggle"
    class="p-2 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
    aria-label="Toggle dark mode"
>
    <!-- Sun icon (shown in dark mode) -->
    <svg
        x-show="dark"
        class="w-5 h-5"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
    </svg>

    <!-- Moon icon (shown in light mode) -->
    <svg
        x-show="!dark"
        class="w-5 h-5"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
    </svg>
</button>
