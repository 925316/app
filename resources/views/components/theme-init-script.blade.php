<script>
    (function() {
        const savedTheme = localStorage.getItem('theme') ?? 'dark';
        const isDark = savedTheme === 'dark';

        document.documentElement.classList.toggle('dark', isDark);

        if (localStorage.getItem('theme') === null) {
            localStorage.setItem('theme', 'dark');
        }
    })();
</script>
