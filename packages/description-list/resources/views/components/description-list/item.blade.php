{{-- format-ignore-start --}}
@props([
    'label' => '',
    // optional content shown to the right of the value, e.g. an edit link
    'action' => null,
    'class' => '',
])
@aware([
    // @aware only sees an explicitly-passed attribute on the root, never its
    // own @props default, so the config fallback is repeated here rather
    // than relied on from the root — matching dropmenu.item's icon_right
    'striped' => config('bladewind.description_list.striped', false),
])
@php
    $striped = parseBladewindVariable($striped);
@endphp
{{-- format-ignore-end --}}
<div @class([
        'bw-description-list-item py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-4',
        'bg-gray-50/60 dark:bg-dark-800/40 px-3 -mx-3 rounded-md' => $striped,
        "$class",
    ])>
    <dt class="text-sm font-medium text-gray-500 dark:text-dark-400">{{ $label }}</dt>
    <dd class="mt-1 flex items-center justify-between gap-2 text-sm text-gray-800 dark:text-dark-200 sm:col-span-2 sm:mt-0">
        <span class="grow">{{ $slot }}</span>
        @isset($action)
            <span class="shrink-0">{{ $action }}</span>
        @endisset
    </dd>
</div>
