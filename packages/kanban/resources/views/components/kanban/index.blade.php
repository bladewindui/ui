{{-- format-ignore-start --}}
@props([
    // animation speed (ms) when a card is dropped. 0 disables animation
    'animation' => config('bladewind.kanban.animation', 150),
    // javascript function called after any card moves, whether by drag or by
    // keyboard, as (cardId, fromColumnId, toColumnId, newIndex)
    'onMove' => null,
    'class' => '',
    'id' => uniqid('bw-kanban-'),
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $animation = is_numeric($animation) ? (int) $animation : 150;
@endphp
{{-- format-ignore-end --}}

<div data-bw-kanban="{{ $id }}" @class(['bw-kanban flex items-start gap-4 overflow-x-auto pb-2', $class])>
    {{ $slot }}
</div>

@once
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/sortable.min.js') }}"></x-bladewind::script>
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/kanban.js') }}"></x-bladewind::script>
@endonce
<x-bladewind::script :nonce="$nonce">
    (() => {
        const root = document.querySelector('[data-bw-kanban="{{ $id }}"]');
        if (root && root.dataset.bwInitialised === 'true') return;
        if (root) root.dataset.bwInitialised = 'true';

        new BladewindKanban('{{ $id }}', {
            animation: {{ $animation }},
            onMove: @if($onMove) {{ $onMove }} @else null @endif,
        });
    })();
</x-bladewind::script>
