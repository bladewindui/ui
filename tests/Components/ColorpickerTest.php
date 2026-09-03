<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ColorpickerTest extends TestCase
{
    use RendersComponents;

    #[Test]
    public function it_renders_a_native_colour_input_by_default(): void
    {
        $html = $this->render('<x-bladewind::colorpicker name="brand" selected_value="#ff0000" />');

        $this->assertElementCount($html, '//input[@type="color"]', 1);
        $this->assertAttribute($html, '//input[@type="hidden"]', 'value', '#ff0000');
        $this->assertAttribute($html, '//input[@type="hidden"]', 'name', 'brand');
    }

    #[Test]
    public function a_colours_list_renders_a_swatch_dropmenu_instead(): void
    {
        $html = $this->render('<x-bladewind::colorpicker name="brand" colors="#ff0000,#00ff00,#0000ff" />');

        $this->assertNoElement($html, '//input[@type="color"]');
        $this->assertElementCount($html, '//*[@data-bw-cp-swatch]', 3);
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('sizeProvider')]
    public function size_controls_the_trigger_dimensions(string $size, string $expected): void
    {
        $html = $this->render('<x-bladewind::colorpicker name="brand" size="'.$size.'" />');

        $this->assertHasClasses($html, $this->withClass('bw-cp-trigger'), [$expected]);
    }

    public static function sizeProvider(): array
    {
        return [
            'small' => ['small', 'size-[24px]'],
            'regular' => ['regular', 'size-[29px]'],
            'medium' => ['medium', 'size-[36px]'],
            'big' => ['big', 'size-[52px]'],
        ];
    }

    #[Test]
    public function show_value_renders_a_label_placeholder(): void
    {
        $html = $this->render('<x-bladewind::colorpicker name="brand" show_value="true" />');

        $this->assertElementCount($html, $this->withClass('bw-cp-label-brand'), 1);
    }

    #[Test]
    public function show_value_is_hidden_by_default(): void
    {
        $html = $this->render('<x-bladewind::colorpicker name="brand" />');

        $this->assertNoElement($html, $this->withClass('bw-cp-label-brand'));
    }
}
