{{-- format-ignore-start --}}
@props([
    // identifier reported to the move hook, e.g. a model id
    'value' => null,
    'class' => '',
])
{{-- format-ignore-end --}}
<li data-card
    @if(! is_null($value)) data-id="{{ $value }}" @endif
    tabindex="0"
    @class([
        'bw-kanban-card rounded-md border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900 px-3 py-2.5 text-sm text-gray-700 dark:text-dark-200 shadow-sm cursor-grab select-none focus:outline-2 focus:outline-primary-500',
        $class,
    ])>
    {{ $slot }}
</li>
