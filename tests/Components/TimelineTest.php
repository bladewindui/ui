<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TimelineTest extends TestCase
{
    use RendersComponents;

    #[Test]
    public function it_renders_the_date_and_content_for_each_entry(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::timelines>
                <x-bladewind::timeline date="Jan 1" content="Order placed" />
                <x-bladewind::timeline date="Jan 2" content="Order shipped" last="true" />
            </x-bladewind::timelines>
        BLADE);

        $this->assertStringContainsString('Jan 1', $html);
        $this->assertStringContainsString('Order placed', $html);
        $this->assertStringContainsString('Jan 2', $html);
        $this->assertStringContainsString('Order shipped', $html);
    }

    /**
     * A completed entry's anchor is filled ($color-500); a pending one is only
     * outlined. status drives $completed unless the prop overrides it.
     */
    #[Test]
    public function a_completed_status_fills_the_anchor(): void
    {
        $pending = $this->render('<x-bladewind::timeline date="d" content="c" status="pending" />');
        $done = $this->render('<x-bladewind::timeline date="d" content="c" status="done" />');

        $this->assertHasClasses($pending, $this->withClass('rounded-full', 'div'), ['border-gray-500/50']);
        $this->assertHasClasses($done, $this->withClass('rounded-full', 'div'), ['bg-gray-500']);
    }

    /**
     * The colour comes from the timelines wrapper's `color` prop through
     * @aware, not from the individual timeline entry.
     */
    #[Test]
    public function the_group_color_is_inherited_by_each_entry(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::timelines color="blue">
                <x-bladewind::timeline date="d" content="c" status="done" />
            </x-bladewind::timelines>
        BLADE);

        $this->assertHasClasses($html, $this->withClass('rounded-full', 'div'), ['bg-blue-500']);
    }

    #[Test]
    public function the_last_entry_has_no_connecting_line(): void
    {
        $withLine = $this->render('<x-bladewind::timeline date="d" content="c" />');
        $last = $this->render('<x-bladewind::timeline date="d" content="c" last="true" />');

        $this->assertElementCount($withLine, $this->withClass('w-[2px]'), 1);
        $this->assertNoElement($last, $this->withClass('w-[2px]'));
    }

    #[Test]
    public function a_big_anchor_defaults_to_a_check_icon_when_completed(): void
    {
        $html = $this->render('<x-bladewind::timeline date="d" content="c" status="done" anchor="big" />');

        $this->assertElementCount($html, '//svg', 1);
    }
}
