{{-- format-ignore-start --}}
@props([
    'transparent' => false,
    'percentage' => 0,
    'color' => 'primary',
    'showPercentageLabel' => config('bladewind.progress_bar.show_percentage_label', false),
    'showPercentageLabelInline' => config('bladewind.progress_bar.show_percentage_label_inline', true),
    'percentageLabelPosition' => 'top-left',
    'shade' => config('bladewind.progress_bar.shade', 'faint'),
    'percentagePrefix' => '',
    'percentageSuffix' => '',
    'class' => '',
    'barClass' => '',
    'cssOverride' => '',
    'percentageLabelOpacity' => config('bladewind.progress_bar.percentage_label_opacity', 100),
    'striped' => false,
    'animated' => false,
])

@php
    $showPercentageLabel = parseBladewindVariable($showPercentageLabel);
    $striped = parseBladewindVariable($striped);
    $animated = parseBladewindVariable($animated);
    $showPercentageLabel = parseBladewindVariable($showPercentageLabel);
    $showPercentageLabelInline = parseBladewindVariable($showPercentageLabelInline);
    $transparent = parseBladewindVariable($transparent);
    if(! is_numeric($percentageLabelOpacity*1)) $percentageLabelOpacity = '100';

    $colour = defaultBladewindColour($color);
    $bar_colour = ($shade == 'dark') ? "bg-$colour-500" : "bg-$colour-300";
    $percentageLabelPosition = str_replace(' ', '_', $percentageLabelPosition);

    $text_colour_weight = [
        'faint' => 600,
        'dark' => 50,
    ];
@endphp
{{-- format-ignore-end --}}

<div class="bw-progress-bar {{$class}}">
    @if($showPercentageLabel &&
        !$showPercentageLabelInline &&
        str_contains($percentageLabelPosition, 'top'))
        <div class="text-xs tracking-wider {{str_replace('top-','text-', $percentageLabelPosition)}}">
            {{$percentagePrefix}} <span
                    class="opacity-{{$percentageLabelOpacity}}">{{ $percentage}}%</span> {{$percentageSuffix}}
        </div>
    @endif
    <div @class([
        'mt-1 my-2 rounded-full h-2',
        '!h-6' => ($showPercentageLabel && $showPercentageLabelInline),
        'bg-slate-200/70 dark:bg-dark-800/70 w-full' => (!$transparent)
        ])>
        <div style="width: {{$percentage}}%"
             class="text-center {{$bar_colour}} {{$cssOverride}} relative overflow-hidden h-full rounded-full bar-width animate__animated animate__fadeIn flex items-center justify-center {{$barClass}}">
            @if($showPercentageLabel && $showPercentageLabelInline)
                <span class="text-{{$colour}}-{{$text_colour_weight[$shade]}} dark:text-dark-600 px-2 text-xs leading-none">
            {{$percentagePrefix}} <span class="opacity-{{$percentageLabelOpacity}}">{{ $percentage}}%</span> {{$percentageSuffix}}
            </span>
            @endif
            @if($striped)
                <div class="striped @if($animated) animated @endif absolute inset-0"></div>
            @endif
        </div>
    </div>
    @if($showPercentageLabel &&
        !$showPercentageLabelInline &&
        str_contains($percentageLabelPosition, 'bottom'))
        <div class="text-xs tracking-wider {{str_replace('bottom-','text-', $percentageLabelPosition)}}">
            {{$percentagePrefix}} <span
                    class="opacity-{{$percentageLabelOpacity}}">{{ $percentage}}%</span> {{$percentageSuffix}}
        </div>
    @endif
</div>