{{-- format-ignore-start --}}
@props([
    'value' => '',
    'label' => '',

    // optional second line displayed under the label to describe the item
    'description' => '',

    'selected' => 'false',
    'flag' => '',
    'image' => '',
    'filterBy' => '',
    'selectable' => 'true',
    'emptyStateFrom' => null,
    'isEmpty' => false,
])
@aware([
    'onselect' => '',
])

@php
    $selected = parseBladewindVariable($selected);
    $selectable = parseBladewindVariable($selectable);
    $isEmpty = parseBladewindVariable($isEmpty);
    $label = html_entity_decode($label);
    $description = html_entity_decode($description);
@endphp
{{-- format-ignore-end --}}

<div
        @class([
        "py-2 pl-4 pr-3 flex items-center text-base cursor-pointer bw-select-item",
        "group hover:bg-primary-600 hover:text-primary-50" => $selectable,
        "text-blue-900/40" => !$selectable,
        "hidden empty-state" => $isEmpty
        ])
        data-label="{!! $label !!}" data-value="{{ $value }}"
        @if($description !== '') data-description="{!! $description !!}" @endif
        @if(!$selectable) data-unselectable @endif
        @if(!empty($filterBy)) data-filter-value="{{$filterBy}}" @endif
        @if($selected) data-selected="true" @endif
        @if($onselect !== '') data-user-function="{{ $onselect }}" @endif>
    @if($isEmpty)
        @if($emptyStateFrom)
            <div class="text-center grow empty-state-copy"></div>
        @else
            <span class="grow text-left text-gray-500">{!! $label !!}</span>
        @endif
    @else
        @if ($flag !== '' && $image == '')
            <i class="{{ $flag }} flag"></i>
        @endif
        @if ($image !== '')
            <x-bladewind::avatar size="tiny" class="!mt-0 !mr-2.5" image="{{ $image }}"/>
        @endif
        <span class="grow text-left">
            {!! $label !!}
            @if($description !== '')
                <span class="block text-sm leading-snug opacity-70">{!! $description !!}</span>
            @endif
        </span>
        <x-bladewind::icon name="check-circle" class="text-slate-400 size-5 hidden shrink-0 svg-{{$value }}"/>
    @endif
</div>
