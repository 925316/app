<script>
    (function() {
        const savedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = savedTheme ?? (systemPrefersDark ? 'dark' : 'light');
        const isDark = theme === 'dark';

        document.documentElement.classList.toggle('dark', isDark);
        if (savedTheme === null) {
            localStorage.setItem('theme', theme);
        }
    })();
</script>
