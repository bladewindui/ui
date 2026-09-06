{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName('bw-transfer-'),
    // array (or JSON string) of items. each needs at least value/label keys
    // (overridable via valueKey/labelKey), e.g.
    // [{"value": 1, "label": "Editor"}, {"value": 2, "label": "Viewer"}]
    'items' => [],
    'valueKey' => config('bladewind.transfer_list.value_key', 'value'),
    'labelKey' => config('bladewind.transfer_list.label_key', 'label'),
    // values already in the right-hand "selected" panel
    'selected' => [],
    'availableLabel' => __('bladewind::bladewind.transfer_list_available'),
    'selectedLabel' => __('bladewind::bladewind.transfer_list_selected'),
    'emptyLabel' => __('bladewind::bladewind.transfer_list_empty'),
    'searchable' => config('bladewind.transfer_list.searchable', true),
    'searchPlaceholder' => __('bladewind::bladewind.select_search_placeholder'),
    // height (px) of each list panel
    'height' => config('bladewind.transfer_list.height', 260),
    'class' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $searchable = parseBladewindVariable($searchable);
    $height = is_numeric($height) ? (int) $height : 260;

    $items = is_string($items) ? (json_decode($items, true) ?? []) : $items;
    $selected = is_string($selected) ? (json_decode($selected, true) ?? []) : $selected;
    $selectedValues = array_map('strval', (array) $selected);
@endphp
{{-- format-ignore-end --}}
<div class="bw-transfer-list {{ $name }} flex flex-col sm:flex-row items-stretch sm:items-start gap-3 {{ $class }}" data-name="{{ $name }}">
    @foreach(['available' => false, 'selected' => true] as $listKey => $isSelectedPanel)
        <div class="flex-1 min-w-0 rounded-md border border-gray-200 dark:border-dark-600 overflow-hidden">
            <div class="flex items-center justify-between gap-2 px-3 py-2 border-b border-gray-200 dark:border-dark-600 bg-gray-50 dark:bg-dark-800/50">
                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-dark-300 cursor-pointer">
                    <input type="checkbox" data-select-all="{{ $listKey }}"
                           aria-label="{{ __('bladewind::bladewind.transfer_list_select_all') }}"/>
                    <span>{{ $isSelectedPanel ? $selectedLabel : $availableLabel }}</span>
                </label>
                <span class="text-xs text-gray-400 dark:text-dark-500" data-count="{{ $listKey }}"></span>
            </div>
            @if($searchable)
                <div class="p-2 border-b border-gray-200 dark:border-dark-600">
                    <input type="text" data-search="{{ $listKey }}" placeholder="{{ $searchPlaceholder }}"
                           class="bw-input w-full text-sm rounded-md border border-gray-200 dark:border-dark-600 dark:bg-dark-900/50 px-2.5 py-1.5 focus:outline-primary-500 focus:border-primary-500"/>
                </div>
            @endif
            <ul data-list="{{ $listKey }}" style="height: {{ $height }}px" class="overflow-y-auto divide-y divide-gray-100 dark:divide-dark-700">
                @foreach($items as $item)
                    @php $value = (string) $item[$valueKey]; @endphp
                    @if(in_array($value, $selectedValues, true) === $isSelectedPanel)
                        <li data-value="{{ $value }}" data-label="{{ mb_strtolower((string) $item[$labelKey]) }}">
                            <label class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-dark-200 cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-800/60">
                                <input type="checkbox" data-item-checkbox/>
                                <span class="truncate">{{ $item[$labelKey] }}</span>
                            </label>
                        </li>
                    @endif
                @endforeach
            </ul>
            <div class="hidden px-3 py-6 text-center text-xs text-gray-400 dark:text-dark-500" data-empty="{{ $listKey }}">{{ $emptyLabel }}</div>
        </div>

        @if($listKey === 'available')
            <div class="flex sm:flex-col flex-row justify-center gap-2 sm:pt-8 sm:pb-0 py-2">
                <button type="button" data-action="move-right" title="{{ __('bladewind::bladewind.transfer_list_selected') }}"
                        class="p-1.5 rounded-md border border-gray-200 dark:border-dark-600 hover:bg-gray-50 dark:hover:bg-dark-800 text-gray-500 dark:text-dark-300">
                    <x-bladewind::icon name="chevron-right" class="size-4 sm:rotate-0 rotate-90"/>
                </button>
                <button type="button" data-action="move-all-right" title="{{ __('bladewind::bladewind.transfer_list_selected') }}"
                        class="p-1.5 rounded-md border border-gray-200 dark:border-dark-600 hover:bg-gray-50 dark:hover:bg-dark-800 text-gray-500 dark:text-dark-300">
                    <x-bladewind::icon name="chevron-double-right" class="size-4 sm:rotate-0 rotate-90"/>
                </button>
                <button type="button" data-action="move-left" title="{{ __('bladewind::bladewind.transfer_list_available') }}"
                        class="p-1.5 rounded-md border border-gray-200 dark:border-dark-600 hover:bg-gray-50 dark:hover:bg-dark-800 text-gray-500 dark:text-dark-300">
                    <x-bladewind::icon name="chevron-left" class="size-4 sm:rotate-0 rotate-90"/>
                </button>
                <button type="button" data-action="move-all-left" title="{{ __('bladewind::bladewind.transfer_list_available') }}"
                        class="p-1.5 rounded-md border border-gray-200 dark:border-dark-600 hover:bg-gray-50 dark:hover:bg-dark-800 text-gray-500 dark:text-dark-300">
                    <x-bladewind::icon name="chevron-double-left" class="size-4 sm:rotate-0 rotate-90"/>
                </button>
            </div>
        @endif
    @endforeach

    @foreach($items as $item)
        <input type="hidden" name="{{ $name }}[]" data-hidden-value="{{ (string) $item[$valueKey] }}"
               value="{{ $item[$valueKey] }}" @unless(in_array((string) $item[$valueKey], $selectedValues, true)) disabled @endunless/>
    @endforeach
</div>

@once
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/transfer-list.js') }}"></x-bladewind::script>
@endonce
<x-bladewind::script :nonce="$nonce">
    (() => {
        const root = document.querySelector('.{{ $name }}');
        // Guard against a duplicate instance (and duplicate listeners) when a
        // framework like Livewire re-renders this markup without a full page
        // reload.
        if (root && root.dataset.bwInitialised === 'true') return;
        if (root) root.dataset.bwInitialised = 'true';

        new BladewindTransferList('{{ $name }}');
    })();
</x-bladewind::script>
