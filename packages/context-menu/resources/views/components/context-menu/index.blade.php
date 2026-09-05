{{-- format-ignore-start --}}
@props([
    // used to uniquely address this instance in css/js
    'name' => defaultBladewindName('bw-context-menu-'),
    'padded' => config('bladewind.context_menu.padded', true),
    // suppress the browser's own right-click menu over the wrapped region
    'disableNative' => config('bladewind.context_menu.disable_native', true),
    // the region that opens the menu on right-click (or the keyboard context-menu key)
    'region' => null,
    'class' => '',
    'modular' => false,
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $padded = parseBladewindVariable($padded);
    $disableNative = parseBladewindVariable($disableNative);
@endphp
{{-- format-ignore-end --}}

<div class="bw-context-menu {{ $name }} inline-block" data-name="{{ $name }}" data-disable-native="{{ $disableNative ? '1' : '0' }}">
    <div class="bw-context-menu-region">
        {{ $region }}
    </div>

    <div class="opacity-0 hidden bw-context-menu-items animate__animated animate__fadeIn animate__faster"
         id="{{ $name }}-menu"
         role="menu"
         aria-hidden="true"
         data-open="0">
        <div @class([
                'bw-items-list fixed rounded-md bg-white dark:bg-dark-700',
                'border border-transparent dark:border-dark-800/20 ring-1 ring-slate-800/5',
                'shadow-md shadow-slate-200/80 dark:shadow-dark-800/70 whitespace-nowrap',
                'z-[9999] min-w-40',
                'p-2' => $padded,
                'p-0' => !$padded,
                "$class",
            ])>
            {{ $slot }}
        </div>
    </div>
</div>

@once
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/context-menu.js') }}"></x-bladewind::script>
@endonce
<x-bladewind::script :nonce="$nonce" :modular="$modular">
    (() => {
        const root = document.querySelector('.{{ $name }}');
        // Guard against a duplicate instance (and duplicate document-level
        // listeners) when a framework like Livewire re-renders this markup
        // without a full page reload.
        if (root && root.dataset.bwInitialised === 'true') return;
        if (root) root.dataset.bwInitialised = 'true';

        new BladewindContextMenu('{{ $name }}');
    })();
</x-bladewind::script>
