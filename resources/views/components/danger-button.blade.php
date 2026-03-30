@props(['tag' => 'button'])

@if ($tag === 'a')
    <a {{ $attributes->class('btn btn-danger') }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit'])->class('btn btn-danger') }}>
        {{ $slot }}
    </button>
@endif
