<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DescriptionListTest extends TestCase
{
    use RendersComponents;

    private const DL = '//dl';
    private const ITEM = '//div[contains(@class, "bw-description-list-item")]';
    private const DT = '//dt';
    private const DD = '//dd';

    private function markup(string $itemAttrs = ''): string
    {
        return <<<BLADE
            <x-bladewind::description-list>
                <x-bladewind::description-list.item label="Full name" {$itemAttrs}>Jane Doe</x-bladewind::description-list.item>
            </x-bladewind::description-list>
        BLADE;
    }

    #[Test]
    public function it_renders_a_semantic_definition_list(): void
    {
        $html = $this->render($this->markup());

        $this->assertElementCount($html, self::DL, 1);
        $this->assertElementCount($html, self::ITEM, 1);
    }

    #[Test]
    public function the_label_and_value_render_in_dt_and_dd(): void
    {
        $html = $this->render($this->markup());

        $this->assertStringContainsString('Full name', $html);
        $this->assertStringContainsString('Jane Doe', $html);
        $this->assertElementCount($html, self::DT, 1);
        $this->assertElementCount($html, self::DD, 1);
    }

    #[Test]
    public function divided_defaults_to_true(): void
    {
        $html = $this->render($this->markup());

        $this->assertHasClasses($html, self::DL, ['divide-y']);
    }

    #[Test]
    public function divided_false_removes_the_dividers(): void
    {
        $html = $this->render('<x-bladewind::description-list divided="false"><x-bladewind::description-list.item label="A">B</x-bladewind::description-list.item></x-bladewind::description-list>');

        $this->assertMissingClasses($html, self::DL, ['divide-y']);
    }

    #[Test]
    public function striped_on_the_root_is_inherited_by_items_via_aware(): void
    {
        $html = $this->render('<x-bladewind::description-list striped="true"><x-bladewind::description-list.item label="A">B</x-bladewind::description-list.item></x-bladewind::description-list>');

        $this->assertHasClasses($html, self::ITEM, ['bg-gray-50/60']);
    }

    #[Test]
    public function an_action_slot_renders_beside_the_value(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::description-list>
                <x-bladewind::description-list.item label="Email">
                    jane@example.com
                    <x-slot:action>
                        <a href="#">Edit</a>
                    </x-slot:action>
                </x-bladewind::description-list.item>
            </x-bladewind::description-list>
        BLADE);

        $this->assertStringContainsString('jane@example.com', $html);
        $this->assertStringContainsString('Edit', $html);
        $this->assertElementCount($html, self::DD.'//a', 1);
    }

    #[Test]
    public function no_action_renders_no_extra_markup(): void
    {
        $html = $this->render($this->markup());

        $this->assertNoElement($html, self::DD.'//a');
    }

    #[Test]
    public function consumer_classes_are_appended_to_the_root_and_an_item(): void
    {
        $html = $this->render('<x-bladewind::description-list class="mt-4"><x-bladewind::description-list.item label="A" class="border-red-500">B</x-bladewind::description-list.item></x-bladewind::description-list>');

        $this->assertHasClasses($html, self::DL, ['mt-4']);
        $this->assertHasClasses($html, self::ITEM, ['border-red-500']);
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config([
            'bladewind.description_list.divided' => false,
            'bladewind.description_list.striped' => true,
        ]);

        $html = $this->render('<x-bladewind::description-list><x-bladewind::description-list.item label="A">B</x-bladewind::description-list.item></x-bladewind::description-list>');

        $this->assertMissingClasses($html, self::DL, ['divide-y']);
        $this->assertHasClasses($html, self::ITEM, ['bg-gray-50/60']);
    }
}
