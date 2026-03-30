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
@php
    $inputAttributes = ['type' => $type];

    if ($id !== null) {
        $inputAttributes['id'] = $id;
    }

    if ($name !== null) {
        $inputAttributes['name'] = $name;
    }

    if ($value !== null) {
        $inputAttributes['value'] = $value;
    }

    if ($placeholder !== null) {
        $inputAttributes['placeholder'] = $placeholder;
    }

    if ($autocomplete !== null) {
        $inputAttributes['autocomplete'] = $autocomplete;
    }
@endphp

<div class="input-icon-wrapper">
    <div class="input-icon-slot">
        @if ($icon)
            <x-icon :name="$icon" :class="$iconClass" />
        @else
            {{ $slot }}
        @endif
    </div>
    <input {{ $attributes->class(['form-input', 'input-with-icon', 'block', 'w-full'])->merge($inputAttributes) }}
        @if ($required) required @endif
        @if ($autofocus) autofocus @endif />
</div>
