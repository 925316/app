@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'topnav-responsive-toggle inline-flex items-center px-1 pt-1 border-b-2 border-[rgb(var(--shell-brand)/0.75)] text-sm font-medium leading-5 text-[rgb(var(--shell-ink))] focus:outline-none transition duration-150 ease-in-out'
            : 'topnav-responsive-toggle inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-[rgb(var(--shell-muted))] hover:text-[rgb(var(--shell-ink))] hover:border-[rgb(var(--shell-line))] focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
