{{-- format-ignore-start --}}
@props([
    // info, success, warning, error, primary
    'tone' => config('bladewind.banner.tone', 'info'),
    'title' => '',
    'showIcon' => config('bladewind.banner.show_icon', true),
    // any Heroicons icon to use in place of the tone's default icon
    'icon' => '',
    'dismissible' => config('bladewind.banner.dismissible', true),
    // remembers a dismissal across page loads via localStorage under this key.
    // leave blank and the banner shows again on every load
    'persistKey' => '',
    'rounded' => config('bladewind.banner.rounded', false),
    'class' => '',
    'id' => uniqid('bw-banner-'),
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $showIcon = parseBladewindVariable($showIcon);
    $dismissible = parseBladewindVariable($dismissible);
    $rounded = parseBladewindVariable($rounded);

    $type = in_array($tone, ['error', 'warning', 'success', 'info']) ? $tone : 'primary';
    // modal-icon only knows info/success/error/warning — a primary tone
    // borrows its default icon rather than rendering nothing
    $iconType = $type === 'primary' ? 'info' : $type;
    $colour = $type === 'primary' ? 'primary' : match ($type) {
        'warning' => 'yellow',
        'error' => 'red',
        'success' => 'green',
        'info' => 'blue',
    };
    $classes = "bg-$colour-50 dark:bg-$colour-500/10 text-$colour-800 dark:text-$colour-200";
    $iconClasses = "text-$colour-500 dark:text-$colour-400";
@endphp
{{-- format-ignore-end --}}

<div data-bw-banner="{{ $id }}"
     @class([
        'bw-banner w-full animate__animated animate__fadeIn flex items-start gap-3 p-4',
        'rounded-md' => $rounded,
        $classes,
        $class,
     ])
     role="{{ in_array($type, ['error', 'warning']) ? 'alert' : 'status' }}"
     aria-live="{{ in_array($type, ['error', 'warning']) ? 'assertive' : 'polite' }}">
    @if($showIcon)
        <div class="pt-[1px] shrink-0">
            @if($icon !== '')
                <span><x-bladewind::icon :name="$icon" class="{{ $iconClasses }}"/></span>
            @else
                <x-bladewind::modal-icon type="{{ $iconType }}" class="{{ $iconClasses }}"/>
            @endif
        </div>
    @endif

    <div class="grow flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm">
            @if($title !== '')
                <p class="font-semibold">{{ $title }}</p>
            @endif
            {{ $slot }}
        </div>

        @isset($actions)
            <div class="flex items-center gap-3 shrink-0">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @if($dismissible)
        <div class="shrink-0" data-bw-banner-dismiss>
            <x-bladewind::icon
                name="x-mark"
                class="size-[18px] p-[3px] stroke-2 cursor-pointer {{ $iconClasses }} bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/20 rounded-full"/>
        </div>
    @endif
</div>

@once
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/banner.js') }}"></x-bladewind::script>
@endonce
<x-bladewind::script :nonce="$nonce">
    (() => {
        const root = document.querySelector('[data-bw-banner="{{ $id }}"]');
        // guard against a duplicate instance (and duplicate listeners) when a
        // framework like Livewire re-renders this markup without a full page
        // reload
        if (root && root.dataset.bwInitialised === 'true') return;
        if (root) root.dataset.bwInitialised = 'true';

        new BladewindBanner('{{ $id }}', '{{ $persistKey }}');
    })();
</x-bladewind::script>
