{{-- format-ignore-start --}}
@props([
    // unique name for this list. used as the css hook and js variable name.
    // prefixed with bw- when used as a class name
    'name' => defaultBladewindName('bw-sortable-'),

    // simple  -> a standalone list that only sorts within itself
    // shared  -> a list whose items can be dragged into other lists in the same group
    'type' => config('bladewind.sortable.type', 'simple'),

    // group name used by shared lists. lists sharing the same group can exchange items.
    // ignored for simple lists (each simple list gets its own private group)
    'group' => config('bladewind.sortable.group', null),

    // when dragging to another (shared) list, leave a copy behind instead of moving the item
    'clone' => config('bladewind.sortable.clone', false),

    // enable or disable sorting of items within this list
    'sortable' => config('bladewind.sortable.sortable', true),

    // items are dragged using a dedicated handle instead of the whole item surface
    'hasHandle' => config('bladewind.sortable.has_handle', false),

    // icon shown as the drag handle when hasHandle is true (any heroicon name)
    'handleIcon' => config('bladewind.sortable.handle_icon', 'bars-3'),

    // space or comma separated class names whose items should not be draggable
    // e.g. filter="locked pinned"
    'filter' => config('bladewind.sortable.filter', null),

    // allow selecting and dragging multiple items at once (ctrl/cmd + click to select)
    'multidrag' => config('bladewind.sortable.multidrag', false),

    // swap items on drop instead of shifting them. cannot be combined with multidrag
    'swap' => config('bladewind.sortable.swap', false),

    // animation speed (ms) when items are reordered. 0 disables animation
    'animation' => config('bladewind.sortable.animation', 150),

    // when set, a hidden <input> with this name is kept in sync with the current
    // order as a JSON array of each item's "value". submit it with your form.
    'inputName' => null,

    // optional javascript called after every reorder as (order, event).
    // handy for saving via ajax. e.g. onSorted="saveOrder"
    'onSorted' => null,

    // additional css classes for the list (ul) element
    'class' => '',

    'modular' => false, // append type="module" to script tags
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $type = (! in_array($type, ['simple', 'shared'])) ? 'simple' : $type;
    $clone = parseBladewindVariable($clone);
    $sortable = parseBladewindVariable($sortable);
    $hasHandle = parseBladewindVariable($hasHandle);
    $multidrag = parseBladewindVariable($multidrag);
    $swap = parseBladewindVariable($swap);
    $animation = is_numeric($animation) ? (int) $animation : 150;

    // multidrag and swap are mutually exclusive in SortableJS — multidrag wins
    if ($multidrag && $swap) { $swap = false; }

    // simple lists are isolated by giving each its own private group.
    // shared lists share a named group so they can exchange items.
    $groupName = ($type === 'shared') ? ($group ?: 'bw-shared-list') : $name;
    $pull = $clone ? "'clone'" : 'true';
    // simple lists keep their private group, so put:true still only accepts items
    // from a list with the same (private) group name — effectively isolating them.
    $put = 'true';

    // turn "locked pinned" or ".locked, .pinned" into a ".locked, .pinned" selector list
    $filterSelector = '';
    if (! empty($filter)) {
        $parts = preg_split('/[\s,]+/', trim($filter), -1, PREG_SPLIT_NO_EMPTY);
        $parts = array_map(fn ($c) => '.'.ltrim($c, '.'), $parts);
        $filterSelector = implode(', ', $parts);
    }
@endphp
{{-- format-ignore-end --}}

@once
    <link href="{{ asset('vendor/bladewind/css/sortable.css') }}" rel="stylesheet"/>
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/sortable.min.js') }}"></x-bladewind::script>
@endonce

<ul {{ $attributes->exceptPropAliases(get_defined_vars())->merge(['class' => "bw-sortable-list {$name}-list space-y-2".($hasHandle ? ' bw-has-handle' : '')." {$class}"]) }}
    data-sortable-name="{{ $name }}">
    {{ $slot }}
</ul>
@if(! empty($inputName))
    <input type="hidden" name="{{ $inputName }}" class="{{ $name }}-order"/>
@endif

<x-bladewind::script :nonce="$nonce" :modular="$modular">
    (() => {
        const list = domEl('.{{ $name }}-list');
        // Guard against a duplicate Sortable instance when a framework like
        // Livewire re-renders this markup without a full page reload.
        if (list && list.dataset.bwInitialised === 'true') return;
        if (list) list.dataset.bwInitialised = 'true';

        // exposed on window, not as a local const, so it stays reachable as
        // a bare `{{ $name }}` reference from other <script> tags on the page
        // (see "Reading the order yourself" in the README) even though
        // instantiation now happens inside this guarded closure.
        window.{{ $name }} = new Sortable(list, {
            group: { name: '{{ $groupName }}', pull: {!! $pull !!}, put: {!! $put !!} },
            sort: {{ $sortable ? 'true' : 'false' }},
            animation: {{ $animation }},
            @if($hasHandle) handle: '.bw-sortable-handle', @endif
            @if(! empty($filterSelector)) filter: '{{ $filterSelector }}', preventOnFilter: true, @endif
            @if($multidrag) multiDrag: true, selectedClass: 'bw-sortable-selected', @endif
            @if($swap) swap: true, swapClass: 'bw-sortable-swap-highlight', @endif
            ghostClass: 'bw-sortable-ghost',
            chosenClass: 'bw-sortable-chosen',
            dragClass: 'bw-sortable-drag',
            @if(! empty($inputName) || ! empty($onSorted))
            onSort: function (evt) {
                const order = {{ $name }}.toArray();
                @if(! empty($inputName))
                const orderInput = domEl('.{{ $name }}-order');
                if (orderInput) setFieldValue(orderInput, JSON.stringify(order));
                @endif
                @if(! empty($onSorted))
                if (typeof {{ $onSorted }} === 'function') {{ $onSorted }}(order, evt);
                @endif
            },
            @endif
        });
        @if(! empty($inputName))
        // seed the hidden input with the initial order on load
        domEl('.{{ $name }}-order').value = JSON.stringify({{ $name }}.toArray());
        @endif
    })();
</x-bladewind::script>
