{{-- format-ignore-start --}}
@props([
    // name of the input field for use in forms
    'name' => defaultBladewindName('input-'),

    // label to display on the input box
    'label' => '',

    // minimum number a user can enter when numeric=true
    'min' => 0,

    // maximum number a user can enter when numeric=true
    'max' => 100,

    // by what digit should incrementing be done
    'step' => 1,

    // is this a required field? Default is false
    'required' => false,

    // value to set when in edit mode, or if you want to load the input with default text
    'selectedValue' => null,

    // for numeric input only: should the numbers include dots
    'withDots' => config('bladewind.number.with_dots', true),

    'size' => config('bladewind.number.size', 'medium'),
    'iconType' => config('bladewind.number.icon_type', 'outline'),
    'transparentIcons' => config('bladewind.number.transparent_icons', true),
    'class' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $withDots = parseBladewindVariable($withDots);
    $required = parseBladewindVariable($required);
    $transparentIcons = parseBladewindVariable($transparentIcons);
    $min = !is_numeric($min) ? 0 : $min;
    $max = (!empty($max) && !is_numeric($max)) ? 100 : $max;
    $step = !is_numeric($step) ? 1 : $step;
    $selectedValue = (!empty($selectedValue)) ? $selectedValue : (($min != 0) ? $min : 0);

    $sizes = [
        'small' => [ 'icon' => '!size-4', 'text' => '', 'width' => 'w-36', 'alt_width' => 'w-40'],
        'regular' => [ 'icon' => '!size-6', 'text' => '', 'width' => 'w-36', 'alt_width' => 'w-40'],
        'medium' => [ 'icon' => '!size-8 !stroke-2', 'text' => 'text-3xl tracking-normal leading-none !py-[5px]', 'width' => 'w-40', 'alt_width' => 'w-48'],
        'big' => [ 'icon' => '!size-12 !stroke-1', 'text' => 'text-4xl !py-[8.5px]', 'width' => 'w-48', 'alt_width' => 'w-56'],
    ];
    $size = (in_array($size, ['small','medium', 'regular','big'])) ? $size : 'medium';
@endphp
{{-- format-ignore-end --}}

<span class="inline-flex bw-number-{{$name}} place">
    <x-bladewind::input
            prefix="arrow-down-circle"
            :prefix_is_icon="true"
            :prefix_icon_type="$iconType"
            prefix_icon_css="{{$sizes[$size]['icon'] ?? 'size-6'}} cursor-pointer"
            :transparent_prefix="$transparentIcons"
            suffix="arrow-up-circle"
            :suffix_is_icon="true"
            :suffix_icon_type="$iconType"
            :transparent_suffix="$transparentIcons"
            suffix_icon_css="{{$sizes[$size]['icon']}} cursor-pointer"
            numeric="true"
            :min="$min"
            :max="$max"
            :label="$label"
            :size="$size"
            :enforce_limits="true"
            :required="$required"
            :with_dots="$withDots"
            class="text-center {{$sizes[$size]['text']??''}} font-semibold {{ ($transparentIcons ? $sizes[$size]['width'] : $sizes[$size]['alt_width'])}} {{$class}}"
            :selected_value="$selectedValue"
            :name="$name"/>
</span>
<x-bladewind::script :nonce="$nonce">
    changeCss('.bw-number-{{$name}} .prefix svg', '!size-4,!size-[18px],size-6,!stroke-2', 'remove');
    changeCss('.bw-number-{{$name}} .suffix svg', '!size-4,!size-[18px],size-6,!stroke-2', 'remove');
    domEl('.bw-number-{{$name}} .suffix').addEventListener('click', () => {
    domEl('.bw-number-{{$name}} input.{{$name}}').value = parseInt(domEl('.bw-number-{{$name}} input.{{$name}}').value)
    + parseInt({{$step}});
    checkMinMax('{{$min}}', '{{$max}}', '{{$name}}', 1);
    });
    domEl('.bw-number-{{$name}} .prefix').addEventListener('click', () => {
    domEl('.bw-number-{{$name}} input.{{$name}}').value = parseInt(domEl('.bw-number-{{$name}} input.{{$name}}').value)
    - parseInt({{$step}});
    checkMinMax('{{$min}}', '{{$max}}', '{{$name}}', 1);
    });
</x-bladewind::script>