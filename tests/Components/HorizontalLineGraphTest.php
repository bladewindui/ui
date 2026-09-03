<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * horizontal-line-graph is a thin, opinionated preset over progress-bar: a
 * labelled, top-left percentage, always shown. These tests pin that contract
 * rather than re-testing progress-bar itself.
 */
class HorizontalLineGraphTest extends TestCase
{
    use RendersComponents;

    #[Test]
    public function it_renders_a_progress_bar_with_the_label_and_percentage(): void
    {
        $html = $this->render('<x-bladewind::horizontal-line-graph label="Storage" percentage="42" />');

        $this->assertHasClasses($html, $this->withClass('bw-progress-bar'), ['bw-progress-bar']);
        $this->assertStringContainsString('Storage', $html);
        $this->assertStringContainsString('42%', $html);
        $this->assertStringContainsString('width: 42%', $html);
    }

    #[Test]
    public function the_percentage_label_is_shown_above_and_to_the_left(): void
    {
        $html = $this->render('<x-bladewind::horizontal-line-graph label="Storage" percentage="10" />');

        $this->assertHasClasses($html, $this->withClass('top_left'), ['top_left']);
    }

    #[Test]
    public function color_and_shade_are_forwarded_to_the_bar(): void
    {
        $html = $this->render(
            '<x-bladewind::horizontal-line-graph label="l" percentage="10" color="red" shade="dark" />'
        );

        $this->assertHasClasses($html, $this->withClass('bar-width'), ['bg-red-500']);
    }

    #[Test]
    public function percentage_label_opacity_is_forwarded(): void
    {
        $html = $this->render(
            '<x-bladewind::horizontal-line-graph label="l" percentage="10" percentage_label_opacity="25" />'
        );

        $this->assertHasClasses($html, $this->withClass('opacity-25'), ['opacity-25']);
    }
}
