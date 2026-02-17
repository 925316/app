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
        'red' => 'bg-red-600 hover:bg-red-700 focus:ring-red-300',
        'green' => 'bg-green-600 hover:bg-green-700 focus:ring-green-300',
        'blue' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-300',
        'yellow' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-300',
        'gray' => 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-300',
        default => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-300',
    };
@endphp
<div id="{{ $id }}" class="modal-backdrop hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800 modal-content">
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
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
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
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeModal('{{ $id }}')"
                        class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        {{ $cancelText }}
                    </button>
                    <button type="submit"
                        class="px-4 py-2 {{ $confirmButtonColor }} text-white text-base font-medium rounded-md shadow-sm transition focus:outline-none focus:ring-2">
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