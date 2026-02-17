@props([
    'id' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'autofocus' => false,
    'autocomplete' => null,
    'icon' => null,
    'iconClass' => 'input-icon',
])
<div class="input-icon-wrapper">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        @if ($icon)
            <x-icon :name="$icon" :class="$iconClass" />
        @else
            {{ $slot }}
        @endif
    </div>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
        @if ($value) value="{{ $value }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif @if ($autofocus) autofocus @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        class="block w-full pl-10 pr-3 py-3 border border-cool-300/50 dark:border-cool-600/50 rounded-lg bg-white/50 dark:bg-cool-700/50 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200 input-with-icon" />
</div>