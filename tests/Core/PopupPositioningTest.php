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

    /**
     * The tooltip is the odd one out: it was drawn with a ::after on the trigger,
     * which cannot leave its own element at all. It is now a real element appended
     * to the body, and the stylesheet switches the pseudo-element off so a page
     * never shows two bubbles for one tooltip.
     */
    #[Test]
    public function the_tooltip_draws_a_real_element_rather_than_a_pseudo_element(): void
    {
        $source = $this->script('tooltip.js');

        $this->assertStringContainsString("createElement('div')", $source);
        $this->assertStringContainsString('document.body.appendChild', $source);
        $this->assertStringContainsString("classList.add('bw-tooltip-js')", $source);
        $this->assertStringContainsString('getBoundingClientRect()', $source);
        $this->assertMatchesRegularExpression("/addEventListener\('scroll',[^)]*,\s*true\)/", $source);
    }

    #[Test]
    public function the_stylesheet_switches_the_old_tooltip_off_when_the_script_runs(): void
    {
        $css = file_get_contents(__DIR__.'/../../packages/core/public/css/bladewind-ui.min.css');

        $this->assertStringContainsString('.bw-tooltip-js [data-tooltip]:before', $css);
        $this->assertStringContainsString('.bw-tooltip-bubble', $css);
    }

    /**
     * The table's action icons carry data-tooltip directly, so they need the
     * script on pages that never render a tooltip component.
     */
    #[Test]
    public function the_table_action_icons_ship_the_tooltip_script(): void
    {
        $html = $this->render(
            '<x-bladewind::table name="t" :columns="[\'ref\']" :rows="$rows" :action_icons="$icons" />',
            [
                'rows' => [['ref' => 'ORD-1', 'id' => 1]],
                'icons' => [['icon' => 'pencil', 'tip' => 'Edit', 'click' => 'edit']],
            ]
        );

        $this->assertStringContainsString('data-tooltip="Edit"', $html);
        $this->assertStringContainsString('js/tooltip.js', $html);
    }

    #[Test]
    public function the_tooltip_component_keeps_its_attribute_contract(): void
    {
        $html = $this->render(
            '<x-bladewind::tooltip text="Archive" position="bottom" size="regular" color="dark">x</x-bladewind::tooltip>'
        );

        $this->assertAttribute($html, $this->withClass('bw-tooltip'), 'data-tooltip', 'Archive');
        $this->assertAttribute($html, $this->withClass('bw-tooltip'), 'data-position', 'bottom center');
        $this->assertAttribute($html, $this->withClass('bw-tooltip'), 'data-size', 'regular');
        $this->assertAttribute($html, $this->withClass('bw-tooltip'), 'data-inverted', '');
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
