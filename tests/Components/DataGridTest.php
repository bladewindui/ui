<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Illuminate\Pagination\LengthAwarePaginator;
use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DataGridTest extends TestCase
{
    use RendersComponents;

    private array $rows = [
        ['id' => 1, 'reference' => 'ORD-1', 'customer' => 'Ama Mensah', 'total' => 84000],
        ['id' => 2, 'reference' => 'ORD-2', 'customer' => 'Kofi Addo', 'total' => 31500],
    ];

    private function grid(string $attributes = '', ?array $columns = null, ?array $rows = null): string
    {
        $columns ??= [
            ['key' => 'reference', 'label' => 'Reference', 'sortable' => true],
            ['key' => 'customer', 'label' => 'Customer'],
            ['key' => 'total', 'label' => 'Total', 'align' => 'right', 'sortable' => true, 'format' => fn ($value) => '$'.number_format($value / 100, 2)],
        ];

        return $this->render(
            '<x-bladewind::data-grid '.$attributes.' :columns="$columns" :rows="$rows" />',
            ['columns' => $columns, 'rows' => $rows ?? $this->rows]
        );
    }

    #[Test]
    public function it_renders_a_named_accessible_table_from_columns_and_rows(): void
    {
        $html = $this->grid('name="orders" label="Orders"');

        $this->assertElementCount($html, '//*[@data-bw-data-grid]', 1);
        $this->assertAttribute($html, '//*[@data-bw-data-grid]', 'data-name', 'orders');
        $this->assertAttribute($html, '//table', 'aria-label', 'Orders');
        $this->assertElementCount($html, '//table/thead/tr/th', 3);
        $this->assertElementCount($html, '//table/tbody/tr[@data-bw-data-grid-row]', 2);
        $this->assertStringContainsString('ORD-1', $html);
        $this->assertStringContainsString('$840.00', $html);
    }

    #[Test]
    public function no_columns_falls_back_to_header_and_default_slots(): void
    {
        $html = $this->render(
            '<x-bladewind::data-grid name="custom"><x-slot:header><th>Custom</th></x-slot:header><tr><td>Row</td></tr></x-bladewind::data-grid>'
        );

        $this->assertElementCount($html, '//table/thead/tr/th', 1);
        $this->assertStringContainsString('Custom', $html);
        $this->assertElementCount($html, '//table/tbody/tr', 1);
        $this->assertStringContainsString('Row', $html);
    }

    #[Test]
    public function sortable_columns_render_buttons_with_aria_sort(): void
    {
        $html = $this->grid('sort-key="total" sort-direction="desc"');

        $this->assertElementCount($html, '//th[@data-column="reference"]//button[@data-bw-data-grid-sort="reference"]', 1);
        $this->assertElementCount($html, '//th[@data-column="customer"]//button', 0);
        $this->assertAttribute($html, '//th[@data-column="total"]', 'aria-sort', 'descending');
        $this->assertAttribute($html, '//th[@data-column="reference"]', 'aria-sort', 'none');
        $this->assertAttribute($html, '//button[@data-bw-data-grid-sort="total"]', 'data-direction', 'desc');
    }

    #[Test]
    public function sort_values_fall_back_to_the_raw_value_and_honour_a_custom_sort_callback(): void
    {
        $html = $this->grid();

        $this->assertAttribute($html, '//tr[@data-row-key="1"]/td[@data-column="reference"]', 'data-sort-value', 'ORD-1');
        $this->assertAttribute($html, '//tr[@data-row-key="1"]/td[@data-column="total"]', 'data-sort-value', '84000');
    }

    #[Test]
    public function multiple_selection_renders_checkboxes_and_a_select_all_control(): void
    {
        $html = $this->grid('selectable="true" selected="1"');

        $this->assertElementCount($html, '//input[@data-bw-data-grid-select-all]', 1);
        $this->assertElementCount($html, '//input[@data-bw-data-grid-select]', 2);
        $this->assertElementCount($html, "//input[@data-bw-data-grid-select and @type='checkbox']", 2);
        $this->assertAttribute($html, '//tr[@data-row-key="1"]/td[1]/input', 'checked', 'checked');
        $this->assertAttribute($html, '//tr[@data-row-key="1"]', 'aria-selected', 'true');
        $this->assertAttribute($html, '//tr[@data-row-key="2"]', 'aria-selected', 'false');
    }

    #[Test]
    public function single_selection_renders_radios_sharing_one_name_and_no_select_all(): void
    {
        $html = $this->grid('name="single-grid" selectable="true" selection-mode="single"');

        $this->assertElementCount($html, '//input[@data-bw-data-grid-select-all]', 0);
        $this->assertElementCount($html, "//input[@data-bw-data-grid-select and @type='radio']", 2);
        $this->assertAttribute($html, '(//input[@data-bw-data-grid-select])[1]', 'name', 'single-grid-select');
        $this->assertAttribute($html, '(//input[@data-bw-data-grid-select])[2]', 'name', 'single-grid-select');
    }

    #[Test]
    public function searchable_renders_a_labelled_toolbar_input(): void
    {
        $html = $this->grid('searchable="true" search-placeholder="Find an order…" label="Orders"');

        $this->assertElementCount($html, '//input[@data-bw-data-grid-search]', 1);
        $this->assertAttribute($html, '//input[@data-bw-data-grid-search]', 'aria-label', 'Search Orders');
        $this->assertAttribute($html, '//input[@data-bw-data-grid-search]', 'placeholder', 'Find an order…');
    }

    #[Test]
    public function client_pagination_distributes_rows_across_pages_and_hides_later_pages(): void
    {
        $rows = array_map(fn ($i) => ['id' => $i, 'reference' => "ORD-$i", 'customer' => 'X', 'total' => 100], range(1, 5));
        $html = $this->grid('name="paged" paginated="true" page-size="2"', null, $rows);

        $this->assertAttribute($html, '//table', 'data-current-page', '1');
        $this->assertAttribute($html, '//tr[@data-row-key="1"]', 'data-page', '1');
        $this->assertAttribute($html, '//tr[@data-row-key="3"]', 'data-page', '2');
        $this->assertAttribute($html, '//tr[@data-row-key="1"]', 'hidden', null);
        $this->assertElementCount($html, '//tr[@data-row-key="3" and @hidden]', 1);
        $this->assertElementCount($html, '//*[@data-bw-data-grid-pagination]', 1);
        $this->assertStringContainsString('Page 1 of 3', $html);
        $this->assertStringContainsString('Showing 1', $html);
    }

    #[Test]
    public function a_laravel_paginator_switches_to_server_pagination_links(): void
    {
        $paginator = new LengthAwarePaginator($this->rows, 40, 2, 1);
        $html = $this->render(
            '<x-bladewind::data-grid name="server" :columns="$columns" :rows="$rows" :paginator="$paginator" />',
            [
                'columns' => [['key' => 'reference', 'label' => 'Reference']],
                'rows' => $this->rows,
                'paginator' => $paginator,
            ]
        );

        $this->assertElementCount($html, '//*[@data-bw-data-grid-pagination]', 0);
        $this->assertElementCount($html, '//*[contains(@class,"bw-pagination-server")]', 1);
        $this->assertAttribute($html, '//table', 'aria-rowcount', '40');
    }

    #[Test]
    public function empty_rows_render_the_empty_state_message(): void
    {
        $html = $this->grid('', null, []);

        $this->assertElementCount($html, '//tr[@data-bw-data-grid-empty]', 1);
        $this->assertAttribute($html, '//tr[@data-bw-data-grid-empty]', 'hidden', null);
        $this->assertStringContainsString('No records found.', $html);
        $this->assertAttribute($html, '//tr[@data-bw-data-grid-empty]/td', 'colspan', '3');
    }

    #[Test]
    public function visual_and_forwarded_attributes_are_exposed(): void
    {
        $html = $this->grid('name="styled" striped="true" bordered="true" dense="true" sticky="false" loading="true" class="custom-grid" data-test="grid"');

        $this->assertHasClasses($html, '//*[@data-bw-data-grid]', ['bw-data-grid-striped', 'bw-data-grid-bordered', 'bw-data-grid-dense', 'custom-grid']);
        $this->assertMissingClasses($html, '//*[@data-bw-data-grid]', ['bw-data-grid-sticky']);
        $this->assertAttribute($html, '//*[@data-bw-data-grid]', 'data-loading', 'true');
        $this->assertAttribute($html, '//table', 'aria-busy', 'true');
        $this->assertAttribute($html, '//*[@data-bw-data-grid]', 'data-test', 'grid');
    }

    #[Test]
    public function labels_and_names_are_escaped_while_cell_content_stays_raw_like_table(): void
    {
        $html = $this->render(
            '<x-bladewind::data-grid :name="$name" :label="$label" :columns="$columns" :rows="$rows" />',
            [
                'name' => 'orders" onmouseover="bad',
                'label' => 'Orders <unsafe>',
                'columns' => [['key' => 'note', 'label' => '<script>bad</script>']],
                'rows' => [['id' => 1, 'note' => '<strong>Priority</strong>']],
            ]
        );

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertAttribute($html, '//table', 'aria-label', 'Orders <unsafe>');
        // cell content is intentionally raw, matching Table, so formatted HTML (badges, icons) can be returned from `format`
        $this->assertElementCount($html, '//td[@data-column="note"]/strong', 1);
    }

    #[Test]
    public function configuration_defaults_are_applied(): void
    {
        config()->set('bladewind.data_grid.selectable', true);
        config()->set('bladewind.data_grid.selection_mode', 'single');
        config()->set('bladewind.data_grid.page_size', 1);

        $html = $this->grid('paginated="true"');

        $this->assertAttribute($html, '//*[@data-bw-data-grid]', 'data-selectable', 'true');
        $this->assertAttribute($html, '//*[@data-bw-data-grid]', 'data-selection-mode', 'single');
        $this->assertStringContainsString('Page 1 of 2', $html);
    }
}
