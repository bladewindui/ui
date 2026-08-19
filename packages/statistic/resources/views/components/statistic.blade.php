{{-- format-ignore-start --}}
@props([
    'number' => '',
    'labelPosition' => config('bladewind.statistic.label_position', 'top'),
    'iconPosition' => config('bladewind.statistic.icon_position', 'left'),
    'currencyPosition' => config('bladewind.statistic.currency_position', 'left'),
    'label' => '',
    'icon' => '',
    'currency' => config('bladewind.statistic.currency', ''),
    'showSpinner' => false,
    'hasShadow' => config('bladewind.statistic.has_shadow', true),
    'hasBorder' => config('bladewind.statistic.has_border', true),
    'class' => '',
    'numberCss' => '',
    'url' => null,
    'radius' => config('bladewind.statistic.radius', 'small'),
])
@php
    $showSpinner = parseBladewindVariable($showSpinner);
    $hasShadow = parseBladewindVariable($hasShadow);
    $hasBorder = parseBladewindVariable($hasBorder);

    $shadow_css = ($hasShadow) ? 'shadow-sm shadow-black/5 dark:shadow-dark-800/70' : '';
    $border_css = ($hasBorder) ? 'border border-neutral-200 dark:border-dark-600/60 focus:outline-none' : '';
    $hover_css =  (!empty($url)) ? 'hover:border hover:border-neutral-400/70 hover:shadow-sm hover:shadow-black/5 hover:dark:shadow-dark-900 cursor-pointer' : '';
    $radius_css = getRadiusString($radius);

    $classes = implode(' ', array_filter([
        'bw-statistic bg-white dark:bg-dark-800/30 focus:outline-none p-6 relative',
        $shadow_css,
        $border_css,
        $hover_css,
        $radius_css,
        $class
    ]));

    if(!empty($url)) {
        if(str_contains($url, '(') && str_contains($url, ')')) {
            $redirect = "javascript:$url";
        } elseif (str_starts_with($url, 'http')){
            $redirect = "window.open('".addslashes($url)."')";
        } else {
            $redirect = "location.href='".addslashes($url)."'";
        }
    }
@endphp
{{-- format-ignore-end --}}

<div {{ $attributes->exceptPropAliases(get_defined_vars())->merge(['class' => $classes])}} @if($url) onclick="{!! $redirect !!}" @endif>
    <div class="flex space-x-4">
        @if($icon !== '' && $iconPosition=='left')
            <div class="grow-0 icon">{!! $icon !!}</div>
        @endif
        <div class="grow number">
            @if($labelPosition=='top')
                <div class="uppercase tracking-wider text-xs text-gray-500/90 mb-1 label">{!! $label!!}</div>
            @endif
            <div class="text-3xl text-gray-500/90 font-light">
                @if($showSpinner)
                    <x-bladewind::spinner></x-bladewind::spinner>
                @endif
                @if($currency!=='' && $currencyPosition == 'left')
                    <span class="text-gray-300 dark:text-slate-600 mr-1 text-2xl">{!!$currency!!}</span>
                @endif<span
                        class="figure tracking-wider dark:text-slate-400 font-semibold {{$numberCss}}">{{ $number }}</span>@if($currency!=='' && $currencyPosition == 'right')
                    <span class="text-gray-300 dark:text-slate-600 ml-1 text-2xl">{!!$currency!!}</span>
                @endif
            </div>
            @if($labelPosition=='bottom')
                <div class="uppercase tracking-wider text-xs text-gray-500/90 mt-1 label">{!! $label!!}</div>
            @endif
            {{ $slot }}
        </div>
        @if($icon !== '' && $iconPosition=='right')
            <div class="grow-0 icon">{!! $icon !!}</div>
        @endif
    </div>
</div>