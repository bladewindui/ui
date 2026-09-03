<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TooltipTest extends TestCase
{
    use RendersComponents;

    #[Test]
    public function it_renders_the_wrapped_content_with_the_tooltip_data_attributes(): void
    {
        $html = $this->render('<x-bladewind::tooltip text="More info">hover me</x-bladewind::tooltip>');

        $this->assertStringContainsString('hover me', $html);
        $this->assertAttribute($html, $this->withClass('bw-tooltip'), 'data-tooltip', 'More info');
    }

    #[Test]
    public function without_text_no_tooltip_attributes_are_rendered(): void
    {
        $html = $this->render('<x-bladewind::tooltip>plain</x-bladewind::tooltip>');

        $this->assertAttribute($html, $this->withClass('bw-tooltip'), 'data-tooltip', null);
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('positionProvider')]
    public function position_maps_to_the_legacy_data_position_string(string $position, string $expected): void
    {
        $html = $this->render('<x-bladewind::tooltip text="t" position="'.$position.'">x</x-bladewind::tooltip>');

        $this->assertAttribute($html, $this->withClass('bw-tooltip'), 'data-position', $expected);
    }

    public static function positionProvider(): array
    {
        return [
            'top' => ['top', 'top center'],
            'bottom' => ['bottom', 'bottom center'],
            'left' => ['left', 'left center'],
            'right' => ['right', 'right center'],
        ];
    }

    #[Test]
    public function an_unknown_position_falls_back_to_top(): void
    {
        $html = $this->render('<x-bladewind::tooltip text="t" position="diagonally">x</x-bladewind::tooltip>');

        $this->assertAttribute($html, $this->withClass('bw-tooltip'), 'data-position', 'top center');
    }

    #[Test]
    public function dark_is_the_default_and_sets_the_inverted_flag(): void
    {
        $html = $this->render('<x-bladewind::tooltip text="t">x</x-bladewind::tooltip>');

        $this->assertAttribute($html, $this->withClass('bw-tooltip'), 'data-inverted', '');
    }

    #[Test]
    public function light_omits_the_inverted_flag(): void
    {
        $html = $this->render('<x-bladewind::tooltip text="t" color="light">x</x-bladewind::tooltip>');

        $this->assertAttribute($html, $this->withClass('bw-tooltip'), 'data-inverted', null);
    }

    #[Test]
    public function size_is_forwarded_as_a_data_attribute(): void
    {
        $html = $this->render('<x-bladewind::tooltip text="t" size="regular">x</x-bladewind::tooltip>');

        $this->assertAttribute($html, $this->withClass('bw-tooltip'), 'data-size', 'regular');
    }
}
