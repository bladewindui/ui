<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TagTest extends TestCase
{
    use RendersComponents;

    private const TAG = '//label';

    #[Test]
    public function it_renders_a_faint_primary_tag_by_default(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" />');

        $this->assertStringContainsString('New', $html);
        $this->assertHasClasses($html, self::TAG, [
            'uppercase',
            'inline-block',
            'rounded-md',
            'mb-3',
            'bg-primary-100/70',
            'text-primary-600',
            'text-[10px]',
        ]);
    }

    #[Test]
    public function the_id_is_prefixed_by_default(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" id="mine" />');

        $this->assertAttribute($html, self::TAG, 'id', 'bw-mine');
    }

    #[Test]
    public function add_id_prefix_false_leaves_the_id_alone(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" id="mine" add_id_prefix="false" />');

        $this->assertAttribute($html, self::TAG, 'id', 'mine');
    }

    #[Test]
    public function rounded_swaps_the_corner_radius(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" rounded="true" />');

        $this->assertHasClasses($html, self::TAG, ['rounded-full']);
        $this->assertMissingClasses($html, self::TAG, ['rounded-md']);
    }

    #[Test]
    public function add_clearing_false_removes_the_bottom_margin(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" add_clearing="false" />');

        $this->assertMissingClasses($html, self::TAG, ['mb-3']);
    }

    #[Test]
    public function uppercasing_false_drops_the_uppercase_class(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" uppercasing="false" />');

        $this->assertMissingClasses($html, self::TAG, ['uppercase']);
    }

    #[Test]
    public function tiny_uses_the_smaller_type_scale(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" tiny="true" />');

        $this->assertHasClasses($html, self::TAG, ['text-[9px]', 'px-[8px]']);
        $this->assertMissingClasses($html, self::TAG, ['text-[10px]']);
    }

    #[Test]
    public function the_dark_shade_uses_heavier_colour_weights(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" shade="dark" />');

        $this->assertHasClasses($html, self::TAG, ['bg-primary-500', 'text-primary-50']);
    }

    #[Test]
    public function outline_swaps_the_fill_for_a_border(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" outline="true" />');

        $this->assertHasClasses($html, self::TAG, ['border', 'border-primary-200', 'text-primary-600']);
        $this->assertMissingClasses($html, self::TAG, ['bg-primary-100/70']);
    }

    #[Test]
    public function color_feeds_every_colour_class(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" color="red" />');

        $this->assertHasClasses($html, self::TAG, ['bg-red-100/70', 'text-red-600']);
    }

    #[Test]
    public function can_close_renders_a_dismiss_control(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" can_close="true" />');

        $this->assertElementCount($html, '//label/a', 1);
        // #608: the default dismiss is a delegated listener, not an inline onclick
        $this->assertAttribute($html, '//label/a', 'data-bw-tag-remove', '');
        $this->assertAttribute($html, '//label/a', 'onclick', null);
        $this->assertElementCount($html, '//label/a/svg', 1);
    }

    #[Test]
    public function a_custom_onclick_replaces_the_default_dismiss_action(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" can_close="true" onclick="doThing()" />');

        $this->assertAttribute($html, '//label/a', 'onclick', 'doThing()');
    }

    #[Test]
    public function a_name_and_value_make_the_tag_selectable_and_suppress_closing(): void
    {
        $html = $this->render('<x-bladewind::tag label="Ghana" name="country" value="gh 233" can_close="true" />');

        // #608: selection is delegated off these attributes rather than inline
        $this->assertAttribute($html, self::TAG, 'data-bw-tag-value', 'gh-233');
        $this->assertAttribute($html, self::TAG, 'data-bw-tag-name', 'country');
        $this->assertAttribute($html, self::TAG, 'onclick', null);
        $this->assertHasClasses($html, self::TAG, ['selectable', 'cursor-pointer', 'bw-country-gh-233']);
        $this->assertNoElement($html, '//label/a');
    }

    #[Test]
    public function consumer_classes_are_appended(): void
    {
        $html = $this->render('<x-bladewind::tag label="New" class="ml-2" />');

        $this->assertHasClasses($html, self::TAG, ['ml-2', 'rounded-md']);
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config([
            'bladewind.tag.color' => 'green',
            'bladewind.tag.rounded' => true,
            'bladewind.tag.uppercasing' => false,
        ]);

        $html = $this->render('<x-bladewind::tag label="New" />');

        $this->assertHasClasses($html, self::TAG, ['bg-green-100/70', 'rounded-full']);
        $this->assertMissingClasses($html, self::TAG, ['uppercase']);
    }
}
