<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PopoverTest extends TestCase
{
    use RendersComponents;

    #[Test]
    public function it_renders_a_trigger_and_a_hidden_content_panel(): void
    {
        $html = $this->render('<x-bladewind::popover>content here</x-bladewind::popover>');

        $this->assertElementCount($html, $this->withClass('bw-trigger'), 1);
        $this->assertHasClasses($html, $this->withClass('bw-popover-content'), ['hidden', 'opacity-0']);
        $this->assertStringContainsString('content here', $html);
    }

    #[Test]
    public function the_trigger_exposes_its_state_to_assistive_tech(): void
    {
        $html = $this->render('<x-bladewind::popover>content</x-bladewind::popover>');

        $this->assertAttribute($html, $this->withClass('bw-trigger'), 'aria-haspopup', 'true');
        $this->assertAttribute($html, $this->withClass('bw-trigger'), 'aria-expanded', 'false');
        $this->assertAttribute($html, $this->withClass('bw-popover-content'), 'role', 'dialog');
    }

    #[Test]
    public function the_trigger_aria_controls_points_at_the_content_panel_id(): void
    {
        $html = $this->render('<x-bladewind::popover name="my-popover">content</x-bladewind::popover>');

        $node = $this->firstNode($html, $this->withClass('bw-trigger'));
        $controls = $node->getAttribute('aria-controls');

        $this->assertNotEmpty($controls);
        $this->assertElementCount($html, '//*[@id="'.$controls.'"]', 1);
    }

    #[Test]
    public function an_optional_title_is_rendered_above_the_content(): void
    {
        $html = $this->render('<x-bladewind::popover title="Heads up">content</x-bladewind::popover>');

        $this->assertStringContainsString('Heads up', $html);
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('positionProvider')]
    public function position_controls_where_the_content_sits(string $position, string $expected): void
    {
        $html = $this->render('<x-bladewind::popover position="'.$position.'">content</x-bladewind::popover>');

        $this->assertHasClasses($html, $this->withClass('bw-popover-content'), [$expected]);
    }

    public static function positionProvider(): array
    {
        return [
            'bottom' => ['bottom', 'top-full'],
            'top' => ['top', 'bottom-full'],
            'left' => ['left', 'right-full'],
            'right' => ['right', 'left-full'],
        ];
    }

    #[Test]
    public function an_unknown_position_falls_back_to_bottom(): void
    {
        $html = $this->render('<x-bladewind::popover position="diagonally">content</x-bladewind::popover>');

        $this->assertHasClasses($html, $this->withClass('bw-popover-content'), ['top-full']);
    }
}
