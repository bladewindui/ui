<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SelectTest extends TestCase
{
    use RendersComponents;

    // the root carries the hooks; role="combobox" moved to the trigger in #597,
    // which is the element that actually owns the expanded state
    private const ROOT = '//div[contains(concat(" ", normalize-space(@class), " "), " bw-select ")]';

    private const DATA = '[{"label":"Ghana","value":"GH"},{"label":"Kenya","value":"KE"}]';

    private function select(string $attributes = '', string $data = self::DATA): string
    {
        return $this->render(
            '<x-bladewind::select name="country" :data="$data" '.$attributes.' />',
            ['data' => json_decode($data, true)]
        );
    }

    #[Test]
    public function it_renders_a_combobox_shell_with_the_expected_hooks(): void
    {
        $html = $this->select();

        $this->assertElementCount($html, self::ROOT, 1);
        $this->assertHasClasses($html, self::ROOT, ['bw-select', 'bw-select-country', 'relative', 'mb-3']);
        $this->assertAttribute($html, self::ROOT, 'data-type', 'dynamic');
        $this->assertAttribute($html, self::ROOT, 'data-required', 'false');
    }

    #[Test]
    public function the_trigger_is_focusable_and_marked_enabled(): void
    {
        $html = $this->select();

        $this->assertAttribute($html, $this->withClass('clickable'), 'tabindex', '0');
        $this->assertHasClasses($html, $this->withClass('clickable'), ['enabled']);
    }

    #[Test]
    public function disabled_and_readonly_replace_the_enabled_state(): void
    {
        $this->assertHasClasses($this->select('disabled="true"'), $this->withClass('clickable'), ['disabled']);
        $this->assertHasClasses($this->select('readonly="true"'), $this->withClass('clickable'), ['readonly']);
        $this->assertMissingClasses($this->select('disabled="true"'), $this->withClass('clickable'), ['enabled']);
    }

    #[Test]
    public function each_data_row_becomes_a_select_item(): void
    {
        $html = $this->select();

        $this->assertStringContainsString('Ghana', $html);
        $this->assertStringContainsString('Kenya', $html);
        // two real items plus the always-rendered, hidden empty-state item
        $this->assertElementCount($html, $this->withClass('bw-select-item'), 3);
        $this->assertElementCount($html, $this->withClass('empty-state', 'div'), 1);
        $this->assertElementCount($html, '//div[@data-value="GH"]', 1);
        $this->assertElementCount($html, '//div[@data-value="KE"]', 1);
    }

    #[Test]
    public function value_and_label_keys_are_configurable(): void
    {
        $html = $this->render(
            '<x-bladewind::select name="country" :data="$data" value_key="id" label_key="name" />',
            ['data' => [['id' => 7, 'name' => 'Ghana']]]
        );

        $this->assertStringContainsString('Ghana', $html);
        $this->assertStringContainsString('7', $html);
    }

    #[Test]
    public function a_placeholder_is_rendered_when_there_is_no_label(): void
    {
        $html = $this->select('placeholder="Pick a country"');

        $this->assertStringContainsString('Pick a country', $html);
    }

    #[Test]
    public function a_label_replaces_the_placeholder_with_a_floating_label(): void
    {
        $html = $this->select('label="Country"');

        $this->assertElementCount($html, $this->withClass('form-label', 'span'), 1);
        $this->assertStringContainsString('Country', $html);
    }

    #[Test]
    public function required_is_reflected_on_the_root_and_the_hidden_input(): void
    {
        $html = $this->select('required="true"');

        $this->assertAttribute($html, self::ROOT, 'data-required', 'true');
        $this->assertHasClasses($html, '//input[@type="hidden"]', ['required']);
    }

    #[Test]
    public function the_hidden_input_carries_the_scoped_name(): void
    {
        $html = $this->select();

        $this->assertElementCount($html, '//input[@type="hidden"]', 1);
        $this->assertHasClasses($html, '//input[@type="hidden"]', ['bw-country']);
    }

    #[Test]
    public function add_clearing_false_removes_the_bottom_margin(): void
    {
        $this->assertMissingClasses($this->select('add_clearing="false"'), self::ROOT, ['mb-3']);
    }

    #[Test]
    public function searchable_adds_the_marker_class_and_shows_the_search_bar(): void
    {
        $html = $this->select('searchable="true"');

        $this->assertHasClasses($html, self::ROOT, ['searchable']);
        $this->assertMissingClasses($html, $this->withClass('search-bar'), ['hidden']);
    }

    /**
     * The search bar is pointless with nothing to search, so the component drops
     * the request rather than rendering a dead control.
     */
    #[Test]
    public function searchable_is_ignored_when_there_is_no_data(): void
    {
        $html = $this->render(
            '<x-bladewind::select name="country" :data="$data" searchable="true" />',
            ['data' => []]
        );

        $this->assertMissingClasses($html, self::ROOT, ['searchable']);
        $this->assertHasClasses($html, $this->withClass('search-bar'), ['hidden']);
    }

    #[Test]
    public function multiple_is_exposed_as_a_data_attribute(): void
    {
        $this->assertAttribute($this->select('multiple="true"'), self::ROOT, 'data-multiple', 'true');
    }

    #[Test]
    public function manual_data_switches_the_component_to_manual_mode(): void
    {
        $html = $this->render('<x-bladewind::select name="country" data="manual" />');

        $this->assertAttribute($html, self::ROOT, 'data-type', 'manual');
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('sizeProvider')]
    public function size_maps_to_a_vertical_padding(string $size, string $expected): void
    {
        $this->assertHasClasses($this->select('size="'.$size.'"'), $this->withClass('clickable'), [$expected]);
    }

    public static function sizeProvider(): array
    {
        return [
            'small' => ['small', 'py-[6px]'],
            'medium' => ['medium', 'py-[10px]'],
            'regular' => ['regular', 'py-[6.5px]'],
            'big' => ['big', 'py-[18.5px]'],
            'unknown falls back to medium' => ['nonsense', 'py-[10px]'],
        ];
    }

    #[Test]
    public function a_filter_is_normalised_and_exposed_as_a_data_attribute(): void
    {
        $this->assertAttribute($this->select('filter="my filter"'), self::ROOT, 'data-filter', 'my_filter');
    }

    #[Test]
    public function names_are_normalised(): void
    {
        $html = $this->render('<x-bladewind::select name="my select" data="manual" />');

        $this->assertHasClasses($html, self::ROOT, ['bw-select-my_select']);
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config(['bladewind.select.size' => 'big', 'bladewind.select.placeholder' => 'Choose one']);

        $html = $this->select();

        $this->assertHasClasses($html, $this->withClass('clickable'), ['py-[18.5px]']);
        $this->assertStringContainsString('Choose one', $html);
    }
}
