@props(['tag' => 'button'])

@if ($tag === 'a')
    <a {{ $attributes->class('btn btn-primary') }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit'])->class('btn btn-primary') }}>
        {{ $slot }}
    </button>
@endif
