[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/data-grid)](https://packagist.org/packages/bladewindui/data-grid)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Data Grid

A higher-level companion to [Table](https://bladewindui.com/component/table): an accessible data grid with column sorting, filtering, row selection, sticky headers, and both client-side and server-driven state.

## Installation

```bash
composer require bladewindui/data-grid
```

The package installs Bladewind Core, Icon, and Pagination automatically.

## Usage

```blade
<x-bladewind::data-grid
    name="orders-grid"
    label="Orders"
    searchable="true"
    selectable="true"
    paginated="true"
    page-size="10"
    :columns="[
        ['key' => 'reference', 'label' => 'Reference', 'sortable' => true],
        ['key' => 'customer', 'label' => 'Customer', 'sortable' => true],
        ['key' => 'total', 'label' => 'Total', 'align' => 'right', 'sortable' => true, 'format' => fn ($value) => '$'.number_format($value, 2)],
    ]"
    :rows="$orders" />
```

`rows` is an array of associative arrays or objects. Each row's identity comes from `row-key` (default `id`).

## Columns

Each column accepts `key`, `label`, `align` (`left`, `center`, `right`), `width`, `sortable`, `class`, and two callbacks: `format($value, $row)` for display, and an optional `sort($value, $row)` when the sortable value differs from what is displayed (for example a formatted currency string). Columns can also be given as `['key', 'key' => 'Label']` shorthand, exactly like Table.

Skip `columns` and `rows` entirely to fall back to a fully custom layout: a `header` slot for `<th>` content and the default slot for hand-written `<tr>` rows.

## Sorting

Set `sortable="true"` on the grid for every column to be sortable, or set `sortable` per column. Clicking a header cycles none → ascending → descending → none. `client-sort` defaults to `true` and reorders rows in the browser. Set `client-sort="false"` for server-driven grids: clicking a header only updates the sort indicator and emits `bladewind:data-grid:sort-change`, leaving the actual reordering to the application.

## Searching

`searchable="true"` renders a toolbar search field. `client-search` defaults to `true` and filters rows by their rendered cell text. Set `client-search="false"` to filter server-side instead: the grid emits `bladewind:data-grid:search` with the current query on every keystroke and renders no client-side filtering itself.

## Selection

`selectable="true"` adds a selection column. `selection-mode` is `multiple` (checkboxes, with a select-all control in the header) or `single` (radio buttons). Pass `selected` with an array of row keys to preselect rows. A selection bar appears above the grid once anything is selected, with a clear-selection control and an optional `bulk-actions` slot for custom buttons.

## Pagination and server-driven state

Set `paginated="true"` with `page-size` for client-side pagination — the grid renders its own prev/next footer and keeps it in sync with sorting and searching. Pass a Laravel paginator through `paginator` instead for real server-side pagination: the grid renders [Pagination](https://bladewindui.com/component/pagination)'s standard page links, and `rows` should be the paginator's current-page items.

## Loading state

Set `loading="true"` (or call `setDataGridLoading(name, true)`) while an application is fetching new rows for a server-driven grid. The table dims and shows a progress indicator; screen readers see `aria-busy="true"`.

## JavaScript API

Every helper returns `true` when it completes or the requested state already applies, and `false` when the target is missing, disabled, or a cancelable event was prevented.

```javascript
sortDataGrid('orders-grid', 'total', 'desc');
setDataGridPage('orders-grid', 2);
selectAllDataGridRows('orders-grid', true);
clearDataGridSelection('orders-grid');
dataGridSelectedKeys('orders-grid');
setDataGridLoading('orders-grid', true);
resetDataGrid('orders-grid');
```

## Events

Data Grid emits cancelable `before-sort-change`, `before-select-change`, and `before-page-change`, and `sort-change`, `select-change`, `page-change`, and `search` after changes. All event names start with `bladewind:data-grid:`. Details include the grid name and, depending on the event, the sort key/direction, the selected row keys, the page, or the search query.

## Accessibility

The grid renders a native `<table>` with `scope="col"` headers, `aria-sort` on sortable columns, and `aria-selected` on selected rows, so every interaction — sorting, selecting, paging, searching — happens through real, independently keyboard-operable controls rather than a hand-rolled widget.

## Documentation

Full examples and the complete attribute tables are available at [bladewindui.com/component/data-grid](https://bladewindui.com/component/data-grid).

## Livewire

Client-side sort, search, pagination, and selection state live in the grid's own DOM (`data-*` attributes and checkbox state), not in Livewire's component state, so they survive re-renders the grid itself triggers. An unrelated Livewire re-render of the surrounding component can still reset that state, so wrap the grid in `wire:ignore` if it lives inside a component that re-renders for reasons unrelated to the grid — or set `client-sort="false"`/`client-search="false"` and drive the grid from Livewire via the `before-*`/`*-change` events instead.

## License

MIT. See the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
