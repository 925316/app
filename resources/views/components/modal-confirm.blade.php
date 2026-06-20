@props([
    'id' => null,
    'title' => 'Confirm',
    'icon' => null,
    'iconName' => null,
    'iconColor' => 'red',
    'action' => null,
    'method' => 'POST',
    'confirmText' => 'Confirm',
    'confirmColor' => 'blue',
    'cancelText' => 'Cancel',
]) @php
    $titleId = $id ? $id.'-title' : 'modal-confirm-title';
    $iconToneClass = match ($iconColor) {
        'red' => 'icon-red',
        'yellow' => 'icon-yellow',
        'blue' => 'icon-blue',
        'green' => 'icon-green',
        default => 'icon-gray',
    };
    $confirmButtonColor = match ($confirmColor) {
        'red' => 'btn btn-danger',
        'green' => 'btn btn-green',
        'blue' => 'btn btn-primary',
        'yellow' => 'btn btn-yellow',
        'gray' => 'btn btn-secondary',
        default => 'btn btn-primary',
    };
@endphp
<div id="{{ $id }}" class="modal-backdrop hidden">
    <div class="modal-content modal-md modal-panel relative top-20 mx-auto w-full border p-5 shadow-lg"
        role="dialog" aria-modal="true" aria-labelledby="{{ $titleId }}">
        <div class="mt-3">
            @if ($icon || $iconName)
                <div class="card-icon-container {{ $iconToneClass }} mx-auto h-12 w-12 rounded-full">
                    <div class="h-6 w-6">
                        @if ($iconName)
                            <x-icon :name="$iconName" class="w-6 h-6" />
                        @else
                            {{ $icon }}
                        @endif
                    </div>
                </div>
            @endif
            @if ($title)
                <div class="mt-2 text-center">
                    <h3 id="{{ $titleId }}" class="card-modal-title text-lg font-medium leading-6">
                        {{ $title }}
                    </h3>
                </div>
            @endif
            <form method="{{ $method }}" action="{{ $action }}" class="mt-5">
                @csrf
                @if ($method !== 'GET' && $method !== 'POST')
                    @method($method)
                @endif
                {{ $slot }}
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" onclick="closeModal('{{ $id }}')" class="btn btn-secondary">
                        {{ $cancelText }}
                    </button>
                    <button type="submit" class="{{ $confirmButtonColor }}">
                        {{ $confirmText }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('hidden');
    }
</script>
