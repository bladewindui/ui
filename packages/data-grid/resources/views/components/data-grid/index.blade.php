@props([
    'name' => null,
    'label' => 'Data grid',
    'columns' => [],
    'rows' => null,
    'rowKey' => 'id',
    'selectable' => null,
    'selectionMode' => null,
    'selected' => [],
    'sortable' => null,
    'sortKey' => null,
    'sortDirection' => null,
    'clientSort' => null,
    'searchable' => null,
    'searchPlaceholder' => null,
    'searchField' => null,
    'clientSearch' => null,
    'paginated' => null,
    'pageSize' => null,
    'paginator' => null,
    'sticky' => null,
    'loading' => null,
    'emptyText' => 'No records found.',
    'striped' => null,
    'bordered' => null,
    'dense' => null,
    'height' => null,
    'selectAllLabel' => 'Select all rows',
    'clearSelectionLabel' => 'Clear selection',
])
@php
    $name = preg_replace('/[^A-Za-z0-9_-]/', '-', trim((string) $name));
    if ($name === '') $name = defaultBladewindName('bw-data-grid-');

    $selectable = parseBladewindVariable($selectable ?? config('bladewind.data_grid.selectable', false));
    $selectionMode = in_array($selectionMode, ['single', 'multiple'], true) ? $selectionMode : config('bladewind.data_grid.selection_mode', 'multiple');
    $sortable = parseBladewindVariable($sortable ?? config('bladewind.data_grid.sortable', false));
    $clientSort = parseBladewindVariable($clientSort ?? config('bladewind.data_grid.client_sort', true));
    $searchable = parseBladewindVariable($searchable ?? config('bladewind.data_grid.searchable', false));
    $searchPlaceholder = $searchPlaceholder ?? config('bladewind.data_grid.search_placeholder', 'Search…');
    $clientSearch = parseBladewindVariable($clientSearch ?? config('bladewind.data_grid.client_search', true));
    $paginated = parseBladewindVariable($paginated ?? config('bladewind.data_grid.paginated', false));
    $pageSize = parseBladewindVariable($pageSize ?? config('bladewind.data_grid.page_size', 25), 'int');
    $sticky = parseBladewindVariable($sticky ?? config('bladewind.data_grid.sticky', true));
    $loading = parseBladewindVariable($loading ?? false);
    $striped = parseBladewindVariable($striped ?? config('bladewind.data_grid.striped', false));
    $bordered = parseBladewindVariable($bordered ?? config('bladewind.data_grid.bordered', false));
    $dense = parseBladewindVariable($dense ?? config('bladewind.data_grid.dense', false));

    $hasColumns = ! empty($columns);
    $hasRows = is_array($rows);

    $isLengthAwarePaginator = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $isPaginator = $isLengthAwarePaginator || $paginator instanceof \Illuminate\Contracts\Pagination\Paginator;
    if ($isPaginator) $paginated = true;

    $normalisedColumns = [];
    if ($hasColumns) {
        foreach ($columns as $key => $column) {
            if (is_string($column)) {
                $column = is_string($key) ? ['key' => $key, 'label' => $column] : ['key' => $column];
            } elseif (is_string($key) && ! isset($column['key'])) {
                $column['key'] = $key;
            }
            if (empty($column['key'])) continue;

            $align = $column['align'] ?? 'left';
            $normalisedColumns[] = [
                'key' => $column['key'],
                'label' => $column['label'] ?? str_replace('_', ' ', $column['key']),
                'align' => in_array($align, ['left', 'center', 'right'], true) ? $align : 'left',
                'width' => $column['width'] ?? null,
                'sortable' => $sortable || (bool) ($column['sortable'] ?? false),
                'format' => $column['format'] ?? null,
                'sort' => $column['sort'] ?? null,
                'class' => $column['class'] ?? '',
            ];
        }
    }
    $anyColumnSortable = collect($normalisedColumns)->contains('sortable', true);

    $selected = is_array($selected) ? $selected : array_filter(explode(',', str_replace(' ', '', (string) $selected)));
    $selectedKeys = array_map('strval', $selected);
    $preparedRows = [];
    if ($hasRows) {
        $totalRecords = count($rows);
        $defaultPage = 1;

        foreach ($rows as $index => $row) {
            $rowArray = (array) $row;
            $rowKeyValue = (string) ($rowArray[$rowKey] ?? $index);
            $display = [];
            $sortValues = [];
            $searchParts = [];

            foreach ($normalisedColumns as $column) {
                $raw = data_get($rowArray, $column['key']);
                $display[$column['key']] = $column['format'] ? call_user_func($column['format'], $raw, $rowArray) : $raw;
                $sortSource = $column['sort'] ? call_user_func($column['sort'], $raw, $rowArray) : $raw;
                $sortValues[$column['key']] = is_scalar($sortSource) ? (string) $sortSource : (string) $display[$column['key']];
                if (is_scalar($display[$column['key']])) $searchParts[] = (string) $display[$column['key']];
                elseif (is_scalar($raw)) $searchParts[] = (string) $raw;
            }

            $page = $paginated && ! $isPaginator ? (int) ceil(($index + 1) / max(1, $pageSize)) : 1;

            $preparedRows[] = [
                'key' => $rowKeyValue,
                'display' => $display,
                'sortValues' => $sortValues,
                'search' => mb_strtolower(implode(' ', $searchParts)),
                'page' => $page,
                'selected' => in_array($rowKeyValue, $selectedKeys, true),
                'raw' => $rowArray,
            ];
        }
    } else {
        $totalRecords = 0;
        $defaultPage = 1;
    }

    $columnCount = count($normalisedColumns) + ($selectable ? 1 : 0);
    $clientTotalPages = max(1, (int) ceil($totalRecords / max(1, $pageSize)));
    $clientPageTo = min($pageSize, $totalRecords);
    $ariaRowCount = $isLengthAwarePaginator ? $paginator->total() : $totalRecords;
    $sortAriaKey = $sortKey;
    $sortAriaDirection = in_array($sortDirection, ['asc', 'desc'], true) ? $sortDirection : null;

    $rootAttributes = $attributes->exceptPropAliases(get_defined_vars())->class([
        'bw-data-grid',
        'bw-data-grid-sticky' => $sticky,
        'bw-data-grid-striped' => $striped,
        'bw-data-grid-bordered' => $bordered,
        'bw-data-grid-dense' => $dense,
    ]);
