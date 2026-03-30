@props([
    'title' => null,
    'value' => null,
    'icon' => null,
    'iconColor' => 'icon-blue',
])
<div class="card-stat">
    <div class="flex items-center">
        <div class="card-icon-container {{ $iconColor }}">
            @if ($icon)
                <x-icon :name="$icon" class="w-7 h-7" />
            @else
                {{ $slot }}
            @endif
        </div>
        <div class="ml-4">
            <p class="stat-card-label text-sm font-medium">{{ $title }}</p>
            <p class="stat-card-value text-2xl font-semibold">
                {{ $value }}
            </p>
        </div>
    </div>
</div>
