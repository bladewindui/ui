[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/calendar)](https://packagist.org/packages/bladewindui/calendar)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Calendar

An inline month/week/day calendar for displaying and selecting dates or events — distinct from [Datepicker](https://bladewindui.com/component/datepicker), which is a popup calendar bound to a single form input. Calendar is always visible, can show events on any day, and supports single or multiple (non-contiguous) date selection. For a form-bound date range, use Datepicker's `range` option instead.

## Installation

```bash
composer require bladewindui/calendar
```

The package installs Bladewind Core and Icon automatically.

## Usage

```blade
<x-bladewind::calendar
    name="team-calendar"
    label="Team calendar"
    view="month"
    :events="[
        ['date' => '2026-08-14', 'label' => 'Sprint planning', 'type' => 'info'],
        ['date' => '2026-08-18', 'end' => '2026-08-20', 'label' => 'Conference', 'type' => 'success'],
    ]" />
```

## Views

`view` is `month`, `week`, or `day`. All three are shown by default with a toggle in the header; `date` is the anchor day (`Y-m-d`, defaults to today) — month view shows the month containing it, week view the week containing it, day view just that day. Week and day view share the same hour grid: an all-day row across the top for date-only and multi-day events, and 24 scrollable hour rows below with timed events positioned and sized to their duration, similar to Outlook or Google Calendar. Day view is that same grid narrowed to one column. Both open scrolled to a sensible hour rather than midnight.

## Selection

`selectable` is `none` (display-only, the default), `single`, or `multiple`. Pass pre-selected dates through `selected` as a `Y-m-d` string, comma-separated string, or array. When selectable, the component renders hidden inputs under `name` (or `name[]` for multiple) so the current selection posts with the surrounding form.

## Events

`events` is an array of `['date' => 'Y-m-d', 'end' => optional, 'label', 'type' => info|success|warning|danger, 'href' => optional, 'description' => optional]`.

An event without a clock time in `date` is all-day: `end` (also date-only) spans it across multiple days, and it shows as a marker in month view and a banner across the days it covers in week and day view's all-day row. Each day shows up to `max-events-per-day` (default 3) markers in month view; the rest sit behind a real, focusable "+N more" button.

An event with a clock time in `date` (e.g. `'2026-09-01 15:00'`) is timed: `end` is also a time on the same day, defaulting to one hour after `date` when omitted. Timed events are positioned in week and day view's hour grid; events that overlap in time are placed in side-by-side columns. In month view, timed events still show as a marker, prefixed with their start time (e.g. "3:00pm Kenya project review"). Cross-midnight timed events are clamped to end at 23:59 the same day.

## Event details drawer

Give an event a `description` and its marker becomes a button that opens a details drawer, contained inside the calendar's own box, showing the event's date/time, label, description, and (if `href` is also set) a "View full details" link. Events without a `description` stay plain links (with `href`) or static text, exactly as before. Nothing needs to be turned on — this is automatic wherever any event in the array has a `description`.

```blade
<x-bladewind::calendar
    name="team-calendar"
    :events="[
        ['date' => '2026-08-14', 'label' => 'Sprint planning', 'type' => 'info', 'description' => 'Review the roadmap and assign owners for Q3.', 'href' => '/events/sprint-planning'],
    ]" />
```

The drawer is a quick peek, not a modal takeover: there's no backdrop, so clicking straight from one event to another swaps its content without closing it first. Escape or its close button dismiss it.

## Restricting dates

`min-date` and `max-date` bound the navigable/selectable range. `disabled-dates` disables specific days (e.g. holidays) regardless of range. Disabled days remain visible and reachable with the arrow keys, but cannot be selected. `show-other-month-days` (default `true`) renders adjacent-month days to fill the grid, dimmed and disabled.

## Fixed height

The grid has a fixed height by default (`40rem`, room for a 6-week month — the tallest a month view ever renders), with an internal scrollbar — so the calendar never changes size navigating between months or switching views. A month with fewer weeks, or week or day view, just leaves empty space at the bottom of the grid rather than shrinking. Pass `height` with your own value (e.g. `28rem`) to use a different fixed size, or an empty string (`height=""`) to fall back to natural, content-driven sizing instead. Each day cell itself already has a fixed height regardless of how many events it holds; a day with more events than `max-events-per-day` gets its own small internal scrollbar once "+N more" is expanded, rather than growing the row.

## Navigation

Previous/next/today controls and PageUp/PageDown (a day in day view, a week in week view, a month in month view; Shift+PageUp/PageDown steps one level up from that — a week, a month, or a year) rebuild the grid in the browser using the `events` already passed in — no round trip. Set `client-navigation="false"` for a server-driven calendar instead: navigation only fires `before-navigate`/`navigate` and the application re-renders (e.g. a fresh page, or your own Livewire/Inertia update).

## JavaScript API

```javascript
nextCalendarPeriod('team-calendar');
previousCalendarPeriod('team-calendar');
goToCalendarToday('team-calendar');
goToCalendarMonth('team-calendar', 2026, 12);
setCalendarView('team-calendar', 'week');
selectCalendarDate('team-calendar', '2026-08-14');
clearCalendarSelection('team-calendar');
calendarSelectedDates('team-calendar'); // ['2026-08-14']
```

## Events (JavaScript)

Cancelable `before-navigate`, `before-view-change`, and `before-select`, and `navigate`, `view-change`, and `select` after changes. All event names start with `bladewind:calendar:`.

## Accessibility

The grid is a real `role="grid"` with `role="gridcell"` days and roving `tabindex` — arrow keys move focus by day (in week and day view, between the day-column headers; in day view there is only one, so left/right navigate to the previous/next day), Home/End jump to the start/end of the row, PageUp/PageDown change the period, and Enter/Space selects. Event markers, including timed events positioned in week and day view's hour grid, are real, independently focusable links or buttons, not decorations bolted onto a hand-rolled widget.

## Documentation

Full examples and the complete attribute tables are available at [bladewindui.com/component/calendar](https://bladewindui.com/component/calendar).

## License

MIT. See the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
