<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ThemeSwitcherTest extends TestCase
{
    use RendersComponents;

    #[Test]
    public function it_renders_a_dropmenu_with_the_three_theme_options(): void
    {
        $html = $this->render('<x-bladewind::theme-switcher />');

        $this->assertStringContainsString('Light', $html);
        $this->assertStringContainsString('Dark', $html);
        $this->assertStringContainsString('System', $html);
        $this->assertElementCount($html, '//*[@data-bw-theme="light"]', 1);
        $this->assertElementCount($html, '//*[@data-bw-theme="dark"]', 1);
        $this->assertElementCount($html, '//*[@data-bw-theme="system"]', 1);
    }

    /**
     * All three trigger icons render hidden server-side; chooseTheme() picks
     * the right one to reveal at runtime based on stored/system preference.
     */
    #[Test]
    public function all_trigger_icons_start_hidden_until_js_picks_one(): void
    {
        $html = $this->render('<x-bladewind::theme-switcher />');

        $this->assertHasClasses($html, $this->withClass('theme-light'), ['hidden']);
        $this->assertHasClasses($html, $this->withClass('theme-dark'), ['hidden']);
        $this->assertHasClasses($html, $this->withClass('theme-system'), ['hidden']);
    }

    #[Test]
    public function custom_labels_are_rendered_instead_of_the_defaults(): void
    {
        $html = $this->render(
            '<x-bladewind::theme-switcher light_text="Day" dark_text="Night" system_text="Auto" />'
        );

        $this->assertStringContainsString('Day', $html);
        $this->assertStringContainsString('Night', $html);
        $this->assertStringContainsString('Auto', $html);
    }

    /**
     * A strict CSP is the whole reason theme selection is a delegated
     * bwOn() listener rather than three inline onclicks (#608) — pin that the
     * behaviour actually lives in the shared script, not back on the markup.
     */
    #[Test]
    public function theme_selection_is_delegated_not_inline(): void
    {
        $html = $this->render('<x-bladewind::theme-switcher />');

        $this->assertStringContainsString("bwOn('click', '[data-bw-theme]'", $html);
        $this->assertDoesNotMatchRegularExpression('/data-bw-theme="[a-z]+"[^>]*onclick=/', $html);
    }
}
