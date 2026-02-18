{{-- format-ignore-start --}}
@props([
    // name of the datepicker. This name is used when posting the form with the datepicker
    'name' => defaultBladewindName(),
    'hourLabel' => config('bladewind.timepicker.hour_label', __("bladewind.timepicker_hour_label")),
    'minuteLabel' => config('bladewind.timepicker.minute_label', __("bladewind.timepicker_minute_label")),
    'formatLabel' => config('bladewind.timepicker.format_label', __("bladewind.timepicker_format_label")),
    'required' => false,
      // what should the time hours be displayed as. Available options are 12, 24
    'format' => config('bladewind.timepicker.format', '12'),
    'selectedValue' => '',
    'style' => config('bladewind.timepicker.style', 'popup'),
    'label' => '',
    'placeholder' => config('bladewind.timepicker.placeholder', __("bladewind.timepicker_placeholder")),
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    if(!empty($selectedValue)) {
        $selected_time_array = explode(':', str_replace(' ', '', $selectedValue));
        $selected_hour = $selected_time_array[0];
        $selected_minute = substr($selected_time_array[1], 0, 2);
        $selected_format = (strlen($selected_time_array[1]) > 2) ? strtoupper(substr($selected_time_array[1], 2, 2)) : '';
    }
@endphp
{{-- format-ignore-end --}}

@if($style == 'popup')
    <div style="width: 125px"
         class="inline-flex items-center align-middle timepicker-{{$name}}">
        <x-input
                class="time-{{$name}}"
                :name="$name"
                suffix="clock"
                :required="$required"
                suffix_is_icon="true"
                :add_clearing="false"
                onclick="openTimepicker('{{$name}}')"
                :selected_value="$selectedValue"
                :placeholder="$placeholder"
                :label="$label"/>
        @once
            <div class="hidden clear-time">
                <x-icon
                        name="x-circle" type="solid"
                        class="ml-1 opacity-70 hover:opacity-100 cursor-pointer"/>
            </div>
            <x-script :nonce="$nonce">
                const clockIcon = domEl('.timepicker-{{$name}} .suffix').innerHTML;
                const clearIcon = domEl('.clear-time').innerHTML;
            </x-script>
        @endonce
    </div>
    <x-modal
            title="{{ __('bladewind.timepicker_popup_title') }}"
            name="{{$name}}"
            cancel_button_label=""
            ok_button_label="{{ __('bladewind.okay') }}"
            ok_button_action="setTime('{{$name}}', '{{$format}}');"
            show_cancel_button="false"
            align_buttons="center">
        <div class="flex justify-center pt-4 pb-3">
            <div>
                <x-input
                        numeric="true"
                        tabindex="1"
                        max="{{($format=='12' ? 12 : 23)}}"
                        selected_value="{{$selected_hour??''}}"
                        class="w-[105px] text-center border-2 border-gray-200/70 rounded-md !px-4 !py-5 text-5xl font-semibold opacity-80 {{$name}}_hh"
                        placeholder="{{$hourLabel}}"
                        enforce_limits="true"
                        onpaste="event.preventDefault();"
                        onkeyup="moveToMinutes('{{$name}}');"/>
            </div>
            <div class="px-3 text-center pt-2.5">
                <div class="block size-3 bg-gray-500 my-4 rounded-full"></div>
                <div class="block size-3 bg-gray-500 rounded-full"></div>
            </div>
            <div>
                <x-input
                        numeric="true"
                        max="59"
                        tabindex="2"
                        data-time-format="{{$format}}"
                        class="w-[105px] text-center border-2 border-gray-200/70 rounded-md !px-2 !py-5 text-5xl font-semibold opacity-80 {{$name}}_mm"
                        selected_value="{{$selected_minute??''}}"
                        placeholder="{{$minuteLabel}}"
                        onpaste="event.preventDefault();"
                        enforce_limits="true"/>
            </div>
            @if($format  == '12')
                <div class="pl-3 space-y-1">
                    <div tabindex="3"
                         class="rounded-t-lg font-semibold cursor-pointer text-2xl px-4 py-2 {{ (!empty($selected_format) && $selected_format == 'AM') ? 'bg-gray-500 text-white' : 'bg-gray-100 hover:bg-gray-300' }} {{$name}}-time-format-am"
                         onclick="toggleFormat('AM', '{{$name}}');">
                        {{ __('bladewind.timepicker_am') }}
                    </div>
                    <div tabindex="4"
                         class="rounded-b-lg font-semibold cursor-pointer text-2xl px-4 py-2 {{ (!empty($selected_format) && $selected_format == 'PM') ? 'bg-gray-500 text-white' : 'bg-gray-100 hover:bg-gray-300' }} {{$name}}-time-format-pm"
                         onclick="toggleFormat('PM', '{{$name}}');">
                        {{ __('bladewind.timepicker_pm') }}
                    </div>
                    <input type="hidden" class="{{$name}}_format bg-gray-500"/>
                </div>
            @endif
        </div>
    </x-modal>
    @once
        <script @if($nonce)nonce="{{$nonce}}"@endif>
            const openTimepicker = (name) => {
                showModal(`${name}`);
                window.setTimeout(() => {
                    domEl(`.${name}_hh`).focus();
                    window.clearTimeout();
                }, 300);
            }

            const toggleFormat = (format, name) => {
                let am = domEl(`.${name}-time-format-am`);
                let pm = domEl(`.${name}-time-format-pm`);
                if (format === 'AM') {
                    changeCss(am, 'bg-gray-500,text-white', 'add', true);
                    changeCss(am, 'bg-gray-100, hover:bg-gray-300', 'remove', true);
                    changeCss(pm, 'bg-gray-500,text-white', 'remove', true);
                    changeCss(pm, 'bg-gray-100, hover:bg-gray-300', 'add', true);
                }
                if (format === 'PM') {
                    changeCss(pm, 'bg-gray-500,text-white', 'add', true);
                    changeCss(pm, 'bg-gray-100, hover:bg-gray-300', 'remove', true);
                    changeCss(am, 'bg-gray-500,text-white', 'remove', true);
                    changeCss(am, 'bg-gray-100, hover:bg-gray-300', 'add', true);
                }
                domEl(`.${name}_format`).value = format;
            }
            const moveToMinutes = (name) => {
                if (domEl(`.${name}_hh`).value.length >= 2) {
                    domEl(`.${name}_mm`).focus();
                }
            }

            const setTime = (name, format) => {
                let field = domEl(`.time-${name}`);
                let suffix = domEl(`.timepicker-${name} .suffix`);

                if (field) {
                    let hour = domEl(`.${name}_hh`).value;
                    hour = (format === '24' && hour.length === 1) ? '0' + hour : hour;
                    let minute = domEl(`.${name}_mm`).value;
                    minute = ':' + ((minute.length === 1) ? '0' + minute : minute);
                    let ampm = domEl(`.${name}_format`).value;
                    let time = `${hour}${minute}${ampm ?? ''}`;

                    if (time.length >= 5) {
                        field.value = time;
                        if (suffix) {
                            suffix.innerHTML = clearIcon.replace('<svg', `<svg onclick="clearTime('${name}')"`);
                        }
                    }
                }
            }

            const clearTime = (name) => {
                let field = domEl(`.time-${name}`);
                let suffix = domEl(`.timepicker-${name} .suffix`);
                field.value = '';
                suffix.innerHTML = clockIcon;
            }
        </script>
    @endonce
@else
    <div class="flex">
        <div>
            <x-select
                    data="manual"
                    onselect="setTime_{{$name}}"
                    :placeholder="$hourLabel"
                    name="{{$name}}_hh"
                    :required="$required"
                    selected_value="{{$selected_hour??''}}">
                @for($hours=1; $hours < (($format=='12') ? 13:24); $hours++)
                    @php $hours = (($format=='12') ? $hours : str_pad($hours, 2, '0', STR_PAD_LEFT))  @endphp
                    <x-select.item label="{{$hours}}" value="{{$hours}}"/>
                @endfor
                @if($format !== '12')
                    <x-select.item label="00" value="00"/>
                @endif
            </x-select>
        </div>
        <div class="!-ml-2">
            <x-select
                    data="manual"
                    onselect="setTime_{{$name}}"
                    :placeholder="$minuteLabel"
                    name="{{$name}}_mm"
                    :required="$required"
                    selected_value="{{$selected_minute??''}}">
                <x-select.item label="00" value="00"/>
                @for($minutes=1; $minutes < 60; $minutes++)
                    @php $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT) @endphp
                    <x-select.item label="{{$minutes}}" value="{{$minutes}}"/>
                @endfor
            </x-select>
        </div>
        @if($format == '12')
            <div class="!-ml-2">
                <x-select
                        data="manual"
                        onselect="setTime_{{$name}}"
                        :placeholder="$formatLabel"
                        name="{{$name}}_format"
                        :required="$required"
                        selected_value="{{$selected_format??''}}">
                    <x-select.item label="{{ __('bladewind.timepicker_am') }}" value="AM"/>
                    <x-select.item label="{{ __('bladewind.timepicker_pm') }}" value="PM"/>
                </x-select>
            </div>
        @endif
    </div>
    <input type="hidden" class="time-{{$name}}" name="{{$name}}" value="{{str_replace(' ', '', $selectedValue)}}"/>
    <x-script :nonce="$nonce">
        const setTime_{{$name}} = () => {
        let field = domEl(`.time-{{$name}}`);
        if (field) {
        let hour = domEl('.{{$name}}_hh').value;
        let minute = ':' + domEl('.{{$name}}_mm').value;
        let format = domEl('.{{$name}}_format').value;
        let time = `${hour}${minute}${format ?? ''}`;
        if (time.length >= 4) {
        field.value = time;
        }
        }
        }
    </x-script>
@endif