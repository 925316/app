@props([
    'label' => null,
    'color' => 'gray',
    'removeUrl' => null,
])

@php
    $colorClasses = match ($color) {
        'blue'
            => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700',
        'green'
            => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-800',
        'purple'
            => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 hover:bg-purple-200 dark:hover:bg-purple-800',
        'orange'
            => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700',
        'yellow'
            => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 hover:bg-yellow-200 dark:hover:bg-yellow-800',
        'gray'
            => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700',
        default
            => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700',
    };
@endphp

<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $colorClasses }}">
    {{ $label }}
    @if ($removeUrl)
        <a href="{{ $removeUrl }}" class="ml-1.5">
            <x-icon name="close" class="w-3 h-3" />
        </a>
    @endif
</span>
