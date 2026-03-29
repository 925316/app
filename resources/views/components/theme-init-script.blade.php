<script>
    (function() {
        const savedTheme = localStorage.getItem('theme');
        const hasSavedTheme = savedTheme === 'dark' || savedTheme === 'light';
        const isDark = hasSavedTheme
            ? savedTheme === 'dark'
            : window.matchMedia('(prefers-color-scheme: dark)').matches;

        document.documentElement.classList.toggle('dark', isDark);
    })();
</script>
