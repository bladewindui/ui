<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * #590, props half — the attributes that ~1,400 `!` utilities in the audited app
 * were standing in for. The cascade half landed separately.
 */
class ComponentPropsTest extends TestCase
{
    use RendersComponents;

    // ---- icon size ------------------------------------------------------

    #[Test]
    #[DataProvider('iconSizeProvider')]
    public function icon_accepts_a_named_size(string $size, string $expected): void
    {
        $html = $this->render('<x-bladewind::icon name="user" size="'.$size.'" />');

        $this->assertHasClasses($html, '//svg', [$expected]);
        $this->assertMissingClasses($html, '//svg', ['size-6']);
    }

    public static function iconSizeProvider(): array
    {
        return [
            'tiny' => ['tiny', 'size-3'],
            'small' => ['small', 'size-4'],
            'regular' => ['regular', 'size-5'],
            'big' => ['big', 'size-8'],
            'large' => ['large', 'size-10'],
        ];
    }

    #[Test]
    public function icon_size_medium_matches_the_historic_default(): void
    {
        $explicit = $this->render('<x-bladewind::icon name="user" size="medium" />');
        $default = $this->render('<x-bladewind::icon name="user" />');

        $this->assertHasClasses($explicit, '//svg', ['size-6']);
        $this->assertHasClasses($default, '//svg', ['size-6']);
    }

    #[Test]
    public function icon_size_passes_an_arbitrary_utility_through(): void
    {
        $html = $this->render('<x-bladewind::icon name="user" size="size-[18px]" />');

        $this->assertHasClasses($html, '//svg', ['size-[18px]']);
        $this->assertMissingClasses($html, '//svg', ['size-6']);
    }

    /**
     * The class regex was the only way to set a size before the prop existed, so
     * it has to keep winning or a lot of existing markup changes size.
     */
    #[Test]
    public function a_size_class_still_beats_the_size_prop(): void
    {
        $html = $this->render('<x-bladewind::icon name="user" size="large" class="size-4" />');

        $this->assertHasClasses($html, '//svg', ['size-4']);
        $this->assertMissingClasses($html, '//svg', ['size-10']);
    }

    #[Test]
    public function icon_without_a_size_is_unchanged(): void
    {
        $html = $this->render('<x-bladewind::icon name="user" />');

        $this->assertHasClasses($html, '//svg', ['size-6', 'inline-block']);
    }

    // ---- card padding ---------------------------------------------------

    #[Test]
    #[DataProvider('cardPaddingProvider')]
    public function card_accepts_a_named_padding(string $padding, ?string $expected): void
    {
        $html = $this->render('<x-bladewind::card padding="'.$padding.'">c</x-bladewind::card>');

        if ($expected === null) {
            $this->assertMissingClasses($html, $this->withClass('bw-card'), ['p-2', 'p-4', 'p-6', 'p-8', 'p-10', 'p-12']);

            return;
        }

        $this->assertHasClasses($html, $this->withClass('bw-card'), [$expected]);
    }

    public static function cardPaddingProvider(): array
    {
        return [
            'none' => ['none', null],
            'tiny' => ['tiny', 'p-2'],
            'small' => ['small', 'p-4'],
            'regular' => ['regular', 'p-6'],
            'medium' => ['medium', 'p-8'],
            'big' => ['big', 'p-10'],
            'large' => ['large', 'p-12'],
        ];
    }

    #[Test]
    public function card_padding_passes_an_arbitrary_utility_through(): void
    {
        $html = $this->render('<x-bladewind::card padding="p-5">c</x-bladewind::card>');

        $this->assertHasClasses($html, $this->withClass('bw-card'), ['p-5']);
        $this->assertMissingClasses($html, $this->withClass('bw-card'), ['p-6']);
    }

    #[Test]
    public function card_padding_wins_over_the_compact_boolean(): void
    {
        $html = $this->render('<x-bladewind::card compact="true" padding="large">c</x-bladewind::card>');

        $this->assertHasClasses($html, $this->withClass('bw-card'), ['p-12']);
        $this->assertMissingClasses($html, $this->withClass('bw-card'), ['p-4']);
    }

    #[Test]
    public function the_compact_and_no_padding_booleans_still_behave(): void
    {
        $compact = $this->render('<x-bladewind::card compact="true">c</x-bladewind::card>');
        $none = $this->render('<x-bladewind::card no_padding="true">c</x-bladewind::card>');
        $plain = $this->render('<x-bladewind::card>c</x-bladewind::card>');

        $this->assertHasClasses($compact, $this->withClass('bw-card'), ['p-4']);
        $this->assertMissingClasses($none, $this->withClass('bw-card'), ['p-4', 'p-6']);
        $this->assertHasClasses($plain, $this->withClass('bw-card'), ['p-6']);
    }

    // ---- radius scale ---------------------------------------------------

    #[Test]
    #[DataProvider('radiusProvider')]
    public function the_radius_scale_covers_the_values_consumers_reached_for(string $radius, string $expected): void
    {
        $html = $this->render('<x-bladewind::card radius="'.$radius.'">c</x-bladewind::card>');

        $this->assertHasClasses($html, $this->withClass('bw-card'), [$expected]);
    }

    public static function radiusProvider(): array
    {
        return [
            'tiny is new' => ['tiny', 'rounded-sm'],
            'omg is new' => ['omg', 'rounded-4xl'],
            'full is new' => ['full', 'rounded-full'],
            'a raw utility passes through' => ['rounded-l-none', 'rounded-l-none'],
            'small is unchanged' => ['small', 'rounded-lg'],
            'large is unchanged' => ['large', 'rounded-2xl'],
        ];
    }

    // ---- input group ----------------------------------------------------

    #[Test]
    public function an_input_group_wraps_its_children_attached_by_default(): void
    {
        $html = $this->render(
            "<x-bladewind::input-group>\n<x-bladewind::input name=\"q\" />\n<x-bladewind::button>Go</x-bladewind::button>\n</x-bladewind::input-group>"
        );

        $this->assertHasClasses($html, $this->withClass('bw-input-group'), ['bw-input-group', 'attached']);
        $this->assertElementCount($html, $this->withClass('bw-input-group').'//input', 1);
        $this->assertElementCount($html, $this->withClass('bw-input-group').'//button', 1);
    }

    #[Test]
    public function an_input_group_can_be_unattached(): void
    {
        $html = $this->render(
            "<x-bladewind::input-group attached=\"false\">\n<x-bladewind::input name=\"q\" />\n</x-bladewind::input-group>"
        );

        $this->assertHasClasses($html, $this->withClass('bw-input-group'), ['gapped']);
        $this->assertMissingClasses($html, $this->withClass('bw-input-group'), ['attached']);
    }

    #[Test]
    public function an_input_group_merges_consumer_classes(): void
    {
        $html = $this->render(
            "<x-bladewind::input-group class=\"mt-8\">\n<x-bladewind::input name=\"q\" />\n</x-bladewind::input-group>"
        );

        $this->assertHasClasses($html, $this->withClass('bw-input-group'), ['mt-8', 'bw-input-group']);
    }

    #[Test]
    public function config_supplies_the_input_group_default(): void
    {
        config(['bladewind.input_group.attached' => false]);

        $html = $this->render(
            "<x-bladewind::input-group>\n<x-bladewind::input name=\"q\" />\n</x-bladewind::input-group>"
        );

        $this->assertHasClasses($html, $this->withClass('bw-input-group'), ['gapped']);
    }
}
