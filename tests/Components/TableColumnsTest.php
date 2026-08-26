<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * #592 — the :columns model. Slots stay as the escape hatch, so every existing
 * usage is untouched; TableTest covers that side.
 */
class TableColumnsTest extends TestCase
{
    use RendersComponents;

    private array $rows = [
        ['when' => 'today', 'amount' => 10],
        ['when' => 'tomorrow', 'amount' => 20],
    ];

    private function table(string $columns, ?array $rows = null, string $attrs = ''): string
    {
        return $this->render(
            '<x-bladewind::table name="tbl" :columns="'.$columns.'" :rows="$rows" '.$attrs.' />',
            ['rows' => $rows ?? $this->rows]
        );
    }

    #[Test]
    public function a_column_model_renders_headings_and_rows(): void
    {
        $html = $this->table('$cols', null);

        $this->assertElementCount($html, '//thead/tr/th', 2);
        $this->assertElementCount($html, '//tbody/tr', 2);
        $this->assertElementCount($html, '//td[@data-column="amount"]', 2);
    }

    protected function setUp(): void
    {
        parent::setUp();
        view()->share('cols', [
            ['key' => 'when', 'label' => 'When'],
            ['key' => 'amount', 'label' => 'Amount', 'align' => 'right'],
        ]);
    }

    #[Test]
    public function alignment_lands_on_both_the_heading_and_the_cells(): void
    {
        $html = $this->table('$cols');

        $this->assertHasClasses($html, '//thead/tr/th[2]', ['text-right']);
        $this->assertHasClasses($html, '//td[@data-column="amount"]', ['text-right']);
        $this->assertHasClasses($html, '//td[@data-column="when"]', ['text-left']);
    }

    #[Test]
    public function a_width_becomes_an_inline_style(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="t" :columns="[[\'key\' => \'when\', \'width\' => \'160px\']]" :rows="$rows" />',
            ['rows' => $this->rows]
        );

        $this->assertAttributeContains($html, '//thead/tr/th[1]', 'style', '160px');
    }

    #[Test]
    public function keys_only_shorthand_derives_the_labels(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="t" :columns="[\'when\', \'amount\']" :rows="$rows" />',
            ['rows' => $this->rows]
        );

        $this->assertElementCount($html, '//thead/tr/th', 2);
        $this->assertStringContainsString('when', $html);
        $this->assertStringContainsString('amount', $html);
    }

    #[Test]
    public function key_to_label_shorthand_is_accepted(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="t" :columns="[\'when\' => \'Date placed\']" :rows="$rows" />',
            ['rows' => $this->rows]
        );

        $this->assertStringContainsString('Date placed', $html);
        $this->assertElementCount($html, '//td[@data-column="when"]', 2);
    }

    #[Test]
    public function underscored_keys_get_a_readable_default_label(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="t" :columns="[\'placed_on\']" :rows="$rows" />',
            ['rows' => [['placed_on' => 'x']]]
        );

        $this->assertStringContainsString('placed on', $html);
    }

    #[Test]
    public function a_format_callback_transforms_the_value(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="t" :columns="$cols" :rows="$rows" />',
            [
                'cols' => [['key' => 'amount', 'format' => fn ($v) => 'GHS '.number_format($v, 2)]],
                'rows' => [['amount' => 1234.5]],
            ]
        );

        $this->assertStringContainsString('GHS 1,234.50', $html);
    }

    #[Test]
    public function a_format_callback_receives_the_whole_row(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="t" :columns="$cols" :rows="$rows" />',
            [
                'cols' => [['key' => 'first', 'format' => fn ($v, $row) => $v.' '.$row['last']]],
                'rows' => [['first' => 'Ada', 'last' => 'Lovelace']],
            ]
        );

        $this->assertStringContainsString('Ada Lovelace', $html);
    }

    #[Test]
    public function a_column_class_is_applied_to_heading_and_cells(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="t" :columns="[[\'key\' => \'when\', \'class\' => \'font-mono\']]" :rows="$rows" />',
            ['rows' => $this->rows]
        );

        $this->assertHasClasses($html, '//thead/tr/th[1]', ['font-mono']);
        $this->assertHasClasses($html, '//td[@data-column="when"]', ['font-mono']);
    }

    #[Test]
    public function a_sortable_column_turns_the_table_sortable(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="t" :columns="[[\'key\' => \'when\', \'sortable\' => true], \'amount\']" :rows="$rows" />',
            ['rows' => $this->rows]
        );

        $this->assertHasClasses($html, '//table', ['sortable']);
        $this->assertAttribute($html, '//thead/tr/th[1]', 'data-can-sort', 'true');
        $this->assertAttribute($html, '//thead/tr/th[2]', 'data-can-sort', null);
    }

    #[Test]
    public function a_missing_key_renders_an_empty_cell_rather_than_failing(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="t" :columns="[\'nope\']" :rows="$rows" />',
            ['rows' => $this->rows]
        );

        $this->assertElementCount($html, '//td[@data-column="nope"]', 2);
    }

    // ---- empty state ----------------------------------------------------

    #[Test]
    public function no_rows_falls_back_to_the_no_data_message(): void
    {
        $html = $this->table('$cols', []);

        $this->assertStringContainsString('No records to display', $html);
        $this->assertAttribute($html, '//tbody/tr/td[@colspan]', 'colspan', '2');
    }

    #[Test]
    public function an_empty_slot_replaces_the_no_data_message(): void
    {
        $html = $this->render(
            "<x-bladewind::table name=\"t\" :columns=\"['when']\" :rows=\"\$rows\">\n"
            ."<x-slot:empty>Nothing here yet</x-slot:empty>\n"
            ."</x-bladewind::table>",
            ['rows' => []]
        );

        $this->assertStringContainsString('Nothing here yet', $html);
        $this->assertStringNotContainsString('No records to display', $html);
    }

    #[Test]
    public function the_empty_row_still_sits_inside_a_balanced_tbody(): void
    {
        $html = $this->table('$cols', []);

        $this->assertElementCount($html, '//tbody/tr/td[@colspan]', 1);
        $this->assertSame(1, substr_count($html, '<tbody>'));
        $this->assertSame(1, substr_count($html, '</tbody>'));
    }

    #[Test]
    public function row_numbers_and_action_icons_widen_the_empty_colspan(): void
    {
        $html = $this->table('$cols', [], 'show_row_numbers="true"');

        $this->assertAttribute($html, '//tbody/tr/td[@colspan]', 'colspan', '3');
    }

    #[Test]
    public function the_table_keeps_its_normal_classes(): void
    {
        $html = $this->table('$cols');

        $this->assertHasClasses($html, '//table', ['bw-table', 'divided', 'with-hover-effect']);
    }
}
