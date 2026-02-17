@props(['status' => 'default', 'text' => null])

<span class="badge badge-{{ $status }}">
    {{ $text ?? ucfirst($status) }}
</span>
