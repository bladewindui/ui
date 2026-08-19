<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class StatisticTest extends TestCase
{
    use RendersComponents;

    private function stat(): string
    {
        return $this->withClass('bw-statistic');
    }

    #[Test]
    public function it_renders_the_documented_default_markup(): void
    {
        $html = $this->render('<x-bladewind::statistic label="Revenue" number="1200" />');

        $this->assertHasClasses($html, $this->stat(), ['bw-statistic', 'bg-white', 'p-6']);
        $this->assertStringContainsString('Revenue', $html);
        $this->assertStringContainsString('1200', $html);
    }

    /**
     * Everything item 8 adds is additive. A card written before this change must
     * render exactly what it rendered before.
     */
    #[Test]
    public function the_new_props_are_absent_unless_asked_for(): void
    {
        $html = $this->render('<x-bladewind::statistic label="Revenue" number="1200" />');

        $this->assertNoElement($html, $this->withClass('note'));
        $this->assertNoElement($html, $this->withClass('progress'));
        $this->assertNoElement($html, $this->withClass('hint'));
        $this->assertNoElement($html, $this->withClass('direction'));
    }

    #[Test]
    #[DataProvider('toneProvider')]
    public function a_named_tone_colours_the_note(string $tone, string $expected): void
    {
        $html = $this->render(
            '<x-bladewind::statistic label="l" number="1" note="up on last month" tone="'.$tone.'" />'
        );

        $this->assertHasClasses($html, $this->withClass('note'), [$expected]);
        $this->assertStringContainsString('up on last month', $html);
    }

    public static function toneProvider(): array
    {
        return [
            'neutral' => ['neutral', 'text-gray-500'],
            'positive' => ['positive', 'text-green-600'],
            'negative' => ['negative', 'text-red-600'],
            'warning' => ['warning', 'text-yellow-600'],
            'info' => ['info', 'text-blue-600'],
        ];
    }

    #[Test]
    public function an_unknown_tone_falls_back_to_neutral(): void
    {
        $html = $this->render('<x-bladewind::statistic label="l" number="1" note="n" tone="chartreuse" />');

        $this->assertHasClasses($html, $this->withClass('note'), ['text-gray-500']);
    }

    #[Test]
    #[DataProvider('directionProvider')]
    public function a_direction_renders_a_trend_icon_coloured_by_meaning(
        string $direction,
        bool $invert,
        string $expected
    ): void {
        $html = $this->render(
            '<x-bladewind::statistic label="l" number="1" direction="'.$direction.'"'
            .($invert ? ' invert_direction="true"' : '').' />'
        );

        $this->assertElementCount($html, $this->withClass('direction', 'svg'), 1);
        $this->assertHasClasses($html, $this->withClass('direction', 'svg'), [$expected]);
    }

    public static function directionProvider(): array
    {
        return [
            'up is good' => ['up', false, 'text-green-600'],
            'down is bad' => ['down', false, 'text-red-600'],
            'flat is neutral' => ['flat', false, 'text-gray-500'],
            'inverted, up is bad' => ['up', true, 'text-red-600'],
            'inverted, down is good' => ['down', true, 'text-green-600'],
            'inverted flat is still neutral' => ['flat', true, 'text-gray-500'],
        ];
    }

    #[Test]
    public function an_unknown_direction_renders_no_icon(): void
    {
        $html = $this->render('<x-bladewind::statistic label="l" number="1" direction="sideways" />');

        $this->assertNoElement($html, $this->withClass('direction', 'svg'));
    }

    #[Test]
    public function the_direction_colours_the_note_when_no_tone_is_given(): void
    {
        $html = $this->render('<x-bladewind::statistic label="l" number="1" direction="down" note="worse" />');

        $this->assertHasClasses($html, $this->withClass('note'), ['text-red-600']);
    }

    #[Test]
    public function an_explicit_tone_beats_the_direction(): void
    {
        $html = $this->render(
            '<x-bladewind::statistic label="l" number="1" direction="down" note="fine" tone="info" />'
        );

        $this->assertHasClasses($html, $this->withClass('note'), ['text-blue-600']);
        $this->assertHasClasses($html, $this->withClass('direction', 'svg'), ['text-blue-600']);
    }

    #[Test]
    public function a_hint_renders_next_to_the_label(): void
    {
        $html = $this->render('<x-bladewind::statistic label="Arrears" number="1" hint="Unpaid after 30 days" />');

        $this->assertElementCount($html, $this->withClass('hint'), 1);
        $this->assertAttribute($html, $this->withClass('hint'), 'title', 'Unpaid after 30 days');
    }

    #[Test]
    public function progress_renders_a_bar_and_replaces_the_note(): void
    {
        $html = $this->render(
            '<x-bladewind::statistic label="l" number="1" note="ignored" progress="42" progress_label="of target" />'
        );

        $this->assertElementCount($html, $this->withClass('progress'), 1);
        $this->assertNoElement($html, $this->withClass('note'));
        $this->assertStringContainsString('of target', $html);
        $this->assertStringContainsString('42%', $html);
        $this->assertStringContainsString('width: 42%', $html);
    }

    #[Test]
    public function the_progress_bar_takes_the_tone_colour(): void
    {
        $html = $this->render('<x-bladewind::statistic label="l" number="1" progress="10" tone="negative" />');

        $this->assertStringContainsString('bg-red-500', $html);
    }

    #[Test]
    public function progress_is_clamped_to_a_sane_range(): void
    {
        $over = $this->render('<x-bladewind::statistic label="l" number="1" progress="180" />');
        $under = $this->render('<x-bladewind::statistic label="l" number="1" progress="-20" />');

        $this->assertStringContainsString('width: 100%', $over);
        $this->assertStringContainsString('width: 0%', $under);
    }

    #[Test]
    public function a_non_numeric_progress_renders_no_bar(): void
    {
        $html = $this->render('<x-bladewind::statistic label="l" number="1" progress="soon" note="n" />');

        $this->assertNoElement($html, $this->withClass('progress'));
        $this->assertElementCount($html, $this->withClass('note'), 1);
    }

    /**
     * Icons on the left is the settled default and the reason this component was
     * centralised in the audited app.
     */
    #[Test]
    public function icons_stay_on_the_left_by_default(): void
    {
        $html = $this->render('<x-bladewind::statistic label="l" number="1" icon="<svg></svg>" />');

        $this->assertElementCount($html, $this->withClass('icon').'/following-sibling::'.'*[contains(@class,"number")]', 1);
    }

    #[Test]
    public function config_supplies_the_tone_defaults(): void
    {
        config(['bladewind.statistic.invert_direction' => true]);

        $html = $this->render('<x-bladewind::statistic label="l" number="1" direction="down" note="n" />');

        $this->assertHasClasses($html, $this->withClass('note'), ['text-green-600']);
    }
}
