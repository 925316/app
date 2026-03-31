@props([
    'id' => null,
    'name' => null,
    'label' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'submitOnSelect' => false,
])

@php
    $fieldName = $name ?? $id ?? 'filter';
    $fieldId = $id ?? \Illuminate\Support\Str::slug(str_replace(['[', ']'], '-', (string) $fieldName));
    $instanceId = $fieldId.'-'.substr(md5($fieldName.uniqid('', true).mt_rand()), 0, 8);
    $labelId = $instanceId.'-label';
    $triggerId = $instanceId.'-trigger';
    $inputId = $instanceId.'-input';
    $menuId = $instanceId.'-menu';

    $normalizedOptions = [];

    foreach ($options as $optionValue => $option) {
        if (is_array($option)) {
            $normalizedOptions[] = [
                'value' => (string) ($option['value'] ?? $optionValue),
                'label' => (string) ($option['label'] ?? ''),
            ];

            continue;
        }

        $normalizedOptions[] = [
            'value' => (string) $optionValue,
            'label' => (string) $option,
        ];
    }

    $selectedValue = $value === null ? '' : (string) $value;
    $fallbackLabel = $placeholder ?? __('Select an option');
    $selectedOption = collect($normalizedOptions)->first(fn (array $option): bool => $option['value'] === $selectedValue);
    $selectedLabel = $selectedOption['label'] ?? $fallbackLabel;
    $shouldSubmitOnSelect = filter_var($submitOnSelect, FILTER_VALIDATE_BOOLEAN);
    $labelledBy = $label ? trim($labelId.' '.$triggerId) : $triggerId;
@endphp

<div
    class="filter-dropdown"
    x-data="{
        open: false,
        value: @js($selectedValue),
        selectedLabel: @js($selectedLabel),
        placeholder: @js($fallbackLabel),
        optionElements() {
            if (! this.$refs.menu) {
                return [];
            }

            return Array.from(this.$refs.menu.querySelectorAll('[role=\'option\']'));
        },
        selectedIndex() {
            return this.optionElements().findIndex((option) => option.dataset.value === this.value);
        },
        focusIndex(index) {
            const options = this.optionElements();

            if (! options.length) {
                return;
            }

            const boundedIndex = Math.max(0, Math.min(index, options.length - 1));
            options[boundedIndex].focus();
        },
        openMenu(target = 'selected') {
            this.open = true;

            this.$nextTick(() => {
                const options = this.optionElements();

                if (! options.length) {
                    return;
                }

                if (target === 'last') {
                    this.focusIndex(options.length - 1);

                    return;
                }

                const currentIndex = this.selectedIndex();
                this.focusIndex(currentIndex >= 0 ? currentIndex : 0);
            });
        },
        moveFocus(step) {
            const options = this.optionElements();

            if (! options.length) {
                return;
            }

            const focusedIndex = options.findIndex((option) => option === document.activeElement);
            const baselineIndex = focusedIndex >= 0
                ? focusedIndex
                : (this.selectedIndex() >= 0 ? this.selectedIndex() : 0);

            this.focusIndex(baselineIndex + step);
        },
        focusBoundary(target = 'start') {
            const options = this.optionElements();

            if (! options.length) {
                return;
            }

            this.focusIndex(target === 'end' ? options.length - 1 : 0);
        },
        toggleMenu() {
            if (this.open) {
                this.close();

                return;
            }

            this.openMenu();
        },
        commit(nextValue, nextLabel, shouldSubmit) {
            this.value = nextValue;
            this.selectedLabel = nextLabel;
            this.open = false;

            this.$nextTick(() => {
                if (shouldSubmit && this.$refs.input.form) {
                    if (typeof this.$refs.input.form.requestSubmit === 'function') {
                        this.$refs.input.form.requestSubmit();

                        return;
                    }

                    this.$refs.input.form.submit();

                    return;
                }

                this.$refs.trigger.focus();
            });
        },
        close(returnFocus = false) {
            this.open = false;

            if (returnFocus) {
                this.$nextTick(() => {
                    this.$refs.trigger.focus();
                });
            }
        },
    }"
    @click.outside="close()"
    @keydown.escape.window="if (open) { close(true) }"
>
    @if ($label)
        <span id="{{ $labelId }}" class="form-label">{{ $label }}</span>
    @endif

    <div class="filter-dropdown-control">
        <input
            x-ref="input"
            id="{{ $inputId }}"
            type="hidden"
            name="{{ $fieldName }}"
            value="{{ $selectedValue }}"
            x-model="value"
        >

        <button
            x-ref="trigger"
            type="button"
            id="{{ $triggerId }}"
            class="filter-dropdown-trigger"
            aria-haspopup="listbox"
            aria-controls="{{ $menuId }}"
            aria-labelledby="{{ $labelledBy }}"
            :aria-expanded="open.toString()"
            @click="toggleMenu()"
            @keydown.down.prevent="openMenu()"
            @keydown.up.prevent="openMenu('last')"
        >
            <span class="filter-dropdown-trigger-text" x-text="selectedLabel || placeholder">{{ $selectedLabel }}</span>
            <x-icon name="chevron-down" class="filter-dropdown-trigger-icon" />
        </button>

        <div
            x-ref="menu"
            id="{{ $menuId }}"
            class="filter-dropdown-menu"
            x-cloak
            x-show="open"
            x-transition.opacity.origin.top.left
            role="listbox"
            aria-orientation="vertical"
            aria-labelledby="{{ $labelledBy }}"
            @keydown.down.prevent="moveFocus(1)"
            @keydown.up.prevent="moveFocus(-1)"
            @keydown.home.prevent="focusBoundary()"
            @keydown.end.prevent="focusBoundary('end')"
        >
            @foreach ($normalizedOptions as $option)
                <button
                    type="button"
                    class="filter-dropdown-option"
                    data-value="{{ $option['value'] }}"
                    role="option"
                    tabindex="-1"
                    :class="{ 'is-active': value === @js($option['value']) }"
                    :aria-selected="(value === @js($option['value'])).toString()"
                    @click="commit(@js($option['value']), @js($option['label']), @js($shouldSubmitOnSelect))"
                    @keydown.enter.prevent="commit(@js($option['value']), @js($option['label']), @js($shouldSubmitOnSelect))"
                    @keydown.space.prevent="commit(@js($option['value']), @js($option['label']), @js($shouldSubmitOnSelect))"
                >
                    <span class="filter-dropdown-option-text">{{ $option['label'] }}</span>
                </button>
            @endforeach
        </div>
    </div>
</div>
