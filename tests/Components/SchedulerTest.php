<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SchedulerTest extends TestCase
{
    use RendersComponents;

    private const ROOT = '//div[contains(concat(" ", normalize-space(@class), " "), " bw-scheduler ")]';
    private const COLUMN = '//div[@data-column]';
    private const EVENT = '//*[@data-event]';

    #[Test]
    public function day_view_defaults_to_a_single_column_when_no_resources_are_given(): void
    {
        $html = $this->render('<x-bladewind::scheduler date="2026-03-10"></x-bladewind::scheduler>');

        $this->assertElementCount($html, self::ROOT, 1);
        $this->assertElementCount($html, self::COLUMN, 1);
    }

    #[Test]
    public function day_view_renders_one_column_per_resource(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::scheduler date="2026-03-10" :resources="[
                ['id' => 'r1', 'label' => 'Room A'],
                ['id' => 'r2', 'label' => 'Room B'],
            ]"></x-bladewind::scheduler>
        BLADE);

        $this->assertElementCount($html, self::COLUMN, 2);
        $this->assertStringContainsString('Room A', $html);
        $this->assertStringContainsString('Room B', $html);
    }

    #[Test]
    public function week_view_renders_seven_day_columns(): void
    {
        $html = $this->render('<x-bladewind::scheduler view="week" date="2026-03-10"></x-bladewind::scheduler>');

        $this->assertElementCount($html, self::COLUMN, 7);
    }

    #[Test]
    public function an_event_is_placed_in_its_resource_column(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::scheduler date="2026-03-10" :resources="[
                ['id' => 'r1', 'label' => 'Room A'],
                ['id' => 'r2', 'label' => 'Room B'],
            ]" :events="[
                ['id' => 'e1', 'resource_id' => 'r1', 'label' => 'Consultation', 'start' => '2026-03-10 09:00', 'end' => '2026-03-10 10:00'],
            ]"></x-bladewind::scheduler>
        BLADE);

        $this->assertElementCount($html, self::EVENT, 1);
        $this->assertStringContainsString('Consultation', $html);
    }

    #[Test]
    public function overlapping_events_in_a_column_are_packed_side_by_side(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::scheduler date="2026-03-10" :events="[
                ['id' => 'e1', 'label' => 'First', 'start' => '2026-03-10 09:00', 'end' => '2026-03-10 10:00'],
                ['id' => 'e2', 'label' => 'Second', 'start' => '2026-03-10 09:30', 'end' => '2026-03-10 10:30'],
            ]"></x-bladewind::scheduler>
        BLADE);

        $this->assertElementCount($html, self::EVENT, 2);
        $this->assertStringContainsString('width: calc(50%', $html);
    }

    #[Test]
    public function non_overlapping_events_take_the_full_column_width(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::scheduler date="2026-03-10" :events="[
                ['id' => 'e1', 'label' => 'First', 'start' => '2026-03-10 09:00', 'end' => '2026-03-10 10:00'],
                ['id' => 'e2', 'label' => 'Second', 'start' => '2026-03-10 11:00', 'end' => '2026-03-10 12:00'],
            ]"></x-bladewind::scheduler>
        BLADE);

        $this->assertElementCount($html, self::EVENT, 2);
        $this->assertStringContainsString('width: calc(100%', $html);
    }

    #[Test]
    public function an_event_with_an_href_renders_as_a_link(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-bladewind::scheduler date="2026-03-10" :events="[
                ['id' => 'e1', 'label' => 'Consultation', 'start' => '2026-03-10 09:00', 'end' => '2026-03-10 10:00', 'href' => '/bookings/1'],
            ]"></x-bladewind::scheduler>
        BLADE);

        $this->assertElementCount($html, '//a[@data-event]', 1);
        $this->assertAttribute($html, '//a[@data-event]', 'href', '/bookings/1');
    }

    #[Test]
    public function timezone_label_is_shown_when_given(): void
    {
        $html = $this->render('<x-bladewind::scheduler date="2026-03-10" timezone="America/New_York"></x-bladewind::scheduler>');

        $this->assertStringContainsString('America/New_York', $html);
    }

    #[Test]
    public function additional_classes_are_applied(): void
    {
        $html = $this->render('<x-bladewind::scheduler date="2026-03-10" class="my-scheduler"></x-bladewind::scheduler>');

        $this->assertHasClasses($html, self::ROOT, ['my-scheduler']);
    }
}
