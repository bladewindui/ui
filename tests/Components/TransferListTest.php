<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TransferListTest extends TestCase
{
    use RendersComponents;

    private const AVAILABLE = '//ul[@data-list="available"]/li';
    private const SELECTED = '//ul[@data-list="selected"]/li';
    private const HIDDEN = '//input[@type="hidden"]';
    private const SEARCH = '//input[@data-search]';

    private function items(): string
    {
        return json_encode([
            ['value' => 1, 'label' => 'Editor'],
            ['value' => 2, 'label' => 'Viewer'],
            ['value' => 3, 'label' => 'Admin'],
        ]);
    }

    #[Test]
    public function items_with_no_preselection_all_start_in_the_available_panel(): void
    {
        $html = $this->render('<x-bladewind::transfer-list name="roles" :items="$items" />', ['items' => $this->items()]);

        $this->assertElementCount($html, self::AVAILABLE, 3);
        $this->assertElementCount($html, self::SELECTED, 0);
    }

    #[Test]
    public function preselected_values_start_in_the_selected_panel(): void
    {
        $html = $this->render(
            '<x-bladewind::transfer-list name="roles" :items="$items" :selected="$selected" />',
            ['items' => $this->items(), 'selected' => [2]]
        );

        $this->assertElementCount($html, self::AVAILABLE, 2);
        $this->assertElementCount($html, self::SELECTED, 1);
        $this->assertAttribute($html, self::SELECTED, 'data-value', '2');
    }

    #[Test]
    public function a_hidden_input_exists_per_item_disabled_unless_selected(): void
    {
        $html = $this->render(
            '<x-bladewind::transfer-list name="roles" :items="$items" :selected="$selected" />',
            ['items' => $this->items(), 'selected' => [2]]
        );

        $this->assertElementCount($html, self::HIDDEN, 3);
        $this->assertAttribute($html, '//input[@data-hidden-value="1"]', 'disabled', 'disabled');
        $this->assertAttribute($html, '//input[@data-hidden-value="2"]', 'disabled', null);
        $this->assertAttribute($html, '//input[@data-hidden-value="2"]', 'name', 'roles[]');
    }

    #[Test]
    public function searchable_defaults_to_true(): void
    {
        $html = $this->render('<x-bladewind::transfer-list name="roles" :items="$items" />', ['items' => $this->items()]);

        $this->assertElementCount($html, self::SEARCH, 2);
    }

    #[Test]
    public function searchable_false_removes_both_search_inputs(): void
    {
        $html = $this->render('<x-bladewind::transfer-list name="roles" :items="$items" searchable="false" />', ['items' => $this->items()]);

        $this->assertNoElement($html, self::SEARCH);
    }

    #[Test]
    public function a_custom_value_and_label_key_are_respected(): void
    {
        $items = json_encode([
            ['id' => 10, 'name' => 'Editor'],
            ['id' => 20, 'name' => 'Viewer'],
        ]);

        $html = $this->render(
            '<x-bladewind::transfer-list name="roles" :items="$items" value-key="id" label-key="name" :selected="$selected" />',
            ['items' => $items, 'selected' => [20]]
        );

        $this->assertElementCount($html, self::SELECTED, 1);
        $this->assertAttribute($html, self::SELECTED, 'data-value', '20');
    }

    #[Test]
    public function panel_labels_use_the_provided_overrides(): void
    {
        $html = $this->render(
            '<x-bladewind::transfer-list name="roles" :items="$items" available-label="Not assigned" selected-label="Assigned" />',
            ['items' => $this->items()]
        );

        $this->assertStringContainsString('Not assigned', $html);
        $this->assertStringContainsString('Assigned', $html);
    }

    #[Test]
    public function config_supplies_the_default_height(): void
    {
        config(['bladewind.transfer_list.height' => 400]);

        $html = $this->render('<x-bladewind::transfer-list name="roles" :items="$items" />', ['items' => $this->items()]);

        $this->assertAttributeContains($html, '//ul[@data-list="available"]', 'style', '400px');
    }
}