@endphp

<div {{ $rootAttributes }}
    data-bw-data-grid
    data-name="{{ $name }}"
    data-row-key="{{ $rowKey }}"
    data-selectable="{{ $selectable ? 'true' : 'false' }}"
    data-selection-mode="{{ $selectionMode }}"
    data-sortable="{{ $anyColumnSortable ? 'true' : 'false' }}"
    data-client-sort="{{ $clientSort ? 'true' : 'false' }}"
    data-searchable="{{ $searchable ? 'true' : 'false' }}"
    data-client-search="{{ $clientSearch ? 'true' : 'false' }}"
    data-paginated="{{ $paginated && ! $isPaginator ? 'true' : 'false' }}"
    data-page-size="{{ $pageSize }}"
    data-loading="{{ $loading ? 'true' : 'false' }}">

    @if($searchable || isset($toolbar))
        <div class="bw-data-grid-toolbar">
            @if($searchable)
                <div class="bw-data-grid-search">
                    <input type="text" class="bw-input small focus:outline-primary-500 focus:border-primary-500 bw-data-grid-search-input" data-bw-data-grid-search
                        aria-label="Search {{ $label }}" placeholder="{{ $searchPlaceholder }}" autocomplete="off" />
                    <div class="bw-data-grid-search-prefix prefix">
                        <x-bladewind::icon name="magnifying-glass" class="!size-[18px] !stroke-2 !opacity-70" />
                    </div>
                </div>
            @endif
            @isset($toolbar)<div class="bw-data-grid-toolbar-content">{{ $toolbar }}</div>@endisset
        </div>
    @endif

    @if($selectable)
        <div class="bw-data-grid-selection-bar" data-bw-data-grid-selection-bar hidden>
            <span class="bw-data-grid-selection-count" data-bw-data-grid-selection-count>0 selected</span>
            <button type="button" class="bw-data-grid-clear-selection" data-bw-data-grid-clear-selection="{{ $name }}">{{ $clearSelectionLabel }}</button>
            @isset($bulkActions)<div class="bw-data-grid-bulk-actions">{{ $bulkActions }}</div>@endisset
        </div>
    @endif

    <div class="bw-data-grid-scroll" data-bw-data-grid-scroll @if($height) style="max-height: {{ $height }}" @endif>
        <div class="bw-data-grid-loading-overlay" data-bw-data-grid-loading-overlay aria-hidden="true">
            <x-bladewind::spinner size="small" color="primary" />
        </div>
        <table class="{{ $name }} bw-data-grid-table" data-bw-data-grid-table data-name="{{ $name }}"
            @if($paginated && ! $isPaginator) data-current-page="{{ $defaultPage }}" @endif
            aria-label="{{ $label }}" aria-busy="{{ $loading ? 'true' : 'false' }}" aria-rowcount="{{ $ariaRowCount }}">
            <thead data-bw-data-grid-head>
                <tr>
                    @if($selectable)
                        <th class="bw-data-grid-select-col" scope="col">
                            @if($selectionMode === 'multiple')
                                <input type="checkbox" class="bw-data-grid-checkbox" data-bw-data-grid-select-all aria-label="{{ $selectAllLabel }}" />
                            @else
                                <span class="sr-only">{{ $selectAllLabel }}</span>
                            @endif
                        </th>
                    @endif
                    @if($hasColumns)
                        @foreach($normalisedColumns as $column)
                            @php
                                $isSorted = $sortAriaKey === $column['key'];
                                $ariaSort = $isSorted ? ($sortAriaDirection === 'desc' ? 'descending' : 'ascending') : ($column['sortable'] ? 'none' : null);
                            @endphp
                            <th scope="col" class="bw-data-grid-align-{{ $column['align'] }} {{ $column['class'] }}"
                                @if($column['width']) style="width: {{ $column['width'] }}" @endif
                                data-column="{{ $column['key'] }}"
                                @if($ariaSort) aria-sort="{{ $ariaSort }}" @endif>
                                @if($column['sortable'])
                                    <button type="button" class="bw-data-grid-sort-button" data-bw-data-grid-sort="{{ $column['key'] }}"
                                        data-direction="{{ $isSorted ? $sortAriaDirection : 'none' }}">
                                        <span>{{ $column['label'] }}</span>
                                        <span class="bw-data-grid-sort-icons" aria-hidden="true">
                                            <x-bladewind::icon name="chevron-up" class="bw-data-grid-sort-icon-asc !size-3" />
                                            <x-bladewind::icon name="chevron-down" class="bw-data-grid-sort-icon-desc !size-3" />
                                        </span>
                                    </button>
                                @else
                                    {{ $column['label'] }}
                                @endif
                            </th>
                        @endforeach
                    @else
                        {{ $header ?? '' }}
                    @endif
                </tr>
            </thead>
            <tbody data-bw-data-grid-body>
                @if($hasColumns)
                    @foreach($preparedRows as $entry)
                        <tr data-bw-data-grid-row data-row-key="{{ $entry['key'] }}"
                            @if($paginated && ! $isPaginator) data-page="{{ $entry['page'] }}" @endif
                            data-search="{{ $entry['search'] }}"
                            aria-selected="{{ $entry['selected'] ? 'true' : 'false' }}"
                            @class(['bw-data-grid-row-selected' => $entry['selected']])
                            @if($paginated && ! $isPaginator && $entry['page'] !== $defaultPage) hidden @endif>
                            @if($selectable)
                                <td class="bw-data-grid-select-col">
                                    <input type="{{ $selectionMode === 'single' ? 'radio' : 'checkbox' }}"
                                        class="bw-data-grid-checkbox"
                                        @if($selectionMode === 'single') name="{{ $name }}-select" @endif
                                        data-bw-data-grid-select value="{{ $entry['key'] }}"
                                        @checked($entry['selected']) aria-label="Select row" />
                                </td>
                            @endif
                            @foreach($normalisedColumns as $column)
                                <td data-column="{{ $column['key'] }}" data-sort-value="{{ $entry['sortValues'][$column['key']] }}"
                                    class="bw-data-grid-align-{{ $column['align'] }} {{ $column['class'] }}">{!! $entry['display'][$column['key']] !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr class="bw-data-grid-empty-row" data-bw-data-grid-empty @if($totalRecords > 0) hidden @endif>
                        <td colspan="{{ max(1, $columnCount) }}">{{ $emptyText }}</td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>

    @if($isPaginator)
        <x-bladewind::pagination :paginator="$paginator" />
    @elseif($paginated && $totalRecords > 0)
        <div class="bw-data-grid-pagination" data-bw-data-grid-pagination>
            <div class="bw-data-grid-pagination-summary" data-bw-data-grid-pagination-summary>Showing 1&ndash;{{ $clientPageTo }} of {{ $totalRecords }}</div>
            <div class="bw-data-grid-pagination-controls">
                <button type="button" class="bw-data-grid-pagination-button" data-bw-data-grid-page="prev" aria-label="Previous page" disabled>
                    <x-bladewind::icon name="chevron-left" class="!size-4" />
                </button>
                <span class="bw-data-grid-pagination-label" data-bw-data-grid-pagination-label>Page 1 of {{ $clientTotalPages }}</span>
                <button type="button" class="bw-data-grid-pagination-button" data-bw-data-grid-page="next" aria-label="Next page" @if($clientTotalPages <= 1) disabled @endif>
                    <x-bladewind::icon name="chevron-right" class="!size-4" />
                </button>
            </div>
        </div>
    @endif
</div>
