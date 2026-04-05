@props([
    'title' => null,
    'value' => null,
    'icon' => null,
    'iconColor' => 'icon-blue',
])
<div class="card-stat">
    <div class="stat-card-shell">
        <div class="card-icon-container {{ $iconColor }}">
            @if ($icon)
                <x-icon :name="$icon" class="w-7 h-7" />
            @else
                {{ $slot }}
            @endif
        </div>
        <div class="stat-card-copy">
            <p class="stat-card-label">{{ $title }}</p>
            <p class="stat-card-value text-2xl font-semibold">
                {{ $value }}
            </p>
        </div>
    </div>
</div>
