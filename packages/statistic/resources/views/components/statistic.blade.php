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

    // named tone for the note and the trend. the colour map is owned by the
    // library so the same sentence cannot read as a warning on one page and as
    // description on another.
    // neutral, positive, negative, warning, info
    'tone' => config('bladewind.statistic.tone', 'neutral'),

    // trend arrow shown beside the figure. up, down, flat
    'direction' => '',

    // for metrics where down is good — arrears, churn, cost per unit.
    // swaps which direction counts as positive.
    'invertDirection' => config('bladewind.statistic.invert_direction', false),

    // short sentence under the figure, coloured by tone (or by direction)
    'note' => '',

    // explanatory text shown on hover next to the label
    'hint' => '',

    // 0-100. renders a progress bar in place of the note
    'progress' => null,
    'progressLabel' => '',
])
@php
    $showSpinner = parseBladewindVariable($showSpinner);
    $hasShadow = parseBladewindVariable($hasShadow);
    $hasBorder = parseBladewindVariable($hasBorder);

    $shadow_css = ($hasShadow) ? 'shadow-sm shadow-black/5 dark:shadow-dark-800/70' : '';
    $border_css = ($hasBorder) ? 'border border-neutral-200 dark:border-dark-600/60 focus:outline-none' : '';
    $hover_css =  (!empty($url)) ? 'hover:border hover:border-neutral-400/70 hover:shadow-sm hover:shadow-black/5 hover:dark:shadow-dark-900 cursor-pointer' : '';
    $radius_css = getRadiusString($radius);

    $invertDirection = parseBladewindVariable($invertDirection);

    // the library owns this map. that is the whole point of the tone prop: the
    // consuming app had a copy of it per stat card and they drifted.
    $tones = [
        'neutral' => 'text-gray-500 dark:text-dark-400',
        'positive' => 'text-green-600 dark:text-green-500',
        'negative' => 'text-red-600 dark:text-red-500',
        'warning' => 'text-yellow-600 dark:text-yellow-500',
        'info' => 'text-blue-600 dark:text-blue-500',
    ];

    $direction = in_array($direction, ['up', 'down', 'flat']) ? $direction : '';

    // which direction reads as good. inverted for metrics where down is better.
    $direction_tone = match($direction) {
        'up' => $invertDirection ? 'negative' : 'positive',
        'down' => $invertDirection ? 'positive' : 'negative',
        'flat' => 'neutral',
        default => '',
    };

    // an explicit tone always wins; otherwise the trend colours itself
    $effective_tone = array_key_exists($tone, $tones) && $tone !== 'neutral'
        ? $tone
        : ($direction_tone ?: 'neutral');

    $tone_css = $tones[$effective_tone];

    $direction_icon = match($direction) {
        'up' => 'arrow-trending-up',
        'down' => 'arrow-trending-down',
        'flat' => 'minus',
        default => '',
    };

    $progress = is_numeric($progress) ? max(0, min(100, (float) $progress)) : null;

    // the bar reuses the tone so a KPI reads the same way in both forms
    $progress_bar_css = [
        'neutral' => 'bg-gray-400 dark:bg-dark-500',
        'positive' => 'bg-green-500',
        'negative' => 'bg-red-500',
        'warning' => 'bg-yellow-500',
        'info' => 'bg-blue-500',
    ][$effective_tone];

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
                <div class="uppercase tracking-wider text-xs text-gray-500/90 mb-1 label">{!! $label!!}@include('bladewind::components.statistic-hint')</div>
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
                @if($direction_icon !== '')
                    <x-bladewind::icon
                            :name="$direction_icon"
                            class="direction size-5 ml-1 -mt-1 stroke-2 {{ $tone_css }}"/>
                @endif
            </div>
            @if($labelPosition=='bottom')
                <div class="uppercase tracking-wider text-xs text-gray-500/90 mt-1 label">{!! $label!!}@include('bladewind::components.statistic-hint')</div>
            @endif
            @if(! is_null($progress))
                <div class="progress mt-2">
                    @if($progressLabel !== '')
                        <div class="flex justify-between text-xs text-gray-500/90 dark:text-dark-400 mb-1">
                            <span class="progress-label">{!! $progressLabel !!}</span>
                            <span class="progress-value">{{ rtrim(rtrim(number_format($progress, 1, '.', ''), '0'), '.') }}%</span>
                        </div>
                    @endif
                    <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-dark-700 overflow-hidden">
                        <div class="h-full rounded-full {{ $progress_bar_css }}" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            @elseif($note !== '')
                <div class="note text-xs mt-1 {{ $tone_css }}">{!! $note !!}</div>
            @endif
            {{ $slot }}
        </div>
        @if($icon !== '' && $iconPosition=='right')
            <div class="grow-0 icon">{!! $icon !!}</div>
        @endif
    </div>
</div>