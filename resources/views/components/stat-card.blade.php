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
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $title }}</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                {{ $value }}
            </p>
        </div>
    </div>
</div>
