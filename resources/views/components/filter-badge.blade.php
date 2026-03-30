@props([
    'label' => null,
    'color' => 'blue',
    'removeUrl' => null,
])

@php
    $colorClasses = match ($color) {
        'blue' => 'filter-badge filter-badge--blue',
        'green' => 'filter-badge filter-badge--green',
        'purple' => 'filter-badge filter-badge--purple',
        'orange' => 'filter-badge filter-badge--orange',
        'yellow' => 'filter-badge filter-badge--yellow',
        default => 'filter-badge filter-badge--neutral',
    };
@endphp

<span class="{{ $colorClasses }}">
    {{ $label }}
    @if ($removeUrl)
        <a href="{{ $removeUrl }}" class="ml-1.5">
            <x-icon name="close" class="w-3 h-3" />
        </a>
    @endif
</span>
