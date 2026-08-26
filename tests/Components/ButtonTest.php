<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ButtonTest extends TestCase
{
    use RendersComponents;

    private const BUTTON = '//button';

    #[Test]
    public function it_renders_a_primary_button_by_default(): void
    {
        $html = $this->render('<x-bladewind::button>Save</x-bladewind::button>');

        $this->assertStringContainsString('Save', $html);
        $this->assertHasClasses($html, self::BUTTON, [
            'bw-button',
            'uppercase',
            'regular',
            'primary',
            'rounded-md',
            'cursor-pointer',
            'bg-primary-500!',
        ]);
        $this->assertMissingClasses($html, self::BUTTON, ['outlined', 'disabled', 'has-icon']);
    }

    /**
     * #600: the type attribute was built as a string and echoed through {{ }}, so
     * the quotes were escaped and the markup read type=&quot;button&quot;. A parser
     * saw the value '"button"' — quote characters included — which is not a valid
     * button type keyword, so the missing-value default applied and every BladeWind
     * button inside a form behaved as type="submit". It now goes through the
     * attribute bag like everything else.
     */
    #[Test]
    public function it_renders_a_real_type_button_attribute(): void
    {
        $html = $this->render('<x-bladewind::button>Go</x-bladewind::button>');

        $this->assertStringNotContainsString('&quot;', $html);
        $this->assertAttribute($html, self::BUTTON, 'type', 'button');
    }

    #[Test]
    public function can_submit_makes_it_a_real_submit_button(): void
    {
        $html = $this->render('<x-bladewind::button can_submit="true">Go</x-bladewind::button>');

        $this->assertAttribute($html, self::BUTTON, 'type', 'submit');
    }

    #[Test]
    public function an_anchor_button_gets_no_type_attribute(): void
    {
        $html = $this->render('<x-bladewind::button tag="a">Go</x-bladewind::button>');

        $this->assertAttribute($html, '//a', 'type', null);
    }

    #[Test]
    public function disabled_sets_both_the_attribute_and_the_class(): void
    {
        $html = $this->render('<x-bladewind::button disabled="true">Go</x-bladewind::button>');

        $this->assertHasClasses($html, self::BUTTON, ['disabled']);
        $this->assertMissingClasses($html, self::BUTTON, ['cursor-pointer']);
        $this->assertAttribute($html, self::BUTTON, 'disabled', 'disabled');
    }

    #[Test]
    public function tag_a_renders_an_anchor_with_no_type_attribute(): void
    {
        $html = $this->render('<x-bladewind::button tag="a">Go</x-bladewind::button>');

        $this->assertElementCount($html, '//a', 1);
        $this->assertNoElement($html, self::BUTTON);
        $this->assertAttribute($html, '//a', 'type', null);
    }

    #[Test]
    public function an_unsupported_tag_falls_back_to_button(): void
    {
        $html = $this->render('<x-bladewind::button tag="span">Go</x-bladewind::button>');

        $this->assertElementCount($html, self::BUTTON, 1);
    }

    #[Test]
    public function color_overrides_type_for_colour_classes(): void
    {
        $html = $this->render('<x-bladewind::button color="red">Go</x-bladewind::button>');

        $this->assertHasClasses($html, self::BUTTON, ['bg-red-500!', 'hover:bg-red-600!']);
    }

    #[Test]
    public function black_strips_the_numeric_colour_weights(): void
    {
        $html = $this->render('<x-bladewind::button color="black">Go</x-bladewind::button>');

        $this->assertHasClasses($html, self::BUTTON, ['bg-black!']);
        $this->assertMissingClasses($html, self::BUTTON, ['bg-black-500!']);
    }

    #[Test]
    public function outline_swaps_the_fill_for_a_border(): void
    {
        $html = $this->render('<x-bladewind::button outline="true">Go</x-bladewind::button>');

        $this->assertHasClasses($html, self::BUTTON, ['outlined', 'border-2', 'border-primary-500/50']);
        $this->assertMissingClasses($html, self::BUTTON, ['bg-primary-500!']);
    }

    #[Test]
    public function border_width_feeds_the_outline_border_class(): void
    {
        $html = $this->render('<x-bladewind::button outline="true" border_width="4">Go</x-bladewind::button>');

        $this->assertHasClasses($html, self::BUTTON, ['border-4']);
    }

    #[Test]
    public function show_focus_ring_false_removes_the_ring(): void
    {
        $html = $this->render('<x-bladewind::button show_focus_ring="false">Go</x-bladewind::button>');

        $this->assertHasClasses($html, self::BUTTON, ['focus:ring-0', 'focus:outline-0']);
        $this->assertMissingClasses($html, self::BUTTON, ['focus:ring']);
    }

    #[Test]
    public function ring_width_is_only_honoured_for_supported_widths(): void
    {
        $this->assertHasClasses(
            $this->render('<x-bladewind::button ring_width="4">Go</x-bladewind::button>'),
            self::BUTTON,
            ['focus:ring-4']
        );

        $this->assertHasClasses(
            $this->render('<x-bladewind::button ring_width="3">Go</x-bladewind::button>'),
            self::BUTTON,
            ['focus:ring']
        );
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('radiusProvider')]
    public function radius_maps_to_a_rounded_utility(string $radius, string $expected): void
    {
        $html = $this->render('<x-bladewind::button radius="'.$radius.'">Go</x-bladewind::button>');

        $this->assertHasClasses($html, self::BUTTON, [$expected]);
    }

    public static function radiusProvider(): array
    {
        return [
            'none' => ['none', 'rounded-none'],
            'small' => ['small', 'rounded-md'],
            'medium' => ['medium', 'rounded-xl'],
            'full' => ['full', 'rounded-full'],
            // unlike card, button has its own map with a fallback
            'unknown' => ['nonsense', 'rounded-full'],
        ];
    }

    #[Test]
    public function uppercasing_false_drops_the_uppercase_class(): void
    {
        $html = $this->render('<x-bladewind::button uppercasing="false">Go</x-bladewind::button>');

        $this->assertMissingClasses($html, self::BUTTON, ['uppercase']);
    }

    #[Test]
    public function an_icon_renders_before_the_label_by_default(): void
    {
        $html = $this->render('<x-bladewind::button icon="user">Go</x-bladewind::button>');

        $this->assertHasClasses($html, self::BUTTON, ['has-icon']);
        $this->assertElementCount($html, '//button/svg', 1);
        $this->assertElementCount($html, '//button/svg/following-sibling::span', 1);
    }

    #[Test]
    public function icon_right_moves_the_icon_after_the_label(): void
    {
        $html = $this->render('<x-bladewind::button icon="user" icon_right="true">Go</x-bladewind::button>');

        $this->assertElementCount($html, '//button/span/following-sibling::svg', 1);
    }

    #[Test]
    public function the_label_is_wrapped_in_a_growing_span(): void
    {
        $html = $this->render('<x-bladewind::button>Go</x-bladewind::button>');

        $this->assertHasClasses($html, '//button/span', ['grow', 'text-white/90']);
    }

    #[Test]
    public function button_text_css_overrides_the_label_colour(): void
    {
        $html = $this->render('<x-bladewind::button button_text_css="text-black">Go</x-bladewind::button>');

        $this->assertHasClasses($html, '//button/span', ['text-black']);
        $this->assertMissingClasses($html, '//button/span', ['text-white/90']);
    }

    #[Test]
    public function a_name_becomes_a_class_hook(): void
    {
        $html = $this->render('<x-bladewind::button name="save-btn">Go</x-bladewind::button>');

        $this->assertHasClasses($html, self::BUTTON, ['save-btn']);
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config([
            'bladewind.button.size' => 'big',
            'bladewind.button.radius' => 'full',
            'bladewind.button.uppercasing' => false,
        ]);

        $html = $this->render('<x-bladewind::button>Go</x-bladewind::button>');

        $this->assertHasClasses($html, self::BUTTON, ['big', 'rounded-full']);
        $this->assertMissingClasses($html, self::BUTTON, ['uppercase', 'regular']);
    }
}
