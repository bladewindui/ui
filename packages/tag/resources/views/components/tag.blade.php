{{-- format-ignore-start --}}
@props([
    'label' => '',
    'class' => '',
    'canClose' => config('bladewind.tag.can_close', false),
    'addClearing' => config('bladewind.tag.add_clearing', true),
    'onclick' => '',
    'id' => uniqid(),
    'addIdPrefix' => true,
    'value' => null,
    'selectable' => false,
])
@aware([
    'shade' => config('bladewind.tag.shade', 'faint'),
    'color' => config('bladewind.tag.color', 'primary'),
    'rounded' => config('bladewind.tag.rounded', false),
    'outline' => config('bladewind.tag.outline', false),
    'max' => null,
    'name' => null,
    'required' => false,
    'uppercasing' => config('bladewind.tag.uppercasing', true),
    'tiny' => false,
])
@php
    $canClose = parseBladewindVariable($canClose);
    $addIdPrefix = parseBladewindVariable($addIdPrefix);
    $rounded = parseBladewindVariable($rounded);
    $addClearing = parseBladewindVariable($addClearing);
    $outline = parseBladewindVariable($outline);
    $tiny = parseBladewindVariable($tiny);
    $uppercasing = parseBladewindVariable($uppercasing);

    $colour_weight = [
        'faint' => [ 'bg' => '100/70', 'border' => 200 ],
        'dark' => [ 'bg' => 500, 'border' => '500/50' ],
    ];

    $text_colour_weight = [
        'faint' => 600,
        'dark' => 50,
    ];

    $rounded_class = ($rounded) ? 'rounded-full' : 'rounded-md';
    $clearing_css = ($addClearing) ? 'mb-3' : '';
    $bg_colour_weight = $colour_weight[$shade]['bg'];
    $border_colour_weight = $colour_weight[$shade]['border'];
    $bg_border_colour_css = ($outline) ?
        "border border-$color-$border_colour_weight" :
        "bg-$color-$bg_colour_weight text-$color-$text_colour_weight[$shade]";
    $text_color_css = ($outline) ? "text-$color-600 dark:!text-$color-300" : "text-$color-$text_colour_weight[$shade]";
    if( (!empty($name) && !empty($value)) ) {
        $canClose = false;
        $value = str_replace(' ', '-', $value);
        $bg_border_colour_css = "bg-$color-200/80 hover:bg-$color-600 cursor-pointer selectable bw-$name-$value";
        $text_color_css = "text-$color-600 hover:text-$color-50";
        $selectable = true;
    }
@endphp
{{-- format-ignore-end --}}

<label id="@if($addIdPrefix)bw-@endif{{$id}}" @if($selectable) data-bw-tag-value="{{$value}}" data-bw-tag-name="{{$name}}" @endif
class="relative  @if($uppercasing) uppercase @endif @if($tiny) text-[9px] px-[8px] leading-5 @else px-[12px] leading-8 text-[10px] @endif tracking-widest whitespace-nowrap inline-block {{$rounded_class}} {{$clearing_css}} {{$bg_border_colour_css}} {{$text_color_css}} {{$class}}">
    {{ $label }}
    @if($canClose)
        <a href="javascript:void(0)" @if($onclick=='') data-bw-tag-remove @else onclick="{!!$onclick!!}" @endif>
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="-mt-0.5 -mr-1 h-5 w-5 p-1 opacity-70 hover:opacity-100 inline {{$text_color_css}}" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </a>
    @endif
</label>