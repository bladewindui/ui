{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName(),
    'value' => null,
    'label' => null,
    'checked' => false,
    'disabled' => false,
    'type' => 'checkbox',
    'class' => 'rounded-md',
    'labelCss' => '',
    'color' => config('bladewind.checkbox.color', 'primary'),
    'addClearing' => config('bladewind.checkbox.add_clearing', true),
    // repopulate this field from old() after a validation redirect
    'fillFromOld' => config('bladewind.forms.fill_from_old', false),

    // give the field its error state and render $errors->first() beneath it
    'showValidationError' => config('bladewind.forms.show_validation_error', false),

    // which error bag to read; null uses Laravel's default
    'errorBag' => config('bladewind.forms.error_bag', null),
])
@php
    $name = parseBladewindName($name);
    $checked = parseBladewindVariable($checked);
    $disabled = parseBladewindVariable($disabled);
    $colour = defaultBladewindColour($color);
    $addClearing = parseBladewindVariable($addClearing);
    $text_colour = ($colour == 'black') ? 'text-black' : "text-$colour-600 dark:bg-dark-800";
    $ring_colour = ($colour == 'black') ? 'ring-black/25' : "ring-$colour-500/25 dark:ring-dark-500/25";
    $border_colour = ($colour == 'black') ? 'border-slate-500/50' : "border-$colour-500/50 dark:border-dark-500/80";

    $fillFromOld = parseBladewindVariable($fillFromOld);
    $showValidationError = parseBladewindVariable($showValidationError);
    // a checkbox is checked when the flashed value matches its own, which for a
    // group arrives as an array. an unticked box submits nothing, so absence only
    // means unchecked once we know a submission actually bounced back — otherwise
    // a first render would silently clear a box set with checked="true".
    if (bladewindHasOldInput($fillFromOld)) {
        $old_checked = bladewindOldInput($name, null, true);
        $checked = is_array($old_checked)
            ? in_array((string) $value, array_map('strval', $old_checked), true)
            : $old_checked !== null && (string) $old_checked === (string) $value;
    }
    $validation_error = bladewindValidationError($name, $showValidationError, $errorBag);
@endphp
{{-- format-ignore-end --}}

<label class="inline-flex items-center cursor-pointer text-sm @if($disabled) opacity-60 @endif @if($addClearing) mb-3 @endif {{ $labelCss }}">
    <input
            type="{{ $type }}"
            name="{{ $name }}"
            class="{{$text_colour}} size-6 @if($addClearing) mr-2 rtl:ml-2 @endif disabled:opacity-50 focus:{{$ring_colour}} border-2 {{$border_colour}} bw-checkbox {{$class}}"
            @if($disabled) disabled @endif
            @if($checked) checked @endif
            value="{{ $value }}"
    />
    {!! $label !!}
</label>
@if($validation_error !== '')
    <div class="text-red-500 text-xs p-1 {{ $name }}-validation-error">{{ $validation_error }}</div>
@endif
