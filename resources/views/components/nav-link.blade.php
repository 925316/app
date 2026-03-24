@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'inline-flex items-center rounded-full border border-[rgb(var(--color-border-strong))/0.5] bg-[rgb(var(--color-surface-card-muted))/0.9] px-3 py-1.5 text-sm font-semibold leading-5 text-[rgb(var(--color-text-primary))] shadow-sm transition duration-200 ease-in-out dark:border-white/20 dark:bg-white/12'
            : 'inline-flex items-center rounded-full border border-transparent px-3 py-1.5 text-sm font-semibold leading-5 text-[rgb(var(--color-text-secondary))] transition duration-150 ease-in-out hover:border-[rgb(var(--color-border-subtle))/0.9] hover:bg-[rgb(var(--color-surface-card-muted))/0.82] hover:text-[rgb(var(--color-text-primary))] dark:text-[rgb(var(--color-text-secondary))] dark:hover:border-white/20 dark:hover:bg-white/10 dark:hover:text-[rgb(var(--color-text-primary))]';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
