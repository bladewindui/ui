<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CurrencyInputTest extends TestCase
{
    use RendersComponents;

    private const INPUT = '//input';
    private const PREFIX = '//div[contains(@class, "prefix")]';
    private const SUFFIX = '//div[contains(@class, "suffix")]';

    #[Test]
    public function it_defaults_to_a_usd_prefix_with_two_decimal_places(): void
    {
        $html = $this->render('<x-bladewind::currency-input name="amount" />');

        $this->assertStringContainsString('$', $html);
        $this->assertAttribute($html, self::INPUT, 'data-mask-money', 'true');
        $this->assertAttribute($html, self::INPUT, 'data-mask-decimal', '.');
        $this->assertAttribute($html, self::INPUT, 'data-mask-thousands', ',');
        $this->assertAttribute($html, self::INPUT, 'data-mask-precision', '2');
    }

    #[Test]
    public function a_known_currency_without_intl_still_gets_a_sensible_symbol(): void
    {
        $html = $this->render('<x-bladewind::currency-input name="amount" currency="GHS" />');

        $this->assertStringContainsString('GH₵', $html);
    }

    #[Test]
    public function a_zero_decimal_currency_defaults_to_no_decimal_places(): void
    {
        $html = $this->render('<x-bladewind::currency-input name="amount" currency="JPY" />');

        $this->assertAttribute($html, self::INPUT, 'data-mask-precision', '0');
    }

    #[Test]
    public function an_unrecognised_currency_falls_back_to_its_own_code_as_the_symbol(): void
    {
        $html = $this->render('<x-bladewind::currency-input name="amount" currency="xyz" />');

        $this->assertStringContainsString('XYZ', $html);
    }

    #[Test]
    public function explicit_overrides_win_over_derived_values(): void
    {
        $html = $this->render('<x-bladewind::currency-input name="amount" currency="USD" symbol="US$" symbol-position="suffix" decimal-separator="," thousands-separator="." precision="3" />');

        $this->assertStringContainsString('US$', $html);
        $this->assertElementCount($html, self::PREFIX, 0);
        $this->assertElementCount($html, self::SUFFIX, 1);
        $this->assertAttribute($html, self::INPUT, 'data-mask-decimal', ',');
        $this->assertAttribute($html, self::INPUT, 'data-mask-thousands', '.');
        $this->assertAttribute($html, self::INPUT, 'data-mask-precision', '3');
    }

    #[Test]
    public function required_is_threaded_through_to_the_underlying_input(): void
    {
        $html = $this->render('<x-bladewind::currency-input name="amount" required="true" />');

        $this->assertHasClasses($html, self::INPUT, ['required']);
    }

    #[Test]
    public function config_supplies_the_default_currency(): void
    {
        config(['bladewind.currency_input.currency' => 'GHS']);

        $html = $this->render('<x-bladewind::currency-input name="amount" />');

        $this->assertStringContainsString('GH₵', $html);
    }
}
