{{-- format-ignore-start --}}
@props([
    // text to display inside the tooltip bubble
    'text' => '',
    // where the bubble appears: top | bottom | left | right
    'position' => config('bladewind.tooltip.position', 'top'),
    // dark (inverted) or light bubble
    'color' => config('bladewind.tooltip.color', 'dark'),
    // tiny | small | regular
    'size' => config('bladewind.tooltip.size', 'small'),
    // additional css classes to add to the wrapper
    'class' => '',
])
@php
    $position = (! in_array($position, ['top', 'bottom', 'left', 'right'])) ? 'top' : $position;
    $color    = (! in_array($color, ['dark', 'light'])) ? 'dark' : $color;
    $size     = (! in_array($size, ['tiny', 'small', 'regular'])) ? 'small' : $size;

    $data_position = [
        'top'    => 'top center',
        'bottom' => 'bottom center',
        'left'   => 'left center',
        'right'  => 'right center',
    ][$position];
@endphp
{{-- format-ignore-end --}}
<span
    {{ $attributes->exceptPropAliases(get_defined_vars())->merge(['class' => "bw-tooltip inline-block {$class}"]) }}
    @if(! empty($text))
        data-tooltip="{{ $text }}"
        data-position="{{ $data_position }}"
        data-size="{{ $size }}"
        @if($color === 'dark') data-inverted @endif
    @endif
>{{ $slot }}</span>
