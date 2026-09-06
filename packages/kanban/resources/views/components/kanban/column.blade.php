{{-- format-ignore-start --}}
@props([
    'title' => '',
    // identifier reported to the move hook. defaults to a slug of the title
    'id' => null,
    // hides the card list and shows a spinner instead, useful while a
    // column's cards are still loading asynchronously
    'loading' => config('bladewind.kanban_column.loading', false),
    // shown in place of the list when it has no cards and isn't loading
    'emptyText' => config('bladewind.kanban_column.empty_text', 'No cards'),
    'class' => '',
])
@php
    $loading = parseBladewindVariable($loading);
    $columnId = $id ?: (string) str($title)->slug();
@endphp
{{-- format-ignore-end --}}

<div data-column data-column-id="{{ $columnId }}" @class(['bw-kanban-column w-72 shrink-0 rounded-lg bg-gray-50 dark:bg-dark-800/60 flex flex-col max-h-full', $class])>
    <div class="flex items-center justify-between gap-2 px-3 py-2.5 border-b border-gray-200 dark:border-dark-700">
        <div class="flex items-center gap-2 min-w-0">
            <span class="font-medium text-sm text-gray-700 dark:text-dark-200 truncate">{{ $title }}</span>
            <span data-count class="text-xs text-gray-400 dark:text-dark-500 bg-gray-200/70 dark:bg-dark-700 rounded-full px-1.5 py-0.5"></span>
        </div>
        @isset($actions)
            <div class="flex items-center gap-1 shrink-0">{{ $actions }}</div>
        @endisset
    </div>

    @if($loading)
        <div class="flex items-center justify-center py-10">
            <x-bladewind::spinner size="small"/>
        </div>
    @else
        <ul data-card-list class="bw-kanban-list flex-1 min-h-[2rem] space-y-2 p-2 overflow-y-auto">
            {{ $slot }}
        </ul>
        <p data-empty hidden class="text-xs text-center text-gray-400 dark:text-dark-500 py-6 px-2">{{ $emptyText }}</p>
    @endif
</div>
