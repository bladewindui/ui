{{-- format-ignore-start --}}
@props([
    // allow date range selection
    'range' => false,

    // name of the datepicker. This name is used when posting the form with the datepicker
    'name' => defaultBladewindName(),

    // default date to fill in to the datepicker.
    'selectedValue' => '',

    // date format.. default is yyyy-mm-dd
    // accepted formats are yyyy-mm-dd, mm-dd-yyyy, dd-mm-yyyy, D d M, Y
    'format' => config('bladewind.datepicker.format', 'yyyy-mm-dd'),

    // text to display in the label that identifies the input field
    'label' => config('bladewind.datepicker.label', 'Select a date'),

    // placeholder text to display if datepicker is empty
    'placeholder' => config('bladewind.datepicker.placeholder', 'Select a date'),

    // is the value of the date field required? used for form validation
    'required' => false,

    'tabindex' => -1,

    // first day of the week
    'weekStarts' => 'sunday',

    // any extra classes for the datepicker
    'class' => '',

    // size of the input field
    'size' => 'medium',

    // calendar dates start from
    'minDate' => '',

    // calendar dates end at
    'maxDate' => '',

    'nonce' => config('bladewind.script.nonce', null),
    // repopulate this field from old() after a validation redirect
    'fillFromOld' => config('bladewind.forms.fill_from_old', false),

    // give the field its error state and render $errors->first() beneath it
    'showValidationError' => config('bladewind.forms.show_validation_error', false),

    // which error bag to read; null uses Laravel's default
    'errorBag' => config('bladewind.forms.error_bag', null),
])
@php
    $name = parseBladewindName($name);
    $required = parseBladewindVariable($required);
    $weekStarts = in_array($weekStarts, ['sunday','monday']) ? $weekStarts : 'sunday';
@endphp
{{-- format-ignore-end --}}

<div class="relative w-full">
    <x-bladewind::input
            class="{{$name}} {{$class}}"
            type="text"
            id="{{ $name }}"
            name="{{$name}}"
            label="{{$label}}"
            placeholder="{{ $placeholder }}"
            size="{{$size}}"
            suffix="calendar-days"
            suffix_is_icon="true"
            selected_value="{{$selectedValue}}"
            suffix_icon_div_css="rtl:!right-[unset] rtl:!left-0"
            suffix_icon_css="text-slate-300"
            :fill_from_old="$fillFromOld"
            :show_validation_error="$showValidationError"
            :error_bag="$errorBag"
            required="{{$required}}"/>
</div>
@once
    <span class="bw-datepicker-right-arrow hidden"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke-width="1.5" stroke="currentColor"
                                                        class="size-6 cursor-pointer opacity-80 hover:opacity-100"><path
                    stroke-linecap="round" stroke-linejoin="round"
                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg></span>
    <span class="bw-datepicker-left-arrow hidden"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                       viewBox="0 0 24 24"
                                                       stroke-width="1.5" stroke="currentColor"
                                                       class="size-6 cursor-pointer opacity-80 hover:opacity-100">
  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
</svg></span>
    <x-bladewind::script :nonce="$nonce">
        const MONTH_NAMES = {
        jan: '{{ __('bladewind::bladewind.jan') }}',
        feb: '{{ __('bladewind::bladewind.feb') }}',
        mar: '{{ __('bladewind::bladewind.mar') }}',
        apr: '{{ __('bladewind::bladewind.apr') }}',
        may: '{{ __('bladewind::bladewind.may') }}',
        jun: '{{ __('bladewind::bladewind.jun') }}',
        jul: '{{ __('bladewind::bladewind.jul') }}',
        aug: '{{ __('bladewind::bladewind.aug') }}',
        sep: '{{ __('bladewind::bladewind.sep') }}',
        oct: '{{ __('bladewind::bladewind.oct') }}',
        nov: '{{ __('bladewind::bladewind.nov') }}',
        dec: '{{ __('bladewind::bladewind.dec') }}',
        };
        const DAY_NAMES = {
        sun: '{{ __('bladewind::bladewind.sun') }}'.substring(0, 3),
        mon: '{{ __('bladewind::bladewind.mon') }}'.substring(0, 3),
        tue: '{{ __('bladewind::bladewind.tue') }}'.substring(0, 3),
        wed: '{{ __('bladewind::bladewind.wed') }}'.substring(0, 3),
        thu: '{{ __('bladewind::bladewind.thu') }}'.substring(0, 3),
        fri: '{{ __('bladewind::bladewind.fri') }}'.substring(0, 3),
        sat: '{{ __('bladewind::bladewind.sat') }}'.substring(0, 3)
        };
    </x-bladewind::script>
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/datepicker.js') }}"></x-bladewind::script>
@endonce
<x-bladewind::script :nonce="$nonce">
    initCalendar({
    inputId: "{{$name}}",
    weekStarts: "{{$weekStarts}}",
    @if(!empty($minDate))
        minDate: "{{$minDate}}",
    @endif @if(!empty($maxDate))
        maxDate: "{{$maxDate}}",
    @endif dateFormat: "{{$format}}",
    useRange: {{$range ? 1 : 0}}
    });
</x-bladewind::script>
