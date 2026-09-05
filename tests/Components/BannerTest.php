<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BannerTest extends TestCase
{
    use RendersComponents;

    private const ROOT = '//div[contains(@class, "bw-banner")]';
    private const ICON = '//*[contains(@class, "modal-icon")]';
    private const DISMISS = '//*[@data-bw-banner-dismiss]';

    private function markup(string $attrs = '', string $slot = 'Scheduled maintenance tonight.'): string
    {
        return <<<BLADE
            <x-bladewind::banner {$attrs}>{$slot}</x-bladewind::banner>
        BLADE;
    }

    #[Test]
    public function it_renders_the_slot_content(): void
    {
        $html = $this->render($this->markup());

        $this->assertStringContainsString('Scheduled maintenance tonight.', $html);
        $this->assertElementCount($html, self::ROOT, 1);
    }

    #[Test]
    public function it_renders_a_title_when_given(): void
    {
        $html = $this->render($this->markup('title="Heads up"'));

        $this->assertStringContainsString('Heads up', $html);
    }

    #[Test]
    public function default_tone_is_info_and_uses_status_role(): void
    {
        $html = $this->render($this->markup());

        $this->assertHasClasses($html, self::ROOT, ['bg-blue-50']);
        $this->assertAttribute($html, self::ROOT, 'role', 'status');
    }

    #[Test]
    public function error_tone_uses_alert_role_and_red_colours(): void
    {
        $html = $this->render($this->markup('tone="error"'));

        $this->assertHasClasses($html, self::ROOT, ['bg-red-50']);
        $this->assertAttribute($html, self::ROOT, 'role', 'alert');
    }

    #[Test]
    public function warning_tone_uses_alert_role_and_yellow_colours(): void
    {
        $html = $this->render($this->markup('tone="warning"'));

        $this->assertHasClasses($html, self::ROOT, ['bg-yellow-50']);
        $this->assertAttribute($html, self::ROOT, 'role', 'alert');
    }

    #[Test]
    public function success_tone_uses_green_colours(): void
    {
        $html = $this->render($this->markup('tone="success"'));

        $this->assertHasClasses($html, self::ROOT, ['bg-green-50']);
    }

    #[Test]
    public function an_unknown_tone_falls_back_to_primary(): void
    {
        $html = $this->render($this->markup('tone="primary"'));

        $this->assertHasClasses($html, self::ROOT, ['bg-primary-50']);
        $this->assertElementCount($html, self::ICON, 1);
    }

    #[Test]
    public function it_shows_an_icon_by_default(): void
    {
        $html = $this->render($this->markup());

        $this->assertElementCount($html, self::ICON, 1);
    }

    #[Test]
    public function show_icon_false_hides_the_icon(): void
    {
        $html = $this->render($this->markup('show-icon="false"'));

        $this->assertNoElement($html, self::ICON);
    }

    #[Test]
    public function it_is_dismissible_by_default(): void
    {
        $html = $this->render($this->markup());

        $this->assertElementCount($html, self::DISMISS, 1);
    }

    #[Test]
    public function dismissible_false_hides_the_dismiss_control(): void
    {
        $html = $this->render($this->markup('dismissible="false"'));

        $this->assertNoElement($html, self::DISMISS);
    }

    #[Test]
    public function rounded_false_by_default(): void
    {
        $html = $this->render($this->markup());

        $this->assertMissingClasses($html, self::ROOT, ['rounded-md']);
    }

    #[Test]
    public function rounded_true_adds_rounded_corners(): void
    {
        $html = $this->render($this->markup('rounded="true"'));

        $this->assertHasClasses($html, self::ROOT, ['rounded-md']);
    }

    #[Test]
    public function it_renders_an_actions_slot(): void
    {
        $html = $this->render(
            '<x-bladewind::banner>Update available.<x-slot:actions><a href="#">Refresh</a></x-slot:actions></x-bladewind::banner>'
        );

        $this->assertStringContainsString('Refresh', $html);
    }

    #[Test]
    public function additional_classes_are_applied(): void
    {
        $html = $this->render($this->markup('class="my-banner"'));

        $this->assertHasClasses($html, self::ROOT, ['my-banner']);
    }
}
