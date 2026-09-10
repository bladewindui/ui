{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName('bw-slider-'),

    // accessible name for the range input(s). a slider with no name reaches a
    // screen reader as an anonymous control. see #597
    'ariaLabel' => __('bladewind::bladewind.slider_label'),
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'selected' => 0,
    'maxSelected' => '',
    'color' => 'primary',
    'showValues' => config('bladewind.slider.show_values', true),
    'range' => config('bladewind.slider.range', false),
    'class' => '',
    'nonce' => config('bladewind.script.nonce', null),

])

@php
    $showValues = parseBladewindVariable($showValues);
    $range = parseBladewindVariable($range);
    $name = parseBladewindName($name);

    $step = (is_numeric($step) && $step > 0) ? $step : 1;
    $min = (is_numeric($min) && $min >= 0) ? $min : 0;
    $max = (is_numeric($max) && $max >= 0) ? $max : 100;
    $selected = (is_numeric($selected) && $selected >= 0) ? $selected : 0;
    $selected = ($min != 0 && $selected== 0) ? $min : $selected;
    $selected = ($selected > $max) ? $max : $selected;
    $colour = defaultBladewindColour($color);
@endphp
{{-- format-ignore-end --}}

<div class="bw-slider-container w-full relative {{$class}}">
    <input type="range" aria-label="{{ $ariaLabel }}"
           min="{{$min}}"
           max="{{$max}}"
           value="{{$selected}}"
           step="{{$step}}"
           class="bw-slider min-slider-{{$name}} {{$colour}}"/>
    @if($range)
        <input type="range" aria-label="{{ $ariaLabel }}"
               min="{{$min}}"
               max="{{$max}}"
               value="{{$maxSelected}}"
               step="{{$step}}"
               class="bw-slider max-slider-{{$name}} {{$colour}}"/>
    @endif

    <div class="text-center pt-5 text-sm font-semibold @if(!$showValues) hidden @endif">
        <span class="slider-selection-{{$name}}">{{$selected}}
            @if($range)
                - {{$maxSelected}}
            @endif
        </span>
    </div>
    <input type="hidden"
           name="{{$name}}"
           id="{{$name}}"
           class="slider-selection-{{$name}}-input bw-slider-{{$name}}"
           value="{{$selected}}@if($range),{{$maxSelected}}@endif"/>
</div>

<x-bladewind::script :nonce="$nonce">
    const minSlider_{{$name}} = domEl('.min-slider-{{$name}}');
    const maxSlider_{{$name}} = domEl('.max-slider-{{$name}}');
    const sliderValue_{{$name}} = domEl('.slider-selection-{{$name}}');
    const sliderInput_{{$name}} = domEl('.slider-selection-{{$name}}-input');

    minSlider_{{$name}}.oninput = function () {
    if (parseInt(minSlider_{{$name}}.value) > parseInt(maxSlider_{{$name}}.value) - 1) {
    minSlider_{{$name}}.value = parseInt(maxSlider_{{$name}}.value) - 1;
    }
    @if($range)
        sliderValue_{{$name}}.innerHTML = `${minSlider_{{$name}}.value} - ${maxSlider_{{$name}}.value}`;
        setFieldValue(sliderInput_{{$name}}, `${minSlider_{{$name}}.value},${maxSlider_{{$name}}.value}`);
    @else
        sliderValue_{{$name}}.innerHTML = `${minSlider_{{$name}}.value}`;
        setFieldValue(sliderInput_{{$name}}, `${minSlider_{{$name}}.value}`);
    @endif
    };
    @if($range)
        maxSlider_{{$name}}.oninput = function () {
        if (parseInt(maxSlider_{{$name}}.value) < parseInt(minSlider_{{$name}}.value) + 1) {
        maxSlider_{{$name}}.value = parseInt(minSlider_{{$name}}.value) + 1;
        }
        sliderValue_{{$name}}.innerHTML = `${minSlider_{{$name}}.value} - ${maxSlider_{{$name}}.value}`;
        setFieldValue(sliderInput_{{$name}}, `${minSlider_{{$name}}.value},${maxSlider_{{$name}}.value}`);
        };
    @endif
</x-bladewind::script>