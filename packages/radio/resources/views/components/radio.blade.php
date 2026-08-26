{{-- format-ignore-start --}}
@props([
    // to create a radio button group, specify the same name
    // for all the radio buttons in the group
    'name' => 'radio',
    'value' => '',
    'label' => '',
    'labelCss' => 'mr-6',
    'color' => 'primary',
    'checked' => false,
    'addClearing' => config('bladewind.radio_button.add_clearing', true),
    'disabled' => false,
    'class' => '',
    // repopulate this field from old() after a validation redirect
    'fillFromOld' => config('bladewind.forms.fill_from_old', false),

    // give the field its error state and render $errors->first() beneath it
    'showValidationError' => config('bladewind.forms.show_validation_error', false),

    // which error bag to read; null uses Laravel's default
    'errorBag' => config('bladewind.forms.error_bag', null),
])
@php
    $checked = parseBladewindVariable($checked);
    $disabled = parseBladewindVariable($disabled);
    $addClearing = parseBladewindVariable($addClearing);
    $name = parseBladewindName($name);
@endphp
{{-- format-ignore-end --}}

<x-bladewind::checkbox
        name="{{$name}}"
        label="{{$label}}"
        value="{{$value}}"
        color="{{$color}}"
        class="rounded-full {{$class}}"
        label_css="{{$labelCss}}"
        disabled="{{$disabled}}"
        checked="{{$checked}}"
        add_clearing="{{$addClearing}}"
        :fill_from_old="$fillFromOld"
        :show_validation_error="$showValidationError"
        :error_bag="$errorBag"
        type="radio"/>