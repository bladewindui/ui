{{-- format-ignore-start --}}
@props([
    // this has nothing to do HTML's button types
    // this defines if the button is primary or secondary
    'type' => 'primary',

    // tiny, small, regular, big
    'size' => config('bladewind.button.size', 'regular'),

    // for use with css and js if you want to manipulate the button
    'name' => null,

    // will show a spinner
    'hasSpinner' => false,

    // will show a spinner
    'showSpinner' => false,

    // will make this <button type="submit">
    'canSubmit' => false,

    // set to true to disable the button
    'disabled' => false,

    // what tags to use for drawing the button <a> or <button>
    // available options are a, button
    'tag' => config('bladewind.button.tag', 'button'),

    // button colour
    'color' => '',

    // overwrite the button text color
    'buttonTextCss' => null,

    // icon to display to the left of the button
    'icon' => '',
    'iconType' => 'outline',

    'iconRight' => config('bladewind.button.icon_right', false),

    // should a ring be shown around the button?
    'showFocusRing' => config('bladewind.button.show_focus_ring', true),

    // should the button be only an outline
    'outline' => config('bladewind.button.outline', false),

    // thickness of outline
    // 2, 4, 8
    // becomes border-2, border-4, border-8
    'borderWidth' => config('bladewind.button.border_width', 2),

    // thickness of the ring shown on focus
    // 1, 2, 4, 8
    // becomes ring, ring-1, ring-2, ring-4, ring-8
    'ringWidth' => config('bladewind.button.ring_width', ''),

    // determines how rounded the button should be
    // none, small, medium, full
    'radius' => config('bladewind.button.radius', 'small'),

    // is this a circular button
    'circular' => false,

    // display button text as uppercase or as user entered
    'uppercasing' => config('bladewind.button.uppercasing', true),
])
@php
    $showSpinner = parseBladewindVariable($showSpinner);
    $hasSpinner = parseBladewindVariable($hasSpinner);
    $canSubmit = parseBladewindVariable($canSubmit);
    $outline = parseBladewindVariable($outline);
    $uppercasing = parseBladewindVariable($uppercasing);
    $circular = parseBladewindVariable($circular);
    $showFocusRing = parseBladewindVariable($showFocusRing);

    $roundness  = [
        'none'      => 'rounded-none',
        'small'     => 'rounded-md',
        'medium'    => 'rounded-xl',
        'full'      => 'rounded-full',
    ];

    $icon_size = [
        'circular' => [
            'tiny' => 'size-[16px]!',
            'small' => 'size-[22px]!',
            'regular' => 'size-6!',
            'medium' => 'size-7!',
            'big' => 'size-9!',
        ],
        'tiny' => 'size-3! mt-[-2px]!',
        'small' => 'size-3.5!',
        'regular' => 'size-4!',
        'medium' => 'size-[20px]!',
        'big' => 'size-[25px]!',
    ];

    $colour = (!empty($color)) ? $color : $type;
    $outline_colour =   "border-$colour-500/50 focus:ring-$colour-500 hover:border-$colour-600
                        dark:hover:border-$colour-200 active:border-$colour-600 text-$colour-600
                        dark:text-dark-400 dark:hover:text-dark-300  %s";
    $button_colour =    "bg-$colour-500! hover:bg-$colour-600! focus:ring-$colour-500 active:bg-$colour-600 %s";

    if($colour == 'black') {
        $outline_colour = preg_replace('/(-)?(\/)?\d+/', '', $outline_colour);
        $button_colour = preg_replace('/-\d+/', '', $button_colour);
    }
    $button_type = ($canSubmit) ? 'submit' : 'button';
    $spinner_css =  sprintf(($outline ? 'text-gray-600 dark:text-white %s' : 'text-white %s'), (!$showSpinner) ?  'hidden' : '');
    $focus_ring_width = ($ringWidth !== '' && in_array((int)$ringWidth, [1,2,4,8])) ? '-'.$ringWidth : '';
    $focus_ring_css = (!$showFocusRing) ? 'focus:ring-0 focus:outline-0' : 'focus:ring'.$focus_ring_width;
    $border_width = ' border-'.$borderWidth;
    $primary_colour_css = (($outline) ?
        sprintf($outline_colour,$focus_ring_css.$border_width) :
        sprintf($button_colour,$focus_ring_css)
    );
    $radius_css = $roundness[$radius] ?? 'rounded-full';
    $button_text_colour = (!empty($buttonTextCss)) ? $buttonTextCss : ($outline ? '' : 'text-white/90 hover:text-white');
    $disabled_css = $disabled ? 'disabled' : 'cursor-pointer';
    $outline_css = ($outline) ? 'outlined '.$border_width : '';
    $has_icon_css = (!empty($icon)) ? ' has-icon ' : '';
    $tag = ($tag !== 'a' && $tag !== 'button') ? 'button' : $tag;
    $base_button_css = ($circular) ? 'bw-button-circle' : 'bw-button '.(($uppercasing) ? 'uppercase ' : '');
    $merged_attributes = $attributes->exceptPropAliases(get_defined_vars())->merge(['class' => "$base_button_css $size $type $name $primary_colour_css $disabled_css $radius_css $outline_css $has_icon_css"]);
    // the type has to go through the attribute bag. building it as a string and
    // echoing it through {{ }} escaped the quotes, so the markup read
    // type=&quot;button&quot; and a parser saw the value '"button"' — not a valid
    // keyword, so every button in a form fell back to the submit default. see #600
    if ($tag === 'button') {
        $merged_attributes = $merged_attributes->merge(['type' => $button_type]);
    }
    $icon_css = ($circular) ? $icon_size['circular'][$size] : $icon_size[$size].' dark:text-white/80 ' . ((!$iconRight) ? '-ml-2! rtl:-mr-2! mr-2! rtl:ml-2!' : '-mr-2! rtl:-ml-2! ml-2! rtl:mr-2!');
@endphp
{{-- format-ignore-end --}}

<{{ $tag }}
    {{ $merged_attributes }}
    {{ $disabled ? 'disabled' : ''}}>
    @if(!empty($icon) && !$iconRight)
        <x-bladewind::icon :name="$icon" :type="$iconType" class="stroke-2 {{$icon_css}}" />
    @endif
    @if (!$circular)
        <span class="grow {{ $button_text_colour }}">{{ $slot }}</span>
    @endif
    @if(!empty($icon) && $iconRight && !$hasSpinner)
        <x-bladewind::icon :name="$icon" :type="$iconType" class="stroke-2 {{$icon_css}}" />
    @endif
    @if($hasSpinner)
        <x-bladewind::spinner class="{{$icon_size[$size]}} -mr-2! rtl:-ml-2! ml-2! rtl:mr-2! {{ $spinner_css }}" />
    @endif
</{{$tag}}>