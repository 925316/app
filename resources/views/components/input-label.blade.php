@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700 dark:text-cool-200']) }}>
    {{ $value ?? $slot }}
</label>
