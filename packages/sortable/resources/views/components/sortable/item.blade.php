{{-- format-ignore-start --}}
@aware([
    'hasHandle' => false,
    'handleIcon' => 'bars-3',
])
@props([
    // identifier used when capturing the list order (e.g. the model id).
    // rendered as data-id, which is what the order array / hidden input reports.
    'value' => null,

    // additional css classes for the item (li) element
    'class' => '',
])
@php
    $hasHandle = parseBladewindVariable($hasHandle);
@endphp
{{-- format-ignore-end --}}

<li @if(! is_null($value)) data-id="{{ $value }}" @endif
    {{ $attributes->exceptPropAliases(get_defined_vars())->merge(['class' => "bw-sortable-item flex items-center gap-x-3 select-none rounded-lg border border-slate-200 dark:border-dark-700 bg-white dark:bg-dark-800 px-4 py-3 text-sm text-slate-700 dark:text-dark-200 shadow-sm {$class}"]) }}>
    @if($hasHandle)
        <x-bladewind::icon
                name="{{ $handleIcon }}"
                class="bw-sortable-handle size-5 shrink-0 text-slate-400 dark:text-dark-400 hover:text-slate-600 dark:hover:text-dark-200"/>
    @endif
    <div class="grow min-w-0">{{ $slot }}</div>
</li>
