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
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        @if ($icon)
            <x-icon :name="$icon" :class="$iconClass" />
        @else
            {{ $slot }}
        @endif
    </div>
    <input {{ $attributes->class(['form-input', 'input-with-icon', 'block', 'w-full', 'py-3', 'pl-10', 'pr-3'])->merge($inputAttributes) }}
        @if ($required) required @endif
        @if ($autofocus) autofocus @endif />
</div>
