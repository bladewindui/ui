<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ListviewTest extends TestCase
{
    use RendersComponents;

    #[Test]
    public function it_renders_a_list_with_its_items(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::listview>
                <x-bladewind::listview.item>first</x-bladewind::listview.item>
                <x-bladewind::listview.item>second</x-bladewind::listview.item>
            </x-bladewind::listview>
        BLADE);

        $this->assertElementCount($html, '//ul[@role="list"]', 1);
        $this->assertElementCount($html, '//ul/li', 2);
        $this->assertStringContainsString('first', $html);
        $this->assertStringContainsString('second', $html);
    }

    #[Test]
    public function it_has_a_white_background_by_default(): void
    {
        $html = $this->render('<x-bladewind::listview></x-bladewind::listview>');

        $this->assertHasClasses($html, '//ul', ['bg-white']);
    }

    #[Test]
    public function transparent_removes_the_background(): void
    {
        $html = $this->render('<x-bladewind::listview transparent="true"></x-bladewind::listview>');

        $this->assertMissingClasses($html, '//ul', ['bg-white']);
    }

    /**
     * @aware on the item picks up compact from the parent list without the
     * consumer repeating it on every item.
     */
    #[Test]
    public function compact_reduces_item_padding_and_is_inherited_by_items(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::listview compact="true">
                <x-bladewind::listview.item>row</x-bladewind::listview.item>
            </x-bladewind::listview>
        BLADE);

        $this->assertHasClasses($html, '//li', ['py-2', 'px-4']);
        $this->assertMissingClasses($html, '//li', ['p-4']);
    }

    #[Test]
    public function an_item_can_override_its_own_class(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::listview>
                <x-bladewind::listview.item class="bg-red-50">row</x-bladewind::listview.item>
            </x-bladewind::listview>
        BLADE);

        $this->assertHasClasses($html, '//li', ['bg-red-50']);
    }
}
