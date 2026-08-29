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
        $this->assertElementCount($html, '//*[@data-bw-calendar-day]', 7);
        $this->assertElementCount($html, '//*[@data-bw-calendar-week]', 1);
        $this->assertElementCount($html, '//*[@data-bw-calendar-week-body]', 1);
        $this->assertAttribute($html, '//button[@data-bw-calendar-view="week"]', 'aria-pressed', 'true');
        $this->assertAttribute($html, '//button[@data-bw-calendar-view="month"]', 'aria-pressed', 'false');
    }

    #[Test]
    public function day_view_renders_a_single_column_hour_grid_and_marks_the_view_switch(): void
    {
        $html = $this->calendar('name="team" view="day" date="2026-08-15"');

        $this->assertAttribute($html, '//*[@data-bw-calendar]', 'data-view', 'day');
        $this->assertElementCount($html, '//*[@data-bw-calendar-day]', 1);
        $this->assertElementCount($html, '//*[@data-bw-calendar-week]', 1);
        $this->assertAttribute($html, '//*[@data-bw-calendar-week]', 'style', '--bw-calendar-week-days: 1');
        $this->assertElementCount($html, '//*[contains(concat(" ", normalize-space(@class), " "), " bw-calendar-week-day-column ")]', 1);
        $this->assertAttribute($html, '//button[@data-bw-calendar-view="day"]', 'aria-pressed', 'true');
        $this->assertAttribute($html, '//button[@data-bw-calendar-view="week"]', 'aria-pressed', 'false');
        $this->assertStringContainsString('Saturday, August 15, 2026', $html);
    }

    #[Test]
    public function day_view_positions_a_timed_event_and_shows_an_all_day_banner(): void
    {
        $events = [
            ['date' => '2026-08-15 09:00', 'end' => '2026-08-15 10:30', 'label' => 'Standup', 'type' => 'info'],
            ['date' => '2026-08-15', 'label' => 'Holiday', 'type' => 'warning'],
        ];
        $html = $this->calendar('name="team" view="day" date="2026-08-15"', $events);

        $event = $this->timedEventXpath('2026-08-15');
        $this->assertElementCount($html, $event, 1);
        $this->assertAttributeContains($html, $event, 'style', 'top: 27rem'); // 9h * 3rem/h
        $this->assertAttributeContains($html, $event, 'style', 'left: 0%'); // nothing to overlap with

        $banner = '//*[contains(concat(" ", normalize-space(@class), " "), " bw-calendar-week-allday-banner ")]';
        $this->assertElementCount($html, $banner, 1);
        $this->assertAttributeContains($html, $banner, 'style', 'span 1');
    }

    /** Exact class-token match — plain contains(@class,...) also matches e.g. bw-calendar-week-timed-event-label. */
    private function timedEventXpath(string $date): string
    {
        return '//div[contains(concat(" ", normalize-space(@class), " "), " bw-calendar-week-day-column ") and @data-date="'.$date.'"]'
            .'//*[contains(concat(" ", normalize-space(@class), " "), " bw-calendar-week-timed-event ")]';
    }

    #[Test]
    public function week_view_positions_a_timed_event_in_the_hour_grid(): void
    {
        // the week containing 2026-08-15 (a Saturday) runs Sun 2026-08-09 to Sat 2026-08-15
        $events = [['date' => '2026-08-11 09:00', 'end' => '2026-08-11 10:30', 'label' => 'Standup', 'type' => 'info']];
        $html = $this->calendar('name="team" view="week" date="2026-08-15"', $events);

        $event = $this->timedEventXpath('2026-08-11');
        $this->assertElementCount($html, $event, 1);
        $this->assertAttributeContains($html, $event, 'style', 'top: 27rem'); // 9h * 3rem/h
        $this->assertAttributeContains($html, $event, 'style', 'height: 4.5rem'); // 1.5h * 3rem/h
        $this->assertStringContainsString('9:00am', $html);
    }

    #[Test]
    public function week_view_folds_a_timed_event_into_the_month_view_marker_with_a_time_prefix(): void
    {
        $events = [['date' => '2026-08-15 09:00', 'label' => 'Standup', 'type' => 'info']];
        $html = $this->calendar('name="team" date="2026-08-15"', $events);

        $this->assertStringContainsString('9:00am Standup', $html);
    }

    #[Test]
    public function week_view_packs_overlapping_timed_events_into_side_by_side_columns(): void
    {
        $events = [
            ['date' => '2026-08-11 09:00', 'end' => '2026-08-11 10:00', 'label' => 'A', 'type' => 'info'],
            ['date' => '2026-08-11 09:30', 'end' => '2026-08-11 10:30', 'label' => 'B', 'type' => 'success'],
        ];
        $html = $this->calendar('name="team" view="week" date="2026-08-15"', $events);

        $event = $this->timedEventXpath('2026-08-11');
        $this->assertElementCount($html, $event, 2);
        // rendered in start-time order: A (09:00) first, B (09:30) second
        $this->assertAttributeContains($html, '('.$event.')[1]', 'style', 'width: calc(50%');
        $this->assertAttributeContains($html, '('.$event.')[1]', 'style', 'left: 0%');
        $this->assertAttributeContains($html, '('.$event.')[2]', 'style', 'width: calc(50%');
        $this->assertAttributeContains($html, '('.$event.')[2]', 'style', 'left: 50%');
    }

    #[Test]
    public function week_view_non_overlapping_timed_events_each_take_the_full_column_width(): void
    {
        $events = [
            ['date' => '2026-08-11 09:00', 'end' => '2026-08-11 09:30', 'label' => 'A', 'type' => 'info'],
            ['date' => '2026-08-11 14:00', 'end' => '2026-08-11 14:30', 'label' => 'B', 'type' => 'info'],
        ];
        $html = $this->calendar('name="team" view="week" date="2026-08-15"', $events);

        $event = $this->timedEventXpath('2026-08-11');
        $this->assertElementCount($html, $event, 2);
        $this->assertAttributeContains($html, '('.$event.')[1]', 'style', 'left: 0%');
        $this->assertAttributeContains($html, '('.$event.')[2]', 'style', 'left: 0%');
    }

    #[Test]
    public function week_view_renders_an_all_day_banner_spanning_the_days_it_covers(): void
    {
        // still within the Sun 2026-08-09 – Sat 2026-08-15 week
        $events = [['date' => '2026-08-11', 'end' => '2026-08-13', 'label' => 'Conference', 'type' => 'success']];
        $html = $this->calendar('name="team" view="week" date="2026-08-15"', $events);

        $banner = '//*[contains(concat(" ", normalize-space(@class), " "), " bw-calendar-week-allday-banner ")]';
        $this->assertElementCount($html, $banner, 1);
        $this->assertAttributeContains($html, $banner, 'style', 'span 3');
    }

    #[Test]
    public function week_view_stacks_overlapping_all_day_banners_onto_separate_rows(): void
    {
        $events = [
            ['date' => '2026-08-11', 'end' => '2026-08-13', 'label' => 'Conference', 'type' => 'success'],
            ['date' => '2026-08-12', 'label' => 'Holiday', 'type' => 'warning'],
        ];
        $html = $this->calendar('name="team" view="week" date="2026-08-15"', $events);

        $banner = '//*[contains(concat(" ", normalize-space(@class), " "), " bw-calendar-week-allday-banner ")]';
        $this->assertAttributeContains($html, '('.$banner.')[1]', 'style', 'grid-row: 1');
        $this->assertAttributeContains($html, '('.$banner.')[2]', 'style', 'grid-row: 2');
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
    public function no_event_details_drawer_renders_when_no_event_has_a_description(): void
    {
        $events = [['date' => '2026-08-15', 'label' => 'Sprint planning', 'type' => 'info', 'href' => '/events/1']];
        $html = $this->calendar('name="team" date="2026-08-15"', $events);

        $this->assertElementCount($html, '//*[@data-bw-calendar-event-drawer]', 0);
        $this->assertElementCount($html, '//a[@href="/events/1"]', 1);
        $this->assertElementCount($html, '//*[@data-bw-calendar-event-trigger]', 0);
    }

    #[Test]
    public function a_month_view_marker_with_a_description_becomes_a_button_that_opens_the_drawer(): void
    {
        $events = [
            ['date' => '2026-08-14', 'label' => 'Plain link', 'type' => 'info', 'href' => '/events/1'],
            ['date' => '2026-08-15', 'label' => 'Sprint planning', 'type' => 'success', 'description' => "Room 4B\nBring laptops", 'href' => '/events/2'],
        ];
        $html = $this->calendar('name="team" date="2026-08-15"', $events);

        $this->assertElementCount($html, '//*[@data-bw-calendar-event-drawer]', 1);
        $trigger = '//button[@data-bw-calendar-event-trigger and @data-bw-calendar-event-index="1"]';
        $this->assertElementCount($html, $trigger, 1);
        $this->assertStringContainsString('Sprint planning', $html);
        // the plain event (index 0, no description) is untouched: still a real link, not a button
        $this->assertElementCount($html, '//a[@href="/events/1"]', 1);
        $this->assertElementCount($html, '//button[@data-bw-calendar-event-index="0"]', 0);
    }

    #[Test]
    public function week_view_timed_events_and_all_day_banners_with_a_description_become_buttons(): void
    {
        $events = [
            ['date' => '2026-08-11 09:00', 'end' => '2026-08-11 10:00', 'label' => 'Standup', 'type' => 'info', 'description' => 'Daily sync'],
            ['date' => '2026-08-12', 'end' => '2026-08-13', 'label' => 'Offsite', 'type' => 'success', 'description' => 'Bring a jacket'],
        ];
        $html = $this->calendar('name="team" view="week" date="2026-08-15"', $events);

        $this->assertElementCount($html, '//*[@data-bw-calendar-event-drawer]', 1);
        $this->assertElementCount(
            $html,
            '//div[contains(concat(" ", normalize-space(@class), " "), " bw-calendar-week-day-column ") and @data-date="2026-08-11"]'
                .'//button[@data-bw-calendar-event-trigger and @data-bw-calendar-event-index="0"]',
            1
        );
        $this->assertElementCount(
            $html,
            '//button[@data-bw-calendar-event-trigger and @data-bw-calendar-event-index="1" and contains(concat(" ", normalize-space(@class), " "), " bw-calendar-week-allday-banner ")]',
            1
        );
    }

    #[Test]
    public function event_descriptions_never_render_as_raw_html(): void
    {
        $events = [['date' => '2026-08-15', 'label' => 'Sprint planning', 'type' => 'info', 'description' => '<img src=x onerror=alert(1)>']];
        $html = $this->calendar('name="team" date="2026-08-15"', $events);

        // description only ever reaches the page via the script payload,
        // with JSON_HEX_TAG escaping its angle brackets to < / > —
        // never as literal, executable markup
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString('\\u003Cimg src=x onerror=alert(1)\\u003E', $html);
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
