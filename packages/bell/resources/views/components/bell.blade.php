{{-- format-ignore-start --}}
@props([
    'size'          => config('bladewind.bell.size', 'small'),
    'showDot'       => config('bladewind.bell.show_dot', true),
    'animateDot'    => config('bladewind.bell.animate_dot', false),
    'invert'        => false,
    'color'         => config('bladewind.bell.color', 'primary'),
])
@php
    // reset variables for Laravel 8 support
    $showDot = parseBladewindVariable($showDot);
    $animateDot = parseBladewindVariable($animateDot);
    $invert = parseBladewindVariable($invert);
    $invert_css = ($invert) ? '!text-white' : '';

    $sizing = [
        'small' => [
            'bell' => 'size-6',
            'dot' => 'size-[9px]'
        ],
        'big' => [
            'bell' => '!size-10',
            'dot' => '!size-4'
        ],
    ];

    $size = (! in_array($size, ['small','big'])) ? 'small' : $size;
    $colour = defaultBladewindColour($color);
@endphp
{{-- format-ignore-end --}}

<div {{ $attributes->exceptPropAliases(get_defined_vars())->merge(['class' => "bw-bell relative inline-block"]) }}>
    <x-bladewind::icon name="bell" class="{{ $sizing[$size]['bell'] }} cursor-pointer {{$invert_css}}"/>
    @if($showDot)
        <div class="{{ $sizing[$size]['dot'] }} rounded-full bg-{{ $colour}}-500 absolute top-0 ltr:right-[2.5px] rtl:left-[2.5px] @if($animateDot) animate-ping @endif"></div>
    @endif
</div>