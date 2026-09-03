{{-- format-ignore-start --}}
@props([
    'image' => null,
    'alt' => 'image',
    'class' => '',
    'dotPosition' => config('bladewind.avatar.dot_position', 'bottom'),
    'dotColor' => config('bladewind.avatar.dot_color', 'primary'),
    'bgColor' => config('bladewind.avatar.bg_color', null),
    'dotted' => config('bladewind.avatar.dotted', false),
    'label' => null,
    'plusAction' => null,
    'nonce' => config('bladewind.script.nonce', null),
])
@aware([
    // these attributes could be passed from the x-bladewind::avatars component also
    'dotted' => $dotted ?? false,
    'size' => config('bladewind.avatar.size', 'regular'),
    'stacked' => false,
    'showRing' => true,
    'dotPosition' => $dotPosition ?? 'bottom',
    'dotColor' => $dotColor ?? 'green',
    'plus' => '',
])
@php
    $sizes = [
        'tiny' => [ 'size_css' => 'size-6', 'dot_css' => 'left-5', 'plus_text_size' => 'text-xs' ],
        'small' => [ 'size_css' => 'size-8', 'dot_css' => 'left-6', 'plus_text_size' => 'text-sm' ],
        'medium' => [ 'size_css' => 'size-10', 'dot_css' => 'left-8', 'plus_text_size' => 'text-base' ],
        'regular' => [ 'size_css' => 'size-12', 'dot_css' => 'left-[31px] rtl:right-[31px]', 'plus_text_size' => 'text-lg' ],
        'big' => [ 'size_css' => 'size-16', 'dot_css' => 'left-[46px] rtl:right-[46px]', 'plus_text_size' => 'text-xl tracking-tighter' ],
        'huge' => [ 'size_css' => 'size-20', 'dot_css' => 'left-[58px] rtl:right-[58px]', 'plus_text_size' => 'text-2xl' ],
        'omg' => [ 'size_css' => 'size-28', 'dot_css' => 'left-[79px] rtl:right-[79px]', 'plus_text_size' => 'text-3xl' ]
    ];

    $dotted = parseBladewindVariable($dotted);
    $stacked = parseBladewindVariable($stacked);
    $showRing = parseBladewindVariable($showRing);
    $dotPosition = (in_array($dotPosition, ['top','bottom'])) ? $dotPosition : 'bottom';
    $size = (in_array($size, ['tiny','small','medium', 'regular','big','huge','omg'])) ? $size : 'regular';
    $avatar = $image ?: asset('vendor/bladewind/images/avatar.png');
    $show_plus = (str_starts_with($avatar, '+'));
    $image_size = $sizes[$size]['size_css'];
    $plus_text_size = $sizes[$size]['plus_text_size'];
    $dot_position_css = $sizes[$size]['dot_css'];
    $stacked = (is_numeric($plus) && $plus > 0) ? true : $stacked;
    $stacked_css = ($stacked) ? 'mb-3 !-mr-3' : '';
    $label = (!empty($label)) ? substr($label, 0, 2) : $label;

    $dotColor = defaultBladewindColour($dotColor);
    if( !empty($bgColor)) {
        $bgColor = defaultBladewindColour($bgColor);
    }

    if(!function_exists("urlExists")){
        function urlExists($url): bool
        {
            $headers = @get_headers($url);
            if(!$headers || $headers[0] == 'HTTP/1.1 404 Not Found') {
                return false;
            }
            return true;
        }
    }

    $use_label = ($label) || (strlen($image) <= 3);
     // $label will still be null if strlen($image) <= 3
    if($use_label) $avatar = $label ?? $image;
@endphp
{{-- format-ignore-end --}}

<div class="relative inline-block ltr:mr-2 rtl:ml-2 mt-1 rounded-full bw-avatar {{ $image_size }} {{$stacked_css}} {{$class}} @if($showRing) ring-2 ring-offset-2 ring-offset-white ring-{{(!empty($bgColor) ? $bgColor : 'gray')}}-200 dark:ring-0 dark:ring-offset-dark-500/50  @endif">
    @if($show_plus || $use_label)
        <div class="{{ $image_size }} {{$plus_text_size}} absolute rounded-full flex items-center justify-center font-semibold tracking-wide {{ (!empty($bgColor) ? 'text-'.$bgColor.'-600' : 'white')}}  bg-{{ (!empty($bgColor) ? $bgColor.'-100/70' : 'white')}} dark:bg-dark-600 dark:text-dark-300 @if($show_plus && !empty($plusAction)) plus-more cursor-pointer @endif"
             @if($show_plus && !empty($plusAction))
                 onclick="{!! $plusAction !!}" role="button" tabindex="0" data-bw-avatar-clickable
             @endif>
            {{$avatar}}
        </div>
    @else
        <img class="{{ $image_size }} object-cover object-center rounded-full {{$class}}" src="{{$avatar}}"
             alt="{{$alt}}"/>
    @endif
    @if($dotted && !$show_plus)
        <span class="-{{$dotPosition}}-1 {{$dot_position_css}} z-20 absolute w-3 h-3 bg-{{$dotColor}}-500 border-2 border-white dark:border-dark-800 rounded-full"></span>
    @endif
</div>

@if($show_plus && !empty($plusAction))
    @once
        <x-bladewind::script :nonce="$nonce">
            bwActivateOnKey('[data-bw-avatar-clickable]');
        </x-bladewind::script>
    @endonce
@endif
