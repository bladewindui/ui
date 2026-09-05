<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ContextMenuTest extends TestCase
{
    use RendersComponents;

    private const ROOT = '//div[contains(concat(" ", normalize-space(@class), " "), " bw-context-menu ")]';
    private const ITEM = '//div[@data-item="true"]';

    #[Test]
    public function it_renders_the_region_and_the_menu_items(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::context-menu name="mine">
                <x-slot:region>
                    <div id="the-region">Right-click me</div>
                </x-slot:region>
                <x-bladewind::context-menu.item>Edit</x-bladewind::context-menu.item>
            </x-bladewind::context-menu>
        BLADE);

        $this->assertStringContainsString('Right-click me', $html);
        $this->assertStringContainsString('Edit', $html);
        $this->assertElementCount($html, self::ITEM, 1);
    }

    #[Test]
    public function disable_native_defaults_to_true(): void
    {
        $html = $this->render('<x-bladewind::context-menu name="mine"><x-bladewind::context-menu.item>Edit</x-bladewind::context-menu.item></x-bladewind::context-menu>');

        $this->assertAttribute($html, self::ROOT, 'data-disable-native', '1');
    }

    #[Test]
    public function disable_native_false_is_threaded_through(): void
    {
        $html = $this->render('<x-bladewind::context-menu name="mine" disable-native="false"><x-bladewind::context-menu.item>Edit</x-bladewind::context-menu.item></x-bladewind::context-menu>');

        $this->assertAttribute($html, self::ROOT, 'data-disable-native', '0');
    }

    #[Test]
    public function a_divider_renders_as_a_separator_with_no_data_item(): void
    {
        $html = $this->render('<x-bladewind::context-menu.item divider="true" />');

        $this->assertAttribute($html, '//div', 'role', 'separator');
        $this->assertAttribute($html, '//div', 'data-item', null);
    }

    #[Test]
    public function a_disabled_item_is_marked_and_inert(): void
    {
        $html = $this->render('<x-bladewind::context-menu.item disabled="true">Edit</x-bladewind::context-menu.item>');

        $this->assertAttribute($html, self::ITEM, 'aria-disabled', 'true');
        $this->assertAttribute($html, self::ITEM, 'data-disabled', '1');
        $this->assertHasClasses($html, self::ITEM, ['pointer-events-none', 'opacity-40']);
    }

    #[Test]
    public function danger_tone_tints_the_item_red(): void
    {
        $html = $this->render('<x-bladewind::context-menu.item tone="danger">Delete</x-bladewind::context-menu.item>');

        $this->assertHasClasses($html, self::ITEM, ['text-red-600!']);
    }

    #[Test]
    public function a_submenu_slot_renders_a_nested_hidden_menu_and_a_chevron(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::context-menu.item>
                New
                <x-slot:submenu>
                    <x-bladewind::context-menu.item>File</x-bladewind::context-menu.item>
                    <x-bladewind::context-menu.item>Folder</x-bladewind::context-menu.item>
                </x-slot:submenu>
            </x-bladewind::context-menu.item>
        BLADE);

        $this->assertAttribute($html, self::ITEM, 'aria-haspopup', 'menu');
        $this->assertElementCount($html, '//div[contains(@class, "bw-context-menu-submenu")]', 1);
        $this->assertElementCount($html, '//div[contains(@class, "bw-context-menu-submenu")]'.self::ITEM, 2);
        $this->assertHasClasses($html, '//div[contains(@class, "bw-context-menu-submenu")]', ['hidden']);
    }

    #[Test]
    public function a_leaf_item_has_no_submenu_markup(): void
    {
        $html = $this->render('<x-bladewind::context-menu.item>Edit</x-bladewind::context-menu.item>');

        $this->assertAttribute($html, self::ITEM, 'aria-haspopup', null);
        $this->assertNoElement($html, '//div[contains(@class, "bw-context-menu-submenu")]');
    }

    #[Test]
    public function consumer_attributes_pass_through_to_the_item(): void
    {
        $html = $this->render('<x-bladewind::context-menu.item onclick="doThing()">Edit</x-bladewind::context-menu.item>');

        $this->assertAttribute($html, self::ITEM, 'onclick', 'doThing()');
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config(['bladewind.context_menu.padded' => false]);

        $html = $this->render('<x-bladewind::context-menu name="mine"><x-bladewind::context-menu.item>Edit</x-bladewind::context-menu.item></x-bladewind::context-menu>');

        $this->assertHasClasses($html, '//div[contains(@class, "bw-items-list")]', ['p-0']);
        $this->assertMissingClasses($html, '//div[contains(@class, "bw-items-list")]', ['p-2']);
    }
}
