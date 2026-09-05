{{-- format-ignore-start --}}
@props([
    // name (or id) of the password field this meter watches. required.
    'for' => null,
    'name' => defaultBladewindName('bw-password-meter-'),
    'showLabel' => config('bladewind.password_meter.show_label', true),
    // a password reaches its first length point at this many characters...
    'minLength' => config('bladewind.password_meter.min_length', 8),
    // ...and a second at this many
    'strongLength' => config('bladewind.password_meter.strong_length', 12),
    'class' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $showLabel = parseBladewindVariable($showLabel);
    $minLength = is_numeric($minLength) ? (int) $minLength : 8;
    $strongLength = is_numeric($strongLength) ? (int) $strongLength : 12;
@endphp
{{-- format-ignore-end --}}
<div class="bw-password-meter {{ $name }} {{ $class }}"
     data-name="{{ $name }}"
     data-for="{{ $for }}"
     data-min-length="{{ $minLength }}"
     data-strong-length="{{ $strongLength }}"
     data-label-weak="{{ __('bladewind::bladewind.password_strength_weak') }}"
     data-label-fair="{{ __('bladewind::bladewind.password_strength_fair') }}"
     data-label-good="{{ __('bladewind::bladewind.password_strength_good') }}"
     data-label-strong="{{ __('bladewind::bladewind.password_strength_strong') }}">
    <div class="flex gap-1" role="presentation">
        <span class="h-1 flex-1 rounded-full bg-gray-200 dark:bg-dark-600 transition-colors duration-150" data-bar></span>
        <span class="h-1 flex-1 rounded-full bg-gray-200 dark:bg-dark-600 transition-colors duration-150" data-bar></span>
        <span class="h-1 flex-1 rounded-full bg-gray-200 dark:bg-dark-600 transition-colors duration-150" data-bar></span>
        <span class="h-1 flex-1 rounded-full bg-gray-200 dark:bg-dark-600 transition-colors duration-150" data-bar></span>
    </div>
    @if($showLabel)
        <div class="text-xs mt-1 text-gray-500 dark:text-dark-400 min-h-[1em]" data-label aria-live="polite"></div>
    @endif
</div>

@once
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/password-meter.js') }}"></x-bladewind::script>
@endonce
<x-bladewind::script :nonce="$nonce">
    (() => {
        const root = document.querySelector('.{{ $name }}');
        // Guard against a duplicate instance (and duplicate input listeners)
        // when a framework like Livewire re-renders this markup without a
        // full page reload.
        if (root && root.dataset.bwInitialised === 'true') return;
        if (root) root.dataset.bwInitialised = 'true';

        new BladewindPasswordMeter('{{ $name }}');
    })();
</x-bladewind::script>
