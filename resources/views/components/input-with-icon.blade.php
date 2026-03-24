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
        class="input-with-icon block w-full rounded-xl border border-[rgb(var(--color-border-subtle))] bg-[rgb(var(--color-surface-card))/0.92] py-3 pl-10 pr-3 text-[rgb(var(--color-text-primary))] placeholder:text-[rgb(var(--color-text-muted))] transition-all duration-200 focus:border-[rgb(var(--color-border-strong))/0.85] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--color-border-strong))/0.2] dark:border-[rgb(var(--color-border-strong))/0.85] dark:bg-[rgb(var(--color-surface-card-muted))/0.9] dark:text-[rgb(var(--color-text-primary))] dark:placeholder:text-[rgb(var(--color-text-muted))] dark:focus:ring-white/20" />
</div>
