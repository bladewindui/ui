<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CarouselTest extends TestCase
{
    use RendersComponents;

    private const ROOT = '//div[contains(concat(" ", normalize-space(@class), " "), " bw-carousel ")]';
    private const TRACK = '//div[@data-track]';
    private const SLIDE = '//div[contains(@class, "bw-carousel-slide")]';
    private const PREV = '//button[@data-prev]';
    private const NEXT = '//button[@data-next]';
    private const INDICATORS = '//div[@data-indicators]';

    private function markup(string $attrs = ''): string
    {
        return <<<BLADE
            <x-bladewind::carousel {$attrs}>
                <x-bladewind::carousel.slide>Slide one</x-bladewind::carousel.slide>
                <x-bladewind::carousel.slide>Slide two</x-bladewind::carousel.slide>
                <x-bladewind::carousel.slide>Slide three</x-bladewind::carousel.slide>
            </x-bladewind::carousel>
        BLADE;
    }

    #[Test]
    public function it_renders_a_track_with_all_slides(): void
    {
        $html = $this->render($this->markup());

        $this->assertElementCount($html, self::ROOT, 1);
        $this->assertElementCount($html, self::TRACK, 1);
        $this->assertElementCount($html, self::SLIDE, 3);
        $this->assertStringContainsString('Slide one', $html);
        $this->assertStringContainsString('Slide three', $html);
    }

    #[Test]
    public function arrows_show_by_default(): void
    {
        $html = $this->render($this->markup());

        $this->assertElementCount($html, self::PREV, 1);
        $this->assertElementCount($html, self::NEXT, 1);
    }

    #[Test]
    public function arrows_false_hides_the_arrow_buttons(): void
    {
        $html = $this->render($this->markup('arrows="false"'));

        $this->assertNoElement($html, self::PREV);
        $this->assertNoElement($html, self::NEXT);
    }

    #[Test]
    public function indicators_show_by_default(): void
    {
        $html = $this->render($this->markup());

        $this->assertElementCount($html, self::INDICATORS, 1);
    }

    #[Test]
    public function indicators_false_hides_the_indicator_container(): void
    {
        $html = $this->render($this->markup('indicators="false"'));

        $this->assertNoElement($html, self::INDICATORS);
    }

    #[Test]
    public function height_sets_an_inline_style(): void
    {
        $html = $this->render($this->markup('height="320px"'));

        $this->assertAttribute($html, self::ROOT, 'style', 'height: 320px');
    }

    #[Test]
    public function it_has_an_accessible_carousel_role(): void
    {
        $html = $this->render($this->markup());

        $this->assertAttribute($html, self::ROOT, 'role', 'region');
        $this->assertAttribute($html, self::ROOT, 'aria-roledescription', 'carousel');
    }

    #[Test]
    public function additional_classes_are_applied(): void
    {
        $html = $this->render($this->markup('class="my-carousel"'));

        $this->assertHasClasses($html, self::ROOT, ['my-carousel']);
    }
}
