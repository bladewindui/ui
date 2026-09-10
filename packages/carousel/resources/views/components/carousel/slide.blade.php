{{-- format-ignore-start --}}
@props([
    'class' => '',
])
{{-- format-ignore-end --}}
<div @class(['bw-carousel-slide shrink-0 w-full h-full', $class])>
    {{ $slot }}
</div>
