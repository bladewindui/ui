{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName(),
    'icon' => null,
    'avatar' => null,
    'avatarSize' => config('bladewind.checkcards.avatar_size', 'medium'),
    'class' => null,
    'required' => false,
    'max' => 1,
    'compact' => config('bladewind.selectable_card.compact', false),
    'color' => config('bladewind.checkcards.color', 'primary'),
    'radius' => config('bladewind.checkcards.radius', 'medium'),
    'borderWidth' => config('bladewind.checkcards.border_width', 2),
    'borderColor' => config('bladewind.checkcards.border_color', 'gray'),
    'alignItems' => config('bladewind.checkcards.align_items', 'top'),
    'showError' => config('bladewind.checkcards.show_error', false),
    'autoSelectNew' => config('bladewind.checkcards.auto_select_new', true),
    'errorHeading' => config('bladewind.checkcards.error_heading', __('bladewind::bladewind.checkcard_error_heading')),
    'errorMessage' => config('bladewind.checkcards.error_message', __('bladewind::bladewind.checkcard_error_message')),
    'selectedValue' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $required = parseBladewindVariable($required);
    $show_error = parseBladewindVariable($showError);
    $auto_select_new = parseBladewindVariable($autoSelectNew);
    $max_selection = (!empty($max) && is_numeric($max) && $max > 0) ? $max : 1;
    if($show_error) $auto_select_new = false;
@endphp
{{-- format-ignore-end --}}

<div class="bw-checkcards-{{$name}} {{$class}}"
     role="group"
     @if($required) aria-required="true" @endif>
    <x-bladewind::input
            :name="$name"
            :error_message="$errorMessage"
            :error_heading="$errorHeading"
            data-max-selection="{{$max_selection}}"
            data-show-error="{{$show_error}}"
            data-auto-select="{{$auto_select_new}}"
            type="hidden"
            class="{{ ($required) ? 'required':''}}"/>
    {{ $slot }}
</div>