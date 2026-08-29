{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName('bw-stepper-'),
    'current' => null,
    'orientation' => config('bladewind.stepper.orientation', 'horizontal'),
    'style' => config('bladewind.stepper.style', 'circles'),
    'linear' => config('bladewind.stepper.linear', true),
    'clickable' => config('bladewind.stepper.clickable', true),
    'showNumbers' => config('bladewind.stepper.show_numbers', true),
    'completedIcon' => config('bladewind.stepper.completed_icon', 'check'),
    'errorIcon' => config('bladewind.stepper.error_icon', 'exclamation-triangle'),
    'iconType' => config('bladewind.stepper.icon_type', 'outline'),
    'iconDir' => config('bladewind.stepper.icon_dir', ''),
])
@php
    $linear = parseBladewindVariable($linear);
    $clickable = parseBladewindVariable($clickable);
    $showNumbers = parseBladewindVariable($showNumbers);
    $orientation = in_array($orientation, ['horizontal', 'vertical'], true) ? $orientation : 'horizontal';
    $style = in_array($style, ['circles', 'chevrons', 'bars', 'line'], true) ? $style : 'circles';
    if ($orientation === 'vertical' && $style === 'chevrons') $style = 'circles';
    $safeName = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $name);
@endphp
{{-- format-ignore-end --}}

<nav {{ $attributes->exceptPropAliases(get_defined_vars())->merge([
        'aria-label' => 'Progress',
        'class' => 'bw-stepper bw-stepper-'.$orientation.' bw-stepper-style-'.$style,
    ]) }}
    data-bw-stepper="{{ $name }}"
    data-name="{{ $name }}"
    data-current="{{ $current }}"
    data-initial-current="{{ $current }}"
    data-orientation="{{ $orientation }}"
    data-style="{{ $style }}"
    data-linear="{{ $linear ? 'true' : 'false' }}"
    data-clickable="{{ $clickable ? 'true' : 'false' }}"
    data-show-numbers="{{ $showNumbers ? 'true' : 'false' }}">
    <ol class="bw-stepper-list" role="list">
        {{ $slot }}
    </ol>
</nav>
