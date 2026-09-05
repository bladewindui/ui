{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName('bw-meter-'),
    'value' => 0,
    'min' => 0,
    'max' => 100,
    // upper boundary of the "low" zone. giving both low and high divides the
    // range into low/medium/high zones and colours the bar accordingly.
    // omitting either renders a single neutral-coloured bar with no zones —
    // this is a bounded measurement, not a task-completion indicator, so
    // there is no default "done" colour the way Progress Bar has one
    'low' => null,
    // lower boundary of the "high" zone
    'high' => null,
    // which zone counts as the good one. defaults to the high zone (higher
    // is better) when zones are in use and optimum is not given
    'optimum' => null,
    'label' => '',
    'showValue' => config('bladewind.meter.show_value', true),
    // tiny | small | medium | large
    'size' => config('bladewind.meter.size', 'medium'),
    'class' => '',
])
@php
    $name = parseBladewindName($name);
    $showValue = parseBladewindVariable($showValue);
    $size = in_array($size, ['tiny', 'small', 'medium', 'large']) ? $size : 'medium';

    $min = is_numeric($min) ? (float) $min : 0;
    $max = is_numeric($max) ? (float) $max : 100;
    $value = is_numeric($value) ? (float) $value : $min;
    $low = is_numeric($low) ? (float) $low : null;
    $high = is_numeric($high) ? (float) $high : null;
    $optimum = is_numeric($optimum) ? (float) $optimum : null;

    $percent = $max > $min ? max(0, min(100, ($value - $min) / ($max - $min) * 100)) : 0;

    $heights = ['tiny' => 'h-1', 'small' => 'h-1.5', 'medium' => 'h-2', 'large' => 'h-3'];
    $heightClass = $heights[$size];

    $hasZones = $low !== null && $high !== null && $low < $high;

    if ($hasZones) {
        $zoneOf = fn ($v) => $v < $low ? 'low' : ($v >= $high ? 'high' : 'medium');
        $currentZone = $zoneOf($value);
        $goodZone = $optimum !== null ? $zoneOf($optimum) : 'high';
        $badZone = match ($goodZone) {
            'low' => 'high',
            'high' => 'low',
            default => null,
        };

        $barColor = match (true) {
            $currentZone === $goodZone => 'bg-green-500',
            $currentZone === $badZone => 'bg-red-500',
            default => 'bg-yellow-500',
        };
    } else {
        $barColor = 'bg-primary-500';
    }
@endphp
{{-- format-ignore-end --}}
<div class="bw-meter {{ $name }} {{ $class }}">
    @if(! empty($label) || $showValue)
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-dark-400 mb-1">
            <span>{{ $label }}</span>
            @if($showValue)
                <span>{{ rtrim(rtrim(number_format($value, 2), '0'), '.') }} / {{ rtrim(rtrim(number_format($max, 2), '0'), '.') }}</span>
            @endif
        </div>
    @endif
    <div class="relative w-full rounded-full bg-gray-200 dark:bg-dark-700 overflow-hidden {{ $heightClass }}" aria-hidden="true">
        <div class="h-full rounded-full transition-all duration-300 {{ $barColor }}" style="width: {{ $percent }}%"></div>
    </div>
    <meter class="sr-only"
           value="{{ $value }}" min="{{ $min }}" max="{{ $max }}"
           @if($low !== null) low="{{ $low }}" @endif
           @if($high !== null) high="{{ $high }}" @endif
           @if($optimum !== null) optimum="{{ $optimum }}" @endif
           @if(! empty($label)) aria-label="{{ $label }}" @endif>{{ $value }}</meter>
</div>
