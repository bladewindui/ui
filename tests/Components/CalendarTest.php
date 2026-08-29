<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CalendarTest extends TestCase
{
    use RendersComponents;

    private function calendar(string $attributes = '', ?array $events = null): string
    {
        return $this->render(
            '<x-bladewind::calendar '.$attributes.' :events="$events" />',
            ['events' => $events ?? []]
        );
    }

    #[Test]
    public function it_renders_a_named_accessible_month_grid(): void
    {
        $html = $this->calendar('name="team" label="Team calendar" date="2026-08-15"');

        $this->assertElementCount($html, '//*[@data-bw-calendar]', 1);
        $this->assertAttribute($html, '//*[@data-bw-calendar]', 'data-name', 'team');
        $this->assertAttribute($html, '//*[@data-bw-calendar]', 'aria-label', 'Team calendar');
        $this->assertAttribute($html, '//table[@data-bw-calendar-table]', 'role', 'grid');
        $this->assertElementCount($html, '//thead/tr/th', 7);
        $this->assertElementCount($html, '//td[@data-bw-calendar-day]', 42);
        $this->assertStringContainsString('August 2026', $html);
    }

    #[Test]
    public function week_view_renders_seven_days_and_marks_the_view_switch(): void
    {
        $html = $this->calendar('name="team" view="week" date="2026-08-15"');

        $this->assertAttribute($html, '//*[@data-bw-calendar]', 'data-view', 'week');
        $this->assertElementCount($html, '//td[@data-bw-calendar-day]', 7);
        $this->assertAttribute($html, '//button[@data-bw-calendar-view="week"]', 'aria-pressed', 'true');
        $this->assertAttribute($html, '//button[@data-bw-calendar-view="month"]', 'aria-pressed', 'false');
    }

    #[Test]
    public function events_render_up_to_the_limit_behind_a_focusable_more_button(): void
    {
        $events = [
            ['date' => '2026-08-15', 'label' => 'Standup', 'type' => 'info'],
            ['date' => '2026-08-15', 'label' => 'Review', 'type' => 'success'],
            ['date' => '2026-08-15', 'label' => 'Retro', 'type' => 'warning'],
            ['date' => '2026-08-15', 'label' => 'Overflow item', 'type' => 'danger'],
        ];
        $html = $this->calendar('name="team" date="2026-08-15"', $events);

        $cell = '//td[@data-bw-calendar-day and @data-date="2026-08-15"]';
        $this->assertElementCount($html, $cell.'//*[@data-bw-calendar-overflow-event="false"]', 3);
        $this->assertElementCount($html, $cell.'//*[@data-bw-calendar-overflow-event="true"]', 1);
        $this->assertElementCount($html, $cell.'//*[@data-bw-calendar-overflow-event="true" and @hidden]', 1);
        $this->assertElementCount($html, $cell.'//button[@data-bw-calendar-more]', 1);
        $this->assertStringContainsString('+1 more', $html);
    }

    #[Test]
    public function a_multi_day_event_appears_on_every_date_it_spans(): void
    {
        $events = [['date' => '2026-08-20', 'end' => '2026-08-22', 'label' => 'Conference', 'type' => 'success']];
        $html = $this->calendar('name="team" date="2026-08-15"', $events);

        foreach (['2026-08-20', '2026-08-21', '2026-08-22'] as $date) {
            $this->assertElementCount(
                $html,
                '//td[@data-bw-calendar-day and @data-date="'.$date.'"]//*[contains(@class,"bw-calendar-event-success")]',
                1
            );
        }
        $this->assertElementCount($html, '//td[@data-bw-calendar-day and @data-date="2026-08-19"]//*[contains(@class,"bw-calendar-event")]', 0);
    }

    #[Test]
    public function single_selection_renders_aria_selected_and_one_hidden_input(): void
    {
        $html = $this->calendar('name="team" date="2026-08-15" selectable="single" selected="2026-08-15"');

        $this->assertAttribute($html, '//td[@data-date="2026-08-15"]', 'aria-selected', 'true');
        $this->assertAttribute($html, '//td[@data-date="2026-08-14"]', 'aria-selected', 'false');
        $this->assertElementCount($html, '//input[@data-bw-calendar-input]', 1);
        $this->assertAttribute($html, '//input[@data-bw-calendar-input]', 'name', 'team');
        $this->assertAttribute($html, '//input[@data-bw-calendar-input]', 'value', '2026-08-15');
    }

    #[Test]
    public function multiple_selection_renders_one_bracketed_hidden_input_per_date(): void
    {
        $html = $this->calendar('name="team" date="2026-08-15" selectable="multiple" selected="2026-08-14,2026-08-15"');

        $this->assertElementCount($html, '//input[@data-bw-calendar-input]', 2);
        $this->assertAttribute($html, '//input[@value="2026-08-14"]', 'name', 'team[]');
        $this->assertAttribute($html, '//input[@value="2026-08-15"]', 'name', 'team[]');
    }

    #[Test]
    public function no_selection_mode_renders_no_aria_selected_and_no_inputs(): void
    {
        $html = $this->calendar('name="team" date="2026-08-15"');

        $this->assertAttribute($html, '//td[@data-date="2026-08-15"]', 'aria-selected', null);
        $this->assertElementCount($html, '//input[@data-bw-calendar-input]', 0);
    }

    #[Test]
    public function min_max_and_explicit_disabled_dates_mark_cells_aria_disabled(): void
    {
        $html = $this->calendar('name="team" date="2026-08-15" min-date="2026-08-10" max-date="2026-08-20" disabled-dates="2026-08-15"');

        $this->assertAttribute($html, '//td[@data-date="2026-08-05"]', 'aria-disabled', 'true');
        $this->assertAttribute($html, '//td[@data-date="2026-08-25"]', 'aria-disabled', 'true');
        $this->assertAttribute($html, '//td[@data-date="2026-08-15"]', 'aria-disabled', 'true');
        $this->assertAttribute($html, '//td[@data-date="2026-08-12"]', 'aria-disabled', null);
    }

    #[Test]
    public function outside_month_days_are_disabled_and_hidden_when_show_other_month_days_is_false(): void
    {
        $shown = $this->calendar('name="team" date="2026-08-15"');
        $this->assertAttribute($shown, '//td[@data-date="2026-07-27"]', 'aria-disabled', 'true');
        $this->assertAttribute($shown, '//td[@data-date="2026-07-27"]', 'hidden', null);

        $hidden = $this->calendar('name="team" date="2026-08-15" show-other-month-days="false"');
        $this->assertElementCount($hidden, '//td[@data-date="2026-07-27" and @hidden]', 1);
    }

    #[Test]
    public function week_numbers_render_when_enabled(): void
    {
        $html = $this->calendar('name="team" date="2026-08-15" show-week-numbers="true"');

        $this->assertElementCount($html, '//td[@class="bw-calendar-week-number"]', 6);
    }

    #[Test]
    public function height_fixes_the_scroll_wrapper_so_the_calendar_does_not_change_size_across_views(): void
    {
        $customHeight = $this->calendar('name="team" date="2026-08-15" height="28rem"');
        $this->assertAttribute($customHeight, '//*[@data-bw-calendar-scroll]', 'style', 'height: 28rem');

        // fixed height is on by default, sized for a 6-week month
        $defaultHeight = $this->calendar('name="team" date="2026-08-15"');
        $this->assertAttribute($defaultHeight, '//*[@data-bw-calendar-scroll]', 'style', 'height: 40rem');

        // an explicit empty value opts back out to natural, content-driven sizing
        $natural = $this->calendar('name="team" date="2026-08-15" height=""');
        $this->assertAttribute($natural, '//*[@data-bw-calendar-scroll]', 'style', null);
    }

    #[Test]
    public function configuration_defaults_are_applied(): void
    {
        config()->set('bladewind.calendar.selectable', 'single');
        config()->set('bladewind.calendar.max_events_per_day', 1);
        config()->set('bladewind.calendar.week_starts', 'monday');
        config()->set('bladewind.calendar.height', '30rem');

        $events = [
            ['date' => '2026-08-15', 'label' => 'One', 'type' => 'info'],
            ['date' => '2026-08-15', 'label' => 'Two', 'type' => 'info'],
        ];
        $html = $this->calendar('name="team" date="2026-08-15"', $events);

        $this->assertAttribute($html, '//*[@data-bw-calendar]', 'data-selectable', 'single');
        $this->assertAttribute($html, '//*[@data-bw-calendar]', 'data-week-starts', 'monday');
        $this->assertAttribute($html, '//*[@data-bw-calendar-scroll]', 'style', 'height: 30rem');
        $this->assertStringContainsString('+1 more', $html);
    }

    #[Test]
    public function labels_and_names_are_escaped(): void
    {
        $html = $this->render(
            '<x-bladewind::calendar :name="$name" :label="$label" date="2026-08-15" :events="$events" />',
            [
                'name' => 'team" onmouseover="bad',
                'label' => 'Team <unsafe>',
                'events' => [['date' => '2026-08-15', 'label' => '<script>bad()</script>', 'type' => 'info']],
            ]
        );

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertAttribute($html, '//*[@data-bw-calendar]', 'aria-label', 'Team <unsafe>');
    }
}
