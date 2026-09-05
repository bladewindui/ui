{{-- format-ignore-start --}}
@props([
    'arrows' => config('bladewind.carousel.arrows', true),
    'indicators' => config('bladewind.carousel.indicators', true),
    'autoplay' => config('bladewind.carousel.autoplay', false),
    // milliseconds between slides when autoplay is on
    'interval' => config('bladewind.carousel.interval', 5000),
    // wrap from the last slide back to the first, and vice versa
    'loop' => config('bladewind.carousel.loop', true),
    'swipe' => config('bladewind.carousel.swipe', true),
    // any valid CSS height value, e.g. "320px". leave blank and the
    // carousel takes the height of its tallest slide
    'height' => '',
    'class' => '',
    'id' => uniqid('bw-carousel-'),
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $arrows = parseBladewindVariable($arrows);
    $indicators = parseBladewindVariable($indicators);
    $autoplay = parseBladewindVariable($autoplay);
    $loop = parseBladewindVariable($loop);
    $swipe = parseBladewindVariable($swipe);
@endphp
{{-- format-ignore-end --}}

<div data-bw-carousel="{{ $id }}"
     @class(['bw-carousel relative overflow-hidden rounded-lg', $class])
     role="region"
     aria-roledescription="carousel"
     @if($height) style="height: {{ $height }}" @endif>
    <div data-track class="bw-carousel-track flex h-full">
        {{ $slot }}
    </div>

    @if($arrows)
        <button type="button" data-prev aria-label="Previous slide"
                class="absolute top-1/2 left-2 -translate-y-1/2 grid place-items-center size-8 rounded-full bg-black/40 text-white hover:bg-black/60">
            <x-bladewind::icon name="chevron-left" class="size-5"/>
        </button>
        <button type="button" data-next aria-label="Next slide"
                class="absolute top-1/2 right-2 -translate-y-1/2 grid place-items-center size-8 rounded-full bg-black/40 text-white hover:bg-black/60">
            <x-bladewind::icon name="chevron-right" class="size-5"/>
        </button>
    @endif

    @if($indicators)
        <div data-indicators class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-2"></div>
    @endif
</div>

@once
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/carousel.js') }}"></x-bladewind::script>
@endonce
<x-bladewind::script :nonce="$nonce">
    (() => {
        const root = document.querySelector('[data-bw-carousel="{{ $id }}"]');
        if (root && root.dataset.bwInitialised === 'true') return;
        if (root) root.dataset.bwInitialised = 'true';

        new BladewindCarousel('{{ $id }}', {
            loop: {{ $loop ? 'true' : 'false' }},
            swipe: {{ $swipe ? 'true' : 'false' }},
            autoplay: {{ $autoplay ? 'true' : 'false' }},
            interval: {{ (int) $interval }},
            indicators: {{ $indicators ? 'true' : 'false' }},
        });
    })();
</x-bladewind::script>
