<div x-data="{
    value: document.documentElement.dataset.theme || localStorage.getItem('theme-variant') || 'default',
    applyThemeVariant(nextTheme) {
        this.value = nextTheme;
        document.documentElement.dataset.theme = nextTheme;
        localStorage.setItem('theme-variant', nextTheme);
    }
}" class="hidden lg:flex lg:items-center lg:gap-2">
    <label for="theme-preset" class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Theme') }}</label>
    <select id="theme-preset" x-model="value" @change="applyThemeVariant($event.target.value)"
        class="form-select py-1.5 text-xs">
        <option value="default">{{ __('Default') }}</option>
        <option value="midnight">{{ __('Midnight') }}</option>
        <option value="emerald">{{ __('Emerald') }}</option>
    </select>
</div>
