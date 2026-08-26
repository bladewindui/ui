<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TableTest extends TestCase
{
    use RendersComponents;

    private const TABLE = '//table';

    #[Test]
    public function it_renders_the_documented_default_table_classes(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl"><tr><td>a</td></tr></x-bladewind::table>');

        $this->assertHasClasses($html, self::TABLE, [
            'bw-table',
            'w-full',
            'tbl',
            'divided',
            'with-hover-effect',
            'uppercase-headers',
        ]);
        $this->assertMissingClasses($html, self::TABLE, [
            'thin', 'striped', 'celled', 'compact', 'sortable', 'paginated',
            'selectable', 'checkable', 'transparent',
        ]);
    }

    #[Test]
    public function the_slot_becomes_the_table_body(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl"><tr><td>cell</td></tr></x-bladewind::table>');

        $this->assertElementCount($html, '//tbody/tr/td', 1);
        $this->assertStringContainsString('cell', $html);
    }

    #[Test]
    public function a_header_slot_renders_a_thead_row(): void
    {
        $html = $this->render(
            "<x-bladewind::table name=\"tbl\">\n<x-slot:header><th>When</th></x-slot:header>\n<tr><td>a</td></tr>\n</x-bladewind::table>"
        );

        $this->assertElementCount($html, '//thead/tr/th', 1);
        $this->assertStringContainsString('When', $html);
    }

    #[Test]
    public function without_a_header_slot_no_thead_is_rendered(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl"><tr><td>a</td></tr></x-bladewind::table>');

        $this->assertNoElement($html, '//thead');
    }

    #[Test]
    public function divider_thin_adds_the_thin_modifier(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl" divider="thin"><tr><td>a</td></tr></x-bladewind::table>');

        $this->assertHasClasses($html, self::TABLE, ['divided', 'thin']);
    }

    #[Test]
    public function thin_is_ignored_when_the_table_is_not_divided(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="tbl" divided="false" divider="thin"><tr><td>a</td></tr></x-bladewind::table>'
        );

        $this->assertMissingClasses($html, self::TABLE, ['divided', 'thin']);
    }

    #[Test]
    public function checkable_implies_selectable(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl" checkable="true"><tr><td>a</td></tr></x-bladewind::table>');

        $this->assertHasClasses($html, self::TABLE, ['checkable', 'selectable']);
    }

    #[Test]
    public function has_border_puts_the_border_on_the_outer_wrapper(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl" has_border="true"><tr><td>a</td></tr></x-bladewind::table>');

        $this->assertHasClasses($html, $this->withClass('border-collapse'), ['border', 'border-gray-200/70']);
    }

    #[Test]
    public function celled_suppresses_the_outer_border(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="tbl" has_border="true" celled="true"><tr><td>a</td></tr></x-bladewind::table>'
        );

        $this->assertMissingClasses($html, $this->withClass('border-collapse'), ['border-gray-200/70']);
        $this->assertHasClasses($html, self::TABLE, ['celled']);
    }

    #[Test]
    public function has_shadow_adds_the_drop_shadow_classes(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl" has_shadow="true"><tr><td>a</td></tr></x-bladewind::table>');

        $this->assertHasClasses($html, self::TABLE, ['drop-shadow', 'shadow']);
    }

    #[Test]
    public function boolean_modifiers_each_add_their_own_class(): void
    {
        foreach (['striped', 'compact', 'transparent', 'sortable', 'selectable'] as $prop) {
            $html = $this->render(
                '<x-bladewind::table name="tbl" '.$prop.'="true"><tr><td>a</td></tr></x-bladewind::table>'
            );

            $this->assertHasClasses($html, self::TABLE, [$prop]);
        }
    }

    #[Test]
    public function uppercasing_false_drops_the_header_modifier(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl" uppercasing="false"><tr><td>a</td></tr></x-bladewind::table>');

        $this->assertMissingClasses($html, self::TABLE, ['uppercase-headers']);
    }

    #[Test]
    public function searchable_renders_the_filter_bar_with_a_scoped_input(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl" searchable="true"><tr><td>a</td></tr></x-bladewind::table>');

        $this->assertElementCount($html, $this->withClass('bw-table-filter-bar'), 1);
        $this->assertAttribute($html, $this->withClass('bw-table-filter-bar'), 'data-table-name', 'tbl');
        // parseBladewindName() inside the input component collapses the dashes
        $this->assertElementCount($html, '//input[@name="bw_search_tbl"]', 1);
    }

    #[Test]
    public function names_with_spaces_or_dashes_are_normalised_to_underscores(): void
    {
        $html = $this->render('<x-bladewind::table name="my table"><tr><td>a</td></tr></x-bladewind::table>');

        $this->assertHasClasses($html, self::TABLE, ['my_table']);
    }

    #[Test]
    public function paginated_records_the_current_page_on_the_table(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="tbl" paginated="true" default_page="1"><tr><td>a</td></tr></x-bladewind::table>'
        );

        $this->assertAttribute($html, self::TABLE, 'data-current-page', '1');
        $this->assertHasClasses($html, self::TABLE, ['paginated']);
    }




    /**
     * The data-driven layout is the path improvements.md item 4 proposes replacing
     * with a :columns model. Pinned so the new API has to stay additive.
     */
    #[Test]
    public function a_data_array_builds_headings_and_rows_automatically(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="tbl" :data="$rows" />',
            ['rows' => [['when' => 'today', 'amount' => 10], ['when' => 'tomorrow', 'amount' => 20]]]
        );

        $this->assertElementCount($html, '//thead/tr/th', 2);
        $this->assertElementCount($html, '//tbody/tr', 2);
        $this->assertStringContainsString('today', $html);
        $this->assertStringContainsString('amount', $html);
    }

    #[Test]
    public function exclude_columns_removes_them_from_the_rendered_table(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="tbl" :data="$rows" exclude_columns="amount" />',
            ['rows' => [['when' => 'today', 'amount' => 10]]]
        );

        $this->assertElementCount($html, '//thead/tr/th', 1);
        $this->assertElementCount($html, '//tbody/tr/td', 1);
        $this->assertNoElement($html, '//td[@data-column="amount"]');
    }

    /**
     * Excluding a column removes it from the rendered table but not from the JSON
     * blob the component writes for its client-side search and pagination. Worth
     * knowing before item 4 designs the :columns API — "excluded" currently means
     * "not displayed", not "not sent".
     */
    #[Test]
    public function excluded_columns_are_still_present_in_the_client_side_data(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="tbl" :data="$rows" exclude_columns="amount" />',
            ['rows' => [['when' => 'today', 'amount' => 10]]]
        );

        $this->assertStringContainsString('"amount":10', $html);
    }

    #[Test]
    public function column_aliases_rename_the_headings(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="tbl" :data="$rows" :column_aliases="$aliases" />',
            ['rows' => [['when' => 'today']], 'aliases' => ['when' => 'Date']]
        );

        $this->assertStringContainsString('Date', $html);
    }

    #[Test]
    public function empty_data_renders_the_no_data_message(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl" :data="$rows" />', ['rows' => []]);

        $this->assertStringContainsString('No records to display', $html);
    }

    /**
     * #601: the empty-data row used to be emitted with no opening <tbody> and a
     * stray closing one, because <tbody> sat inside the has-rows branch while
     * </tbody> sat after the @endif.
     */
    #[Test]
    public function the_empty_data_row_sits_inside_a_balanced_tbody(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl" :data="$rows" />', ['rows' => []]);

        $this->assertElementCount($html, '//tbody/tr/td[@colspan]', 1);
        $this->assertSame(1, substr_count($html, '<tbody>'));
        $this->assertSame(1, substr_count($html, '</tbody>'));
    }

    #[Test]
    public function a_populated_table_still_has_exactly_one_balanced_tbody(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="tbl" :data="$rows" />',
            ['rows' => [['when' => 'today'], ['when' => 'tomorrow']]]
        );

        $this->assertSame(1, substr_count($html, '<tbody>'));
        $this->assertSame(1, substr_count($html, '</tbody>'));
        $this->assertElementCount($html, '//tbody/tr', 2);
    }

    /**
     * #602: this branch used to write a raw <script :nonce="$nonce">, so the Blade
     * attribute shipped to the browser uncompiled and the script carried no nonce —
     * blocked outright under a nonce-based CSP.
     */
    #[Test]
    public function the_empty_data_script_goes_through_the_script_component(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl" :data="$rows" />', ['rows' => []]);

        $this->assertStringNotContainsString(':nonce=', $html);
        $this->assertStringContainsString('has-no-data', $html);
    }

    #[Test]
    public function the_empty_data_script_carries_a_configured_nonce(): void
    {
        config(['bladewind.script.nonce' => 'abc123']);

        $html = $this->render('<x-bladewind::table name="tbl" :data="$rows" />', ['rows' => []]);

        $this->assertStringContainsString('abc123', $html);
    }

    /**
     * With no rows there are no headings either, so the placeholder cell used to
     * render colspan="0", which is not a legal value. Item 4's :columns API gives
     * the component a column list that survives an empty result; until then it
     * spans at least one.
     */
    #[Test]
    public function the_empty_data_cell_spans_at_least_one_column(): void
    {
        $html = $this->render('<x-bladewind::table name="tbl" :data="$rows" />', ['rows' => []]);

        $this->assertStringNotContainsString('colspan="0"', $html);
        $this->assertAttribute($html, '//tbody/tr/td[@colspan]', 'colspan', '1');
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config([
            'bladewind.table.divider' => 'thin',
            'bladewind.table.has_hover' => false,
            'bladewind.table.striped' => true,
        ]);

        $html = $this->render('<x-bladewind::table name="tbl"><tr><td>a</td></tr></x-bladewind::table>');

        $this->assertHasClasses($html, self::TABLE, ['thin', 'striped']);
        $this->assertMissingClasses($html, self::TABLE, ['with-hover-effect']);
    }
}
