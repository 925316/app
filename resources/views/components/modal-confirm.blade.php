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
    $iconBgColor = match ($iconColor) {
        'red' => 'bg-red-100 dark:bg-red-900',
        'yellow' => 'bg-yellow-100 dark:bg-yellow-900',
        'blue' => 'bg-blue-100 dark:bg-blue-900',
        'green' => 'bg-green-100 dark:bg-green-900',
        default => 'bg-gray-100 dark:bg-gray-700',
    };
    $iconTextColor = match ($iconColor) {
        'red' => 'text-red-600 dark:text-red-400',
        'yellow' => 'text-yellow-600 dark:text-yellow-400',
        'blue' => 'text-blue-600 dark:text-blue-400',
        'green' => 'text-green-600 dark:text-green-400',
        default => 'text-gray-600 dark:text-gray-400',
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
                <div class="flex items-center justify-center mx-auto h-12 w-12 rounded-full {{ $iconBgColor }}">
                    <div class="h-6 w-6 {{ $iconTextColor }}">
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
