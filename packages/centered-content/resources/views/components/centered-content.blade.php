{{-- format-ignore-start --}}
@props([
    'size' => 'xl',
])
@php
    $width = [
        'omg' => '7xl',
        'xxl' => '6xl',
        'xl' => '4xl',
        'big' => '3xl',
        'medium' => '2xl',
        'small' => 'lg',
        'tiny' => 'md'
    ];
    $sizes = ['omg', 'xxl', 'xl', 'big', 'medium', 'small', 'tiny'];

    if(! in_array($size, $sizes)) {
        $size = 'xl';
    }
@endphp
{{-- format-ignore-end --}}

<div class="max-w-md max-w-lg max-w-2xl max-w-3xl max-w-4xl max-w-6xl max-w-7xl "></div>
<div {{ $attributes->exceptPropAliases(get_defined_vars())->merge(['class' => "max-w-$width[$size] mx-auto"]) }}>
    {{ $slot }}
</div>
