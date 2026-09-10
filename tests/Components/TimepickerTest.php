<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TimepickerTest extends TestCase
{
    use RendersComponents;

    #[Test]
    public function the_popup_style_renders_an_input_and_a_modal(): void
    {
        $html = $this->render('<x-bladewind::timepicker name="starts_at" />');

        $this->assertElementCount($html, $this->withClass('bw-timepicker-starts_at'), 1);
        $this->assertElementCount($html, '//*[@data-bw-timepicker="starts_at"]', 1);
        $this->assertStringContainsString('Select Time', $html);
    }

    #[Test]
    public function twelve_hour_format_renders_am_pm_toggles(): void
    {
        $html = $this->render('<x-bladewind::timepicker name="t" format="12" />');

        $this->assertElementCount($html, '//*[@data-bw-time-format="AM"]', 1);
        $this->assertElementCount($html, '//*[@data-bw-time-format="PM"]', 1);
    }

    #[Test]
    public function twenty_four_hour_format_has_no_am_pm_toggles(): void
    {
        $html = $this->render('<x-bladewind::timepicker name="t" format="24" />');

        $this->assertNoElement($html, '//*[@data-bw-time-format]');
    }

    #[Test]
    public function an_initial_value_is_split_into_hour_minute_and_meridiem(): void
    {
        $html = $this->render('<x-bladewind::timepicker name="t" selected_value="09:30 AM" />');

        $this->assertAttribute($html, $this->withClass('bw-t_hh'), 'value', '09');
        $this->assertAttribute($html, $this->withClass('bw-t_mm'), 'value', '30');
        $this->assertHasClasses($html, $this->withClass('bw-t-time-format-am'), ['bg-gray-500']);
    }

    #[Test]
    public function the_dropdown_style_renders_selects_instead_of_a_modal(): void
    {
        $html = $this->render('<x-bladewind::timepicker name="t" style="dropdown" />');

        $this->assertNoElement($html, $this->withClass('bw-timepicker-t'));
        $this->assertElementCount($html, '//select[contains(@name,"t_hh")] | //*[contains(@class,"bw-select-t_hh")]', 1);
    }
}
