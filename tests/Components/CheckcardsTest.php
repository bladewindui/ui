<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CheckcardsTest extends TestCase
{
    use RendersComponents;

    private function checkcards(string $groupAttributes = '', string $liteAttributes = ''): string
    {
        return $this->render(
            "<x-bladewind::checkcards name=\"plan\" {$groupAttributes}>\n"
            ."<x-bladewind::checkcards.card value=\"pro\" title=\"Pro\">Pro plan</x-bladewind::checkcards.card>\n"
            ."<x-bladewind::checkcards.card value=\"lite\" title=\"Lite\" {$liteAttributes}>Lite plan</x-bladewind::checkcards.card>\n"
            ."</x-bladewind::checkcards>"
        );
    }

    #[Test]
    public function cards_are_enabled_by_default(): void
    {
        $html = $this->checkcards();

        $this->assertElementCount($html, '//*[@aria-disabled]', 0);
    }

    #[Test]
    public function disabling_the_group_disables_every_card(): void
    {
        $html = $this->checkcards('disabled="true"');

        $this->assertAttribute($html, '//*[@data-value="pro"]', 'aria-disabled', 'true');
        $this->assertAttribute($html, '//*[@data-value="lite"]', 'aria-disabled', 'true');
        // the hidden input stays enabled, so a locked-in selection is still submitted
        $this->assertAttribute($html, '//input[@name="plan"]', 'disabled', null);
    }

    #[Test]
    public function a_single_card_can_be_disabled(): void
    {
        $html = $this->checkcards('', 'disabled="true"');

        $this->assertAttribute($html, '//*[@data-value="pro"]', 'aria-disabled', null);
        $this->assertAttribute($html, '//*[@data-value="lite"]', 'aria-disabled', 'true');
        $this->assertAttribute($html, '//*[@data-value="lite"]', 'disabled', null);
    }

    #[Test]
    public function a_card_can_opt_out_of_a_disabled_group(): void
    {
        $html = $this->checkcards('disabled="true"', 'disabled="false"');

        $this->assertAttribute($html, '//*[@data-value="pro"]', 'aria-disabled', 'true');
        $this->assertAttribute($html, '//*[@data-value="lite"]', 'aria-disabled', null);
    }

    #[Test]
    public function disabled_cards_are_styled_from_aria_disabled(): void
    {
        $html = $this->checkcards();

        $this->assertHasClasses($html, '//*[@data-value="pro"]', [
            'aria-disabled:cursor-not-allowed',
            'aria-disabled:opacity-50',
        ]);
    }

    #[Test]
    public function the_onclick_function_name_is_handed_to_the_script(): void
    {
        $this->assertAttribute($this->checkcards('onclick="nextQuestion"'), '//input[@name="plan"]', 'data-onclick', 'nextQuestion');
        $this->assertAttribute($this->checkcards(), '//input[@name="plan"]', 'data-onclick', null);
    }
}
