@props(['color' => 'blue', 'icon' => null, 'iconName' => null, 'title' => null, 'gradient' => true])

@php
    $iconColorClass = match ($color) {
        'blue' => 'icon-blue',
        'green' => 'icon-green',
        'yellow' => 'icon-yellow',
        'red' => 'icon-red',
        default => 'icon-blue',
    };
    $titleColorClass = match ($color) {
        'blue' => 'text-zinc-800 dark:text-zinc-200',
        'green' => 'text-green-800 dark:text-green-200',
        'yellow' => 'text-yellow-800 dark:text-yellow-200',
        'red' => 'text-red-800 dark:text-red-200',
        default => 'text-gray-800 dark:text-gray-200',
    };
@endphp

<div class="card-info {{ $gradient ? 'bg-gradient-to-r' : '' }}">
    @if ($icon || $iconName || $title)
        <div class="flex items-start space-x-4 mb-4">
            @if ($icon || $iconName)
                <div class="card-icon-container {{ $iconColorClass }}">
                    <div class="w-6 h-6">
                        @if ($iconName)
                            <x-icon :name="$iconName" class="w-6 h-6" />
                        @else
                            {{ $icon }}
                        @endif
                    </div>
                </div>
            @endif
            @if ($title)
                <div class="flex-1">
                    <h4 class="text-lg font-semibold {{ $titleColorClass }}">
                        {{ $title }}
                    </h4>
                </div>
            @endif
        </div>
    @endif
    {{ $slot }}
</div>
