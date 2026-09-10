<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DividerTest extends TestCase
{
    use RendersComponents;

    private const DIVIDER = '//div[contains(@class, "bw-divider")]';

    #[Test]
    public function it_renders_a_decorative_horizontal_rule_by_default(): void
    {
        $html = $this->render('<x-bladewind::divider />');

        $this->assertHasClasses($html, self::DIVIDER, [
            'bw-divider-horizontal',
            'border-t',
            'border-gray-200',
            'my-4',
        ]);
        $this->assertAttribute($html, self::DIVIDER, 'role', 'none');
        $this->assertAttribute($html, self::DIVIDER, 'aria-hidden', 'true');
    }

    #[Test]
    public function decorative_false_makes_it_a_semantic_separator(): void
    {
        $html = $this->render('<x-bladewind::divider decorative="false" />');

        $this->assertAttribute($html, self::DIVIDER, 'role', 'separator');
        $this->assertAttribute($html, self::DIVIDER, 'aria-orientation', 'horizontal');
        $this->assertAttribute($html, self::DIVIDER, 'aria-hidden', null);
    }

    #[Test]
    public function vertical_orientation_swaps_the_axis(): void
    {
        $html = $this->render('<x-bladewind::divider orientation="vertical" decorative="false" />');

        $this->assertHasClasses($html, self::DIVIDER, [
            'bw-divider-vertical',
            'border-l',
            'self-stretch',
            'mx-4',
        ]);
        $this->assertMissingClasses($html, self::DIVIDER, ['border-t']);
        $this->assertAttribute($html, self::DIVIDER, 'aria-orientation', 'vertical');
    }

    #[Test]
    public function a_label_renders_a_centered_line_split(): void
    {
        $html = $this->render('<x-bladewind::divider label="OR" />');

        $this->assertStringContainsString('OR', $html);
        $this->assertHasClasses($html, self::DIVIDER, ['flex', 'items-center']);
        $this->assertElementCount($html, self::DIVIDER.'/span', 3);
    }

    #[Test]
    public function a_label_is_ignored_on_a_vertical_divider(): void
    {
        $html = $this->render('<x-bladewind::divider orientation="vertical" label="OR" />');

        $this->assertStringNotContainsString('OR', $html);
        $this->assertHasClasses($html, self::DIVIDER, ['bw-divider-vertical']);
    }

    #[Test]
    public function spacing_controls_the_margin_scale(): void
    {
        $html = $this->render('<x-bladewind::divider spacing="none" />');

        $this->assertHasClasses($html, self::DIVIDER, ['my-0']);
        $this->assertMissingClasses($html, self::DIVIDER, ['my-4']);
    }

    #[Test]
    public function an_unknown_spacing_falls_back_to_medium(): void
    {
        $html = $this->render('<x-bladewind::divider spacing="huge" />');

        $this->assertHasClasses($html, self::DIVIDER, ['my-4']);
    }

    #[Test]
    public function color_tints_the_line_and_label(): void
    {
        $html = $this->render('<x-bladewind::divider label="OR" color="red" />');

        $this->assertHasClasses($html, self::DIVIDER.'/span[2]', ['text-red-600']);
        $this->assertHasClasses($html, self::DIVIDER.'/span[1]', ['border-red-200']);
    }

    #[Test]
    public function consumer_classes_are_appended(): void
    {
        $html = $this->render('<x-bladewind::divider class="my-custom-class" />');

        $this->assertHasClasses($html, self::DIVIDER, ['my-custom-class', 'border-t']);
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config([
            'bladewind.divider.spacing' => 'large',
            'bladewind.divider.decorative' => false,
        ]);

        $html = $this->render('<x-bladewind::divider />');

        $this->assertHasClasses($html, self::DIVIDER, ['my-8']);
        $this->assertAttribute($html, self::DIVIDER, 'role', 'separator');
    }
}
