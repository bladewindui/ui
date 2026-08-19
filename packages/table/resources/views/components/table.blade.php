{{-- format-ignore-start --}}
@props([
    // your table headers in <th></th> tags
    'header' => '',

    // setting to true will result in a striped table
    'striped' => config('bladewind.table.striped', false),

    // should the table with displayed with a drop-shadow effect
    'hasShadow' => config('bladewind.table.has_shadow', false),

    // should the table have a border on all four sides
    'hasBorder' => config('bladewind.table.has_border', false),

    // should the table have row dividers
    'divided' => config('bladewind.table.divided', true),

    // if table has row dividers, how wide should they be
    // available value are regular, thin
    'divider' => config('bladewind.table.divider', 'regular'),

    // should rows light up on hover
    'hasHover' => config('bladewind.table.has_hover', true),

    // should the rows be tighter together
    'compact' => config('bladewind.table.compact', false),

    // provide a table name you can access via css
    'name' => defaultBladewindName('tbl-'),

    'data' => null,
    'excludeColumns' => null,
    'includeColumns' => null,
    'actionIcons' => null,
    'groupby' => null,
    'actionsTitle' => 'actions',
    'columnAliases' => [],
    'searchable' => config('bladewind.table.searchable', false),
    'searchPlaceholder' => config('bladewind.table.search_placeholder', __("bladewind::bladewind.table_search_placeholder")),
    'searchField' => null,
    'searchDebounce' => 0,
    'searchMinLength' => 0,
    'searchContainer' => null,
    'celled' => config('bladewind.table.celled', false),
    'uppercasing' => config('bladewind.table.uppercasing', true),
    'noDataMessage' => config('bladewind.table.no_data_message', __("bladewind::bladewind.table_no_data")),
    'messageAsEmptyState' => config('bladewind.table.message_as_empty_state', false),
    // parameters expected by the empty state component ---------------
    'image' => asset('vendor/bladewind/images/empty-state.svg'),
    'heading' => '',
    'buttonLabel' => '',
    'showImage' => config('bladewind.table.show_image', true),
    'onclick' => '',
    //------------------ end empty state parameters -------------------
    'selectable' => config('bladewind.table.selectable', false),
    'checkable' => config('bladewind.table.checkable', false),
    'transparent' => config('bladewind.table.transparent', false),
    'selectedValue' => null,
    'sortable' => config('bladewind.table.sortable', false),
    'sortableColumns' => [],
    'paginated' => config('bladewind.table.paginated', false),
    'paginationStyle' => config('bladewind.table.pagination_style', 'arrows'),
    'pageSize' => config('bladewind.table.page_size', 25),
    'showRowNumbers' => config('bladewind.table.show_row_numbers', false),
    'showTotal' => config('bladewind.table.show_total', true),
    'showPageNumber' => config('bladewind.table.show_page_number', true),
    'showTotalPages' => config('bladewind.table.show_total_pages', false),
    'defaultPage' => 1,
    'totalLabel' => config('bladewind.table.total_label', __("bladewind::bladewind.pagination_label")),
    'limit' => null,
    'layout' => 'auto',
    'groupHeadingCss' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    // reset variables for Laravel 8 support
    $hasShadow = parseBladewindVariable($hasShadow);
    $hasHover = parseBladewindVariable($hasHover);
    $striped = parseBladewindVariable($striped);
    $compact = parseBladewindVariable($compact);
    $divided = parseBladewindVariable($divided);
    $searchable = parseBladewindVariable($searchable);
    $searchField = parseBladewindVariable($searchField, 'string');
    $searchDebounce = parseBladewindVariable($searchDebounce, 'int');
    $searchMinLength = parseBladewindVariable($searchMinLength, 'int');
    $uppercasing = parseBladewindVariable($uppercasing);
    $celled = parseBladewindVariable($celled);
    $selectable = parseBladewindVariable($selectable);
    $checkable = parseBladewindVariable($checkable);
    $transparent = parseBladewindVariable($transparent);
    $paginated = parseBladewindVariable($paginated);
    $sortable = parseBladewindVariable($sortable);
    $pageSize = parseBladewindVariable($pageSize, 'int');
    $messageAsEmptyState = parseBladewindVariable($messageAsEmptyState);
    $showRowNumbers = parseBladewindVariable($showRowNumbers);
    $showTotal = parseBladewindVariable($showTotal);
    $defaultPage = parseBladewindVariable($defaultPage, 'int');

    $name = preg_replace('/[\s-]/', '_', $name);
    $iconsArray = [];
    $canGroup = false;

    $excludeColumns = !empty($excludeColumns) ? explode(',', str_replace(' ','', $excludeColumns)) : [];
    $actionIcons = (!empty($actionIcons)) ? ((is_array($actionIcons)) ?
        $actionIcons : json_decode(str_replace('&quot;', '"', $actionIcons), true)) : [];
    $columnAliases = (!empty($columnAliases)) ? ((is_array($columnAliases)) ?
        $columnAliases : json_decode(str_replace('&quot;', '"', $columnAliases), true)) : [];

    if($checkable) $selectable = true;

    if (!is_null($data)) {
        $data = (!is_array($data)) ? json_decode(str_replace('&quot;', '"', $data), true) : $data;

        $totalRecords = (!empty($limit)) ? $limit : count($data);
        $defaultPage = ($defaultPage > ceil($totalRecords/$pageSize)) ? 1 : $defaultPage;
        $tableHeadings = $allTableHeadings = ($totalRecords > 0) ? array_keys((array) $data[0]) : (($columnAliases) ?? []);

        if( !empty($excludeColumns) ) {
            $tableHeadings = array_values(array_diff($tableHeadings, $excludeColumns));
        }

        if( !empty($includeColumns) ) {
            $excludeColumns = [];
            $tableHeadings = explode(',', str_replace(' ','', $includeColumns));
        }

        if($sortable){
            $sortableColumns = empty($sortableColumns) ? $tableHeadings : explode(',', str_replace(' ','', $sortableColumns));
        }

        if(!empty($groupby) && in_array($groupby, $tableHeadings)) {
            $canGroup = true;
            $groupedData = collect($data)->groupBy($groupby);
            $uniqueGroupHeadings = $groupedData->keys()->all();
            $tableHeadings = collect($tableHeadings)->reject(fn($heading) => $heading === $groupby)->values()->all();
        }

        // build action icons
        $tempActionsArray = [];
        if(!empty($actionIcons[0]) && is_string($actionIcons[0])){
            foreach ($actionIcons as $action) {
                $parts = array_map('trim', explode('|', $action));
                $result = [];
                foreach ($parts as $part) {
                    [$key, $value] = explode(':', $part, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if ($key !== '' && $value !== '') {
                        $result[$key] = $value;
                    }
                }
                $tempActionsArray[] = $result;
            }
            $actionIcons = $tempActionsArray;
        }

        if(!function_exists('build_click')){
            function build_click($click, $rowData): array|string|null
            {
                return preg_replace_callback("/'\{\w+}'/", function ($matches) use ($rowData) {
                    foreach($matches as $match) {
                        $value  = $rowData[str_replace('}\'', '', str_replace('\'{', '', $match))];
                        return "'{$value}'";
                    }
                }, $click);
            }
        }
    }
@endphp
{{-- format-ignore-end --}}

<x-bladewind::script :nonce="$nonce">
    let tableData_{{str_replace('-','_', $name)}} = {!! json_encode($data) !!};
</x-bladewind::script>
<div @class([
    'border-collapse max-w-full',
    'border border-gray-200/70 dark:border-dark-600' => ($hasBorder && !$celled)
])>
    <div class="w-full">
        @if($searchable)
            <div class="bw-table-filter-bar p-2 @if($hasShadow) drop-shadow shadow shadow-gray-200/70 dark:shadow-md dark:shadow-dark-950/20 @endif"
                 data-table-name="{{$name}}">
                <x-bladewind::input
                        name="bw-search-{{$name}}"
                        placeholder="{{$searchPlaceholder}}"
                        add_clearing="false"
                        clearable="true"
                        class="!border-0 !outline-transparent focus:!border-none focus:!outline-transparent !py-2.5"
                        onInput="filterTableDebounced(this.value, 'table.{{$name}}', '{{$searchField}}', {{$searchDebounce}}, {{$searchMinLength}}, tableData_{{str_replace('-','_', $name)}})();"
                        prefix="magnifying-glass"
                        prefix_is_icon="true"/>
            </div>
        @endif
        <table @if($paginated) data-current-page="{{$defaultPage}}" @endif @class([
            'bw-table w-full ' . $name,
            'drop-shadow shadow shadow-gray-200/70 dark:shadow-md dark:shadow-dark-950/20' => $hasShadow,
            'divided' => $divided,
            'thin' => $divided && $divider == 'thin',
            'striped' => $striped,
            'celled' => $celled,
            'with-hover-effect' => $hasHover,
            'compact' => $compact,
            'uppercase-headers' => $uppercasing,
            'sortable' => $sortable,
            'paginated' => $paginated,
            'selectable' => $selectable,
            'checkable' => $checkable,
            'transparent' => $transparent
            ])>
            @if(is_null($data) || $layout == 'custom')
                @if(!empty($header))
                    <thead>
                    <tr>{{ $header }}</tr>
                    </thead>
                @endif
                <tbody>{{ $slot }}</tbody>
            @else
                <thead>
                <tr>
                    @if($showRowNumbers)
                        <th>#</th>
                    @endif
                    @foreach($tableHeadings as $th)
                        @continue(!empty($excludeColumns) && in_array($th, $excludeColumns))
                        @php
                            // get positions/indices of the fields to be displayed
                            // use these indices to directly target data to display from $data
                            $indices[] = $loop->index;
                        @endphp
                        <th @if($sortable && in_array($th, $sortableColumns))
                                class="cursor-pointer"
                            data-sort-dir="no-sort"
                            data-can-sort="true"
                            data-column-index="{{ $checkable ? count($indices) : count($indices)-1}}"
                            onclick="sortTableByColumn(this, '{{$name}}')" @endif>
                            <span class="peer cursor-pointer">{{ str_replace('_', ' ', $columnAliases[$th] ?? $th ) }}</span>
                            @if($sortable && in_array($th, $sortableColumns))
                                <x-bladewind::icon
                                        name="funnel" class="size-3 opacity-40 peer-hover:opacity-80 no-sort"/>
                                <x-bladewind::icon
                                        name="arrow-long-up"
                                        class="size-3 opacity-60 peer-hover:opacity-90 sort-asc hidden"/>
                                <x-bladewind::icon
                                        name="arrow-long-down"
                                        class="size-3 opacity-60 peer-hover:opacity-90 sort-desc hidden"/>
                            @endif
                        </th>
                    @endforeach
                    @if(!empty($actionIcons))
                        <th class="!text-right">{{$actionsTitle}}</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @if($totalRecords > 0 && $layout == 'auto')
                    @if($canGroup)
                        @foreach($groupedData as $heading => $rows)
                            <tr class="no-hover">
                                <td colspan="{{count($tableHeadings)}}" @class([
                                        "no-hover",
                                        "group-heading" => empty($groupHeadingCss),
                                        "$groupHeadingCss" => !empty($groupHeadingCss)])>{{ $heading }}</td>
                            </tr>
                            @foreach($rows as $row)
                                @php $row_id =  $row['id'] ?? uniqid(); @endphp
                                <tr data-id="{{ $row_id }}">
                                    @foreach($tableHeadings as $th)
                                        <td data-row-id="{{ $row_id }}" data-column="{{ $th }}"
                                            @if(!empty($onclick)) onclick="{!! build_click($onclick, $row) !!}" @endif>{!! $row[$th] !!}</td>
                                    @endforeach
                                    <x-bladewind::table-icons
                                            :icons_array="$actionIcons"
                                            :row="$row"
                                            :onclick="$onclick"/>
                                </tr>
                            @endforeach
                        @endforeach
                    @else
                        @foreach($data as $row)
                            @php
                                $row_id =  $row['id'] ?? uniqid();
                                $row_page = (!$paginated || $loop->iteration < $pageSize) ? 1 : ceil($loop->iteration/$pageSize);
                            @endphp
                            @if(!empty($limit) && $loop->iteration > $limit)
                                @break
                            @endif
                            <tr @class([
                                'hidden' => ($paginated && $row_page != $defaultPage),
                                'cursor-pointer' => !empty($onclick),
                                ]) data-id="{{ $row_id }}" data-page="{{ $row_page }}">
                                @if($showRowNumbers)
                                    <td @if(!empty($onclick)) onclick="{!! build_click($onclick, $row) !!}" @endif>{{$loop->iteration}}</td>
                                @endif
                                @foreach($tableHeadings as $th)
                                    <td data-row-id="{{ $row_id }}"
                                        data-column="{{ $th }}"
                                        @if(!empty($onclick)) onclick="{!! build_click($onclick, $row) !!}" @endif>{!! $row[$th] !!}</td>
                                @endforeach
                                <x-bladewind::table-icons :icons_array="$actionIcons" :row="$row"/>
                            </tr>
                        @endforeach
                    @endif
                    @else
                        <tr>
                            <td colspan="{{ count($tableHeadings) ?: 1 }}" class="text-center">
                                @if($messageAsEmptyState)
                                    <x-bladewind::empty-state
                                            :message="$noDataMessage"
                                            :buttonLabel="$buttonLabel"
                                            :onclick="$onclick"
                                            :image="$image"
                                            :showImage="$showImage"
                                            :heading="$heading"/>
                                @else
                                    {{ $noDataMessage }}
                                @endif
                                <x-bladewind::script :nonce="$nonce">
                                    changeCss('.{{$name}}', 'with-hover-effect', 'remove');
                                    changeCss('.{{$name}}', 'has-no-data');
                                </x-bladewind::script>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                @endif
        </table>
        @if($paginated && !empty($totalRecords))
            <x-bladewind::pagination
                    :style="$paginationStyle"
                    :totalRecords="$totalRecords"
                    :pageSize="$pageSize"
                    :showTotal="$showTotal"
                    :showTotalPages="$showTotalPages"
                    :showPageNumber="$showPageNumber"
                    :label="$totalLabel"
                    :table="$name"
                    :defaultPage="$defaultPage"/>
        @endif
    </div>
</div>
@once
    <x-bladewind::script :nonce="$nonce" :src="asset('vendor/bladewind/js/table.js')"></x-bladewind::script>
@endonce
@if($selectable)
    <x-bladewind::script :nonce="$nonce">listenToSelectableTableRowEvents('{{$name}}');</x-bladewind::script>
    <input type="hidden" name="{{$name}}" class="{{$name}}"/>
@endif
@if(!empty($searchContainer))
    <script>
        let searchContainer = domEl(".{{$searchContainer}}");
        if (searchContainer) {
            searchContainer.innerHTML = '<div class="bw-table-filter-bar-unhinged">' + domEl('div[data-table-name="{{$name}}"]').innerHTML + '</div>';
            domEl('div[data-table-name="{{$name}}"]').remove();
        }
    </script>
@endif

@if($checkable)
    @once
        <div class="hidden size-0 checkbox-template">
            <x-bladewind::checkbox class="!size-5 !mr-0 rounded-md" label_css="mr-0" add_clearing="false"/>
        </div>
        <div class="hidden size-0 partial-check-template">
            <icon name="minus" type="solid"
                  class="hidden stroke-2 rounded-md bg-primary-500 text-white check-icon !size-5 !mb-1 !mt-[4px] !-ml-1"/>
        </div>
    @endonce
    <x-bladewind::script :nonce="$nonce">
        addCheckboxesToTable('.bw-table.{{$name}}');
        @if(!empty($selectedValue))
            checkSelected('.bw-table.{{$name}}', '{{$selectedValue}}')
        @endif
    </x-bladewind::script>
@endif
@if($sortable)
    @once
        <x-bladewind::script :nonce="$nonce">
            const originalTableOrder = new Map();
            // Save the initial order of all tables when the page loads
            window.onload = saveOriginalTableOrder;
        </x-bladewind::script>
    @endonce
@endif