@props(['tag' => 'button'])

@if ($tag === 'a')
    <a {{ $attributes->class('btn btn-secondary') }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button'])->class('btn btn-secondary') }}>
        {{ $slot }}
    </button>
@endif
