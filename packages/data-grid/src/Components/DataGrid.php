<?php

namespace Mkocansey\Bladewind\DataGrid\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DataGrid extends Component
{
    public function __construct(
        public ?string $name = null,
        public string $label = 'Data grid',
        public array $columns = [],
        public ?array $rows = null,
        public string $rowKey = 'id',
        public mixed $selectable = null,
        public ?string $selectionMode = null,
        public mixed $selected = [],
        public mixed $sortable = null,
        public ?string $sortKey = null,
        public ?string $sortDirection = null,
        public mixed $clientSort = null,
        public mixed $searchable = null,
        public ?string $searchPlaceholder = null,
        public ?string $searchField = null,
        public mixed $clientSearch = null,
        public mixed $paginated = null,
        public ?int $pageSize = null,
        public mixed $paginator = null,
        public mixed $sticky = null,
        public mixed $loading = null,
        public string $emptyText = 'No records found.',
        public mixed $striped = null,
        public mixed $bordered = null,
        public mixed $dense = null,
        public ?string $height = null,
        public string $selectAllLabel = 'Select all rows',
        public string $clearSelectionLabel = 'Clear selection',
    ) {
        $this->name ??= defaultBladewindName('bw-data-grid-');
        $this->selectable ??= config('bladewind.data_grid.selectable', false);
        $this->selectionMode = in_array($this->selectionMode, ['single', 'multiple'], true)
            ? $this->selectionMode
            : config('bladewind.data_grid.selection_mode', 'multiple');
        $this->sortable ??= config('bladewind.data_grid.sortable', false);
        $this->clientSort ??= config('bladewind.data_grid.client_sort', true);
        $this->searchable ??= config('bladewind.data_grid.searchable', false);
        $this->searchPlaceholder ??= config('bladewind.data_grid.search_placeholder', 'Search…');
        $this->clientSearch ??= config('bladewind.data_grid.client_search', true);
        $this->paginated ??= config('bladewind.data_grid.paginated', false);
        $this->pageSize ??= config('bladewind.data_grid.page_size', 25);
        $this->sticky ??= config('bladewind.data_grid.sticky', true);
        $this->loading ??= false;
        $this->striped ??= config('bladewind.data_grid.striped', false);
        $this->bordered ??= config('bladewind.data_grid.bordered', false);
        $this->dense ??= config('bladewind.data_grid.dense', false);
    }

    public function render(): View
    {
        return view('bladewind::components.data-grid.index');
    }
}
