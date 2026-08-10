{{-- format-ignore-start --}}
@props([
    // title of the card
    'title' => null,

    // should the card be displayed with a shadow
    'hasShadow' => config('bladewind.card.has_shadow', true),

    // display a border around the card. useful on white backgrounds
    'hasBorder' => config('bladewind.card.has_border', true),

    // display a thicker shadow on hover
    'hasHover' => config('bladewind.card.has_hover', false),

    // reduce padding within the card
    'compact' => config('bladewind.card.compact', false),

    'noPadding' => config('bladewind.card.no_padding', false),

    // content to display as card header
    'header' => null,

    // content to display as card footer
    'footer' => null,

    // additional css
    'class' => null,

    'url' => null,

    // the contact card uses this card component but needs to have a different class name
    'isContactCard' => false,
    'radius' => config('bladewind.card.radius', 'small'),
])
@php
    $compact = parseBladewindVariable($compact);
    $hasShadow = parseBladewindVariable($hasShadow);
    $hasHover = parseBladewindVariable($hasHover);
    $hasBorder = parseBladewindVariable($hasBorder);
    $isContactCard = parseBladewindVariable($isContactCard);
    $noPadding = parseBladewindVariable($noPadding);

    $radius_css = getRadiusString($radius);
    $class = "bg-white dark:bg-dark-800/25 $radius_css $class";
    $contact_card_css =   ($isContactCard) ? 'bw-contact-card' : 'bw-card';
    $has_border_css =   ($hasBorder) ? 'border border-neutral-200 dark:border-dark-600/60 focus:outline-none' : '';
    $header_compact_css =   (!$header && ! $compact && !$noPadding) ? 'p-6' : (($compact) ? 'p-4' : '');
    $shadow_css =   ($hasShadow) ? 'shadowed' : '';
    $hover_css =  ($hasHover || !empty($url)) ? 'shadowed-hover hover:border hover:border-neutral-400/70 cursor-pointer' : '';

    $classes = implode(' ', array_filter([
        $class,
        $contact_card_css,
        $has_border_css,
        $header_compact_css,
        $shadow_css,
        $hover_css
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

<div {{ $attributes->merge([ 'class' => $classes]) }} @if($url) onclick="{!! $redirect !!}" @endif>
    @if($header)
        <div class="border-b border-gray-100/30 dark:border-dark-600/60">
            {{ $header }}
        </div>
    @endif
    @if($title && ! $header)
        <div class="uppercase tracking-wide text-sm text-gray-500 mb-2 antialiased">{{ $title }}</div>
    @endif
    <div @if($title && ! $header) class="mt-6" @endif>
        {{ $slot }}
    </div>
    @if($footer)
        <div class="border-t border-gray-100/30 dark:border-dark-600/60">
            {{$footer}}
        </div>
    @endif
</div>
