@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'topnav-responsive-toggle block w-full rounded-r-2xl border-l-4 border-[rgb(var(--shell-brand)/0.75)] bg-[rgb(var(--shell-brand)/0.12)] ps-3 pe-4 py-2 text-start text-base font-medium text-[rgb(var(--shell-ink))] focus:outline-none transition duration-150 ease-in-out'
            : 'topnav-responsive-toggle block w-full rounded-r-2xl border-l-4 border-transparent ps-3 pe-4 py-2 text-start text-base font-medium text-[rgb(var(--shell-muted))] hover:border-[rgb(var(--shell-line))] hover:bg-[rgb(var(--shell-surface)/0.65)] hover:text-[rgb(var(--shell-ink))] focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
