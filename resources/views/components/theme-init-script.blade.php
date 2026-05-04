<script>
    (function() {
        const savedTheme = localStorage.getItem('theme');
        const hasSavedTheme = savedTheme === 'dark' || savedTheme === 'light';
        // Default to dark mode when no saved preference
        const isDark = hasSavedTheme
            ? savedTheme === 'dark'
            : true;

        document.documentElement.classList.toggle('dark', isDark);
    })();
</script>
