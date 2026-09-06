<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PasswordMeterTest extends TestCase
{
    use RendersComponents;

    private const ROOT = '//div[contains(concat(" ", normalize-space(@class), " "), " bw-password-meter ")]';
    private const BAR = '//span[@data-bar]';
    private const LABEL = '//div[@data-label]';

    #[Test]
    public function it_renders_four_bars_and_a_label_by_default(): void
    {
        $html = $this->render('<x-bladewind::password-meter for="password" />');

        $this->assertElementCount($html, self::BAR, 4);
        $this->assertElementCount($html, self::LABEL, 1);
        $this->assertAttribute($html, self::ROOT, 'data-for', 'password');
    }

    #[Test]
    public function show_label_false_omits_the_label(): void
    {
        $html = $this->render('<x-bladewind::password-meter for="password" show-label="false" />');

        $this->assertNoElement($html, self::LABEL);
    }

    #[Test]
    public function the_translated_strength_labels_are_exposed_as_data_attributes(): void
    {
        $html = $this->render('<x-bladewind::password-meter for="password" />');

        $this->assertAttribute($html, self::ROOT, 'data-label-weak', 'Weak');
        $this->assertAttribute($html, self::ROOT, 'data-label-fair', 'Fair');
        $this->assertAttribute($html, self::ROOT, 'data-label-good', 'Good');
        $this->assertAttribute($html, self::ROOT, 'data-label-strong', 'Strong');
    }

    #[Test]
    public function length_thresholds_are_exposed_as_data_attributes(): void
    {
        $html = $this->render('<x-bladewind::password-meter for="password" min-length="6" strong-length="10" />');

        $this->assertAttribute($html, self::ROOT, 'data-min-length', '6');
        $this->assertAttribute($html, self::ROOT, 'data-strong-length', '10');
    }

    #[Test]
    public function an_invalid_length_threshold_falls_back_to_the_default(): void
    {
        $html = $this->render('<x-bladewind::password-meter for="password" min-length="not-a-number" />');

        $this->assertAttribute($html, self::ROOT, 'data-min-length', '8');
    }

    #[Test]
    public function consumer_classes_are_appended(): void
    {
        $html = $this->render('<x-bladewind::password-meter for="password" class="mt-4" />');

        $this->assertHasClasses($html, self::ROOT, ['mt-4']);
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config([
            'bladewind.password_meter.show_label' => false,
            'bladewind.password_meter.min_length' => 10,
        ]);

        $html = $this->render('<x-bladewind::password-meter for="password" />');

        $this->assertNoElement($html, self::LABEL);
        $this->assertAttribute($html, self::ROOT, 'data-min-length', '10');
    }
}
