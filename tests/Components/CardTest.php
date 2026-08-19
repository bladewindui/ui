<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CardTest extends TestCase
{
    use RendersComponents;

    private function card(): string
    {
        return $this->withClass('bw-card');
    }

    #[Test]
    public function it_renders_the_documented_default_markup(): void
    {
        $html = $this->render('<x-bladewind::card>content</x-bladewind::card>');

        $this->assertStringContainsString('content', $html);
        $this->assertHasClasses($html, $this->card(), [
            'bw-card',
            'bg-white',
            'rounded-lg',
            'border',
            'border-neutral-200',
            'shadowed',
            'p-6',
        ]);
        $this->assertMissingClasses($html, $this->card(), [
            'bw-contact-card',
            'shadowed-hover',
            'p-4',
        ]);
    }

    #[Test]
    public function has_shadow_false_drops_the_shadow_class(): void
    {
        $html = $this->render('<x-bladewind::card has_shadow="false">c</x-bladewind::card>');

        $this->assertMissingClasses($html, $this->card(), ['shadowed']);
    }

    #[Test]
    public function has_border_false_drops_the_border_classes(): void
    {
        $html = $this->render('<x-bladewind::card has_border="false">c</x-bladewind::card>');

        $this->assertMissingClasses($html, $this->card(), ['border', 'border-neutral-200']);
    }

    #[Test]
    public function has_hover_adds_the_hover_shadow(): void
    {
        $html = $this->render('<x-bladewind::card has_hover="true">c</x-bladewind::card>');

        $this->assertHasClasses($html, $this->card(), ['shadowed-hover', 'cursor-pointer']);
    }

    #[Test]
    public function compact_swaps_the_padding_scale(): void
    {
        $html = $this->render('<x-bladewind::card compact="true">c</x-bladewind::card>');

        $this->assertHasClasses($html, $this->card(), ['p-4']);
        $this->assertMissingClasses($html, $this->card(), ['p-6']);
    }

    #[Test]
    public function no_padding_removes_all_padding(): void
    {
        $html = $this->render('<x-bladewind::card no_padding="true">c</x-bladewind::card>');

        $this->assertMissingClasses($html, $this->card(), ['p-6', 'p-4']);
    }

    /**
     * improvements.md item 2 wants a freer radius scale on card. These are the
     * values that currently work, pinned so a wider scale stays additive.
     */
    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('radiusProvider')]
    public function radius_maps_to_a_rounded_utility(string $radius, string $expected): void
    {
        $html = $this->render('<x-bladewind::card radius="'.$radius.'">c</x-bladewind::card>');

        $this->assertHasClasses($html, $this->card(), [$expected]);
    }

    public static function radiusProvider(): array
    {
        return [
            'none' => ['none', 'rounded-none'],
            'small' => ['small', 'rounded-lg'],
            'medium' => ['medium', 'rounded-xl'],
            'large' => ['large', 'rounded-2xl'],
            'xl' => ['xl', 'rounded-3xl'],
        ];
    }

    /**
     * getRadiusString() indexes its map directly, so an unrecognised radius is a
     * fatal, not a fallback — the `?? ''` on the return is dead code because the
     * array access is evaluated first. Pinned here so item 2's wider radius scale
     * has to decide deliberately what an unknown value does.
     */
    #[Test]
    public function an_unrecognised_radius_currently_throws(): void
    {
        $this->expectException(\Illuminate\View\ViewException::class);

        $this->render('<x-bladewind::card radius="full">c</x-bladewind::card>');
    }

    #[Test]
    public function a_header_slot_replaces_the_padded_body_and_renders_a_divider(): void
    {
        $html = $this->render(
            "<x-bladewind::card>\n<x-slot:header>head</x-slot:header>\nbody\n</x-bladewind::card>"
        );

        $this->assertStringContainsString('head', $html);
        $this->assertMissingClasses($html, $this->card(), ['p-6']);
        $this->assertElementCount($html, $this->withClass('border-b'), 1);
    }

    #[Test]
    public function a_footer_slot_renders_a_top_divider(): void
    {
        $html = $this->render(
            "<x-bladewind::card>\n<x-slot:footer>foot</x-slot:footer>\nbody\n</x-bladewind::card>"
        );

        $this->assertStringContainsString('foot', $html);
        $this->assertElementCount($html, $this->withClass('border-t'), 1);
    }

    #[Test]
    public function a_title_renders_above_the_body_when_there_is_no_header(): void
    {
        $html = $this->render('<x-bladewind::card title="Revenue">body</x-bladewind::card>');

        $this->assertStringContainsString('Revenue', $html);
        $this->assertElementCount($html, $this->withClass('uppercase'), 1);
    }

    #[Test]
    public function a_relative_url_becomes_a_location_assignment(): void
    {
        $html = $this->render('<x-bladewind::card url="/reports">c</x-bladewind::card>');

        $this->assertAttribute($html, $this->card(), 'onclick', "location.href='/reports'");
        $this->assertHasClasses($html, $this->card(), ['cursor-pointer']);
    }

    #[Test]
    public function an_absolute_url_opens_a_new_window(): void
    {
        $html = $this->render('<x-bladewind::card url="https://example.com">c</x-bladewind::card>');

        $this->assertAttribute($html, $this->card(), 'onclick', "window.open('https://example.com')");
    }

    #[Test]
    public function a_function_call_url_is_treated_as_javascript(): void
    {
        $html = $this->render('<x-bladewind::card url="doThing(1)">c</x-bladewind::card>');

        $this->assertAttribute($html, $this->card(), 'onclick', 'javascript:doThing(1)');
    }

    #[Test]
    public function consumer_classes_are_merged_rather_than_replaced(): void
    {
        $html = $this->render('<x-bladewind::card class="mt-8">c</x-bladewind::card>');

        $this->assertHasClasses($html, $this->card(), ['mt-8', 'bw-card', 'shadowed']);
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config([
            'bladewind.card.has_border' => false,
            'bladewind.card.has_shadow' => false,
            'bladewind.card.radius' => 'large',
        ]);

        $html = $this->render('<x-bladewind::card>c</x-bladewind::card>');

        $this->assertHasClasses($html, $this->card(), ['rounded-2xl']);
        $this->assertMissingClasses($html, $this->card(), ['border', 'shadowed']);
    }
}
