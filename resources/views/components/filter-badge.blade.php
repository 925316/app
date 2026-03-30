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
    <span class="filter-badge__label">{{ $label }}</span>
    @if ($removeUrl)
        <a href="{{ $removeUrl }}" class="filter-badge__remove" aria-label="{{ __('Remove filter: :label', ['label' => strip_tags((string) $label)]) }}">
            <x-icon name="close" class="w-3 h-3" />
        </a>
    @endif
</span>
