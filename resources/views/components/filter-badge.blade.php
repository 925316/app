@props([
    'label' => null,
    'color' => 'blue',
    'removeUrl' => null,
])

@php
    $colorClasses = match ($color) {
        'blue'
            => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800',
        'green'
            => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-800',
        'purple'
            => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 hover:bg-purple-200 dark:hover:bg-purple-800',
        'orange'
            => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 hover:bg-orange-200 dark:hover:bg-orange-800',
        'yellow'
            => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 hover:bg-yellow-200 dark:hover:bg-yellow-800',
        default
            => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600',
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
