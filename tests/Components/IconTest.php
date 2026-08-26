<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class IconTest extends TestCase
{
    use RendersComponents;

    private const SVG = '//svg';

    #[Test]
    public function it_inlines_the_named_outline_svg_by_default(): void
    {
        $html = $this->render('<x-bladewind::icon name="user" />');

        $this->assertElementCount($html, self::SVG, 1);
        $this->assertHasClasses($html, self::SVG, ['size-6', 'inline-block']);
    }

    #[Test]
    public function it_reads_solid_icons_from_the_solid_directory(): void
    {
        $outline = $this->render('<x-bladewind::icon name="user" />');
        $solid = $this->render('<x-bladewind::icon name="user" type="solid" />');

        $this->assertElementCount($solid, self::SVG, 1);
        $this->assertNotSame($outline, $solid);
    }

    #[Test]
    public function an_unknown_icon_name_renders_nothing(): void
    {
        $html = $this->render('<x-bladewind::icon name="not-a-real-icon" />');

        $this->assertNoElement($html, self::SVG);
        $this->assertSame('', trim($html));
    }

    #[Test]
    public function an_empty_name_renders_nothing(): void
    {
        $this->assertSame('', trim($this->render('<x-bladewind::icon />')));
    }

    /**
     * The default size is only applied when the consumer has not expressed one.
     * improvements.md item 2 wants this promoted from a class regex to a `size`
     * prop; these tests pin the sniffing behaviour the prop has to preserve.
     */
    #[Test]
    public function an_explicit_size_class_suppresses_the_default_size(): void
    {
        $html = $this->render('<x-bladewind::icon name="user" class="size-4" />');

        $this->assertHasClasses($html, self::SVG, ['size-4']);
        $this->assertMissingClasses($html, self::SVG, ['size-6']);
    }

    #[Test]
    public function a_height_or_width_class_also_suppresses_the_default_size(): void
    {
        foreach (['h-5', 'w-5'] as $class) {
            $html = $this->render('<x-bladewind::icon name="user" class="'.$class.'" />');

            $this->assertHasClasses($html, self::SVG, [$class]);
            $this->assertMissingClasses($html, self::SVG, ['size-6']);
        }
    }

    #[Test]
    public function a_hidden_icon_does_not_get_inline_block(): void
    {
        $html = $this->render('<x-bladewind::icon name="user" class="hidden" />');

        $this->assertHasClasses($html, self::SVG, ['hidden']);
        $this->assertMissingClasses($html, self::SVG, ['inline-block']);
    }

    #[Test]
    public function an_action_wraps_the_icon_in_a_clickable_anchor(): void
    {
        $html = $this->render('<x-bladewind::icon name="user" action="doThing()" />');

        $this->assertAttribute($html, '//a', 'onclick', 'doThing()');
        $this->assertHasClasses($html, '//a', ['cursor-pointer']);
        $this->assertElementCount($html, '//a/svg', 1);
    }

    #[Test]
    public function a_raw_svg_string_is_passed_through_untouched(): void
    {
        $html = $this->render(
            '<x-bladewind::icon name=\'<svg viewBox="0 0 1 1"><path d="M0 0"/></svg>\' />'
        );

        $this->assertElementCount($html, self::SVG, 1);
        // DOMDocument's HTML parser lower-cases attribute names.
        $this->assertAttribute($html, self::SVG, 'viewbox', '0 0 1 1');
    }

    #[Test]
    public function the_icon_directory_is_configurable(): void
    {
        config(['bladewind.icon.type' => 'solid']);

        $default = $this->render('<x-bladewind::icon name="user" />');
        $solid = $this->render('<x-bladewind::icon name="user" type="solid" />');

        $this->assertSame($solid, $default);
    }
}
