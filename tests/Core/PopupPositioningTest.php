<?php

namespace Mkocansey\Bladewind\Tests\Core;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * #591 — popups positioned in normal flow are clipped by any ancestor that
 * establishes a scroll container, and the common one is the overflow-x-auto
 * wrapper every wide table needs (overflow-x: auto computes overflow-y to auto,
 * so it clips vertically too).
 *
 * These assert the mechanism is wired, not the geometry. Proving a rect is not
 * clipped needs layout, which is Tier C of #588 and the Playwright dependency
 * this repo does not have.
 */
class PopupPositioningTest extends TestCase
{
    use RendersComponents;

    private function script(string $name): string
    {
        return file_get_contents(__DIR__.'/../../packages/core/public/js/'.$name);
    }

    #[Test]
    #[DataProvider('popupProvider')]
    public function the_popup_escapes_its_scroll_container_with_fixed_positioning(string $script): void
    {
        $source = $this->script($script);

        $this->assertStringContainsString("classList.add('fixed')", $source);
        $this->assertStringContainsString('getBoundingClientRect()', $source);
    }

    #[Test]
    #[DataProvider('popupProvider')]
    public function the_popup_repositions_when_any_ancestor_scrolls(string $script): void
    {
        $source = $this->script($script);

        // capture phase, or scrolling an inner container leaves the popup behind
        $this->assertMatchesRegularExpression(
            "/addEventListener\('scroll',[^)]*,\s*true\)/",
            $source,
            "{$script} must listen for scroll in the capture phase"
        );
        $this->assertStringContainsString("addEventListener('resize'", $source);
    }

    /**
     * Position has to be reset on close, or a popup opened once keeps stale
     * coordinates the next time its container moves.
     */
    #[Test]
    #[DataProvider('popupProvider')]
    public function the_popup_returns_to_normal_flow_when_it_closes(string $script): void
    {
        $this->assertStringContainsString("classList.add('absolute')", $this->script($script));
    }

    public static function popupProvider(): array
    {
        return [
            'dropmenu' => ['dropmenu.js'],
            'select' => ['select.js'],
            'popover' => ['popover.js'],
        ];
    }

    #[Test]
    public function popover_is_told_which_side_it_was_asked_to_open_on(): void
    {
        $html = $this->render(
            '<x-bladewind::popover name="p" trigger="Open" position="right">Body</x-bladewind::popover>'
        );

        $this->assertStringContainsString("position: 'right'", $html);
    }

    /**
     * The popups keep their place in the DOM — they are repositioned, not
     * reparented. That is deliberate: a true portal would break consumer CSS that
     * selects a popup through an ancestor, which the issue flagged as the risk.
     */
    #[Test]
    public function the_select_list_stays_inside_its_component(): void
    {
        $html = $this->render(
            '<x-bladewind::select name="c" :data="$d" />',
            ['d' => [['label' => 'Ghana', 'value' => 'GH']]]
        );

        $this->assertElementCount($html, $this->withClass('bw-select').'//'.ltrim($this->withClass('bw-select-items-container'), '/'), 1);
    }
}
