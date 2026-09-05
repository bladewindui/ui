<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MeterTest extends TestCase
{
    use RendersComponents;

    private const BAR = '//div[contains(@class, "rounded-full")]/div';
    private const METER = '//meter';

    #[Test]
    public function with_no_zones_it_renders_a_single_neutral_coloured_bar(): void
    {
        $html = $this->render('<x-bladewind::meter value="50" max="100" />');

        $this->assertHasClasses($html, self::BAR, ['bg-primary-500']);
        $this->assertAttributeContains($html, self::BAR, 'style', 'width: 50%');
    }

    #[Test]
    public function a_value_in_the_good_high_zone_is_green_by_default(): void
    {
        $html = $this->render('<x-bladewind::meter value="90" max="100" low="30" high="70" />');

        $this->assertHasClasses($html, self::BAR, ['bg-green-500']);
    }

    #[Test]
    public function a_value_in_the_bad_low_zone_is_red_by_default(): void
    {
        $html = $this->render('<x-bladewind::meter value="10" max="100" low="30" high="70" />');

        $this->assertHasClasses($html, self::BAR, ['bg-red-500']);
    }

    #[Test]
    public function a_value_in_the_medium_zone_is_yellow(): void
    {
        $html = $this->render('<x-bladewind::meter value="50" max="100" low="30" high="70" />');

        $this->assertHasClasses($html, self::BAR, ['bg-yellow-500']);
    }

    #[Test]
    public function an_optimum_in_the_low_zone_flips_which_end_is_good(): void
    {
        // e.g. an error rate: low values are good, high values are bad
        $html = $this->render('<x-bladewind::meter value="10" max="100" low="30" high="70" optimum="0" />');

        $this->assertHasClasses($html, self::BAR, ['bg-green-500']);
    }

    #[Test]
    public function an_optimum_in_the_low_zone_makes_the_high_zone_bad(): void
    {
        $html = $this->render('<x-bladewind::meter value="90" max="100" low="30" high="70" optimum="0" />');

        $this->assertHasClasses($html, self::BAR, ['bg-red-500']);
    }

    #[Test]
    public function the_percentage_reflects_min_and_max(): void
    {
        $html = $this->render('<x-bladewind::meter value="15" min="10" max="20" />');

        $this->assertAttributeContains($html, self::BAR, 'style', 'width: 50%');
    }

    #[Test]
    public function the_native_meter_element_carries_the_real_semantics(): void
    {
        $html = $this->render('<x-bladewind::meter value="15" min="10" max="20" low="12" high="18" optimum="19" />');

        $this->assertAttribute($html, self::METER, 'value', '15');
        $this->assertAttribute($html, self::METER, 'min', '10');
        $this->assertAttribute($html, self::METER, 'max', '20');
        $this->assertAttribute($html, self::METER, 'low', '12');
        $this->assertAttribute($html, self::METER, 'high', '18');
        $this->assertAttribute($html, self::METER, 'optimum', '19');
    }

    #[Test]
    public function show_value_false_hides_the_value_readout(): void
    {
        $html = $this->render('<x-bladewind::meter value="15" max="20" show-value="false" />');

        $this->assertStringNotContainsString('15 / 20', $html);
    }

    #[Test]
    public function a_label_is_shown_even_when_show_value_is_false(): void
    {
        $html = $this->render('<x-bladewind::meter value="15" max="20" label="Disk usage" show-value="false" />');

        $this->assertStringContainsString('Disk usage', $html);
    }

    #[Test]
    public function config_supplies_the_default_size(): void
    {
        config(['bladewind.meter.size' => 'large']);

        $html = $this->render('<x-bladewind::meter value="15" max="20" />');

        $this->assertHasClasses($html, '//div[contains(@class, "bg-gray-200")]', ['h-3']);
    }
}
