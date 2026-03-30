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
        'blue' => 'card-info-title',
        'green' => 'card-info-title',
        'yellow' => 'card-info-title',
        'red' => 'card-info-title',
        default => 'card-info-title',
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
