[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/calendar)](https://packagist.org/packages/bladewindui/calendar)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Calendar

An inline month/week calendar for displaying and selecting dates or events — distinct from [Datepicker](https://bladewindui.com/component/datepicker), which is a popup calendar bound to a single form input. Calendar is always visible, can show events on any day, and supports single or multiple (non-contiguous) date selection. For a form-bound date range, use Datepicker's `range` option instead.

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

`view` is `month` or `week`. Both are shown by default with a toggle in the header; `date` is the anchor day (`Y-m-d`, defaults to today) — month view shows the month containing it, week view the week containing it.

## Selection

`selectable` is `none` (display-only, the default), `single`, or `multiple`. Pass pre-selected dates through `selected` as a `Y-m-d` string, comma-separated string, or array. When selectable, the component renders hidden inputs under `name` (or `name[]` for multiple) so the current selection posts with the surrounding form.

## Events

`events` is an array of `['date' => 'Y-m-d', 'end' => optional 'Y-m-d', 'label', 'type' => info|success|warning|danger, 'href' => optional]`. `end` spans an event across multiple days. Each day shows up to `max-events-per-day` (default 3) events; the rest sit behind a real, focusable "+N more" button.

## Restricting dates

`min-date` and `max-date` bound the navigable/selectable range. `disabled-dates` disables specific days (e.g. holidays) regardless of range. Disabled days remain visible and reachable with the arrow keys, but cannot be selected. `show-other-month-days` (default `true`) renders adjacent-month days to fill the grid, dimmed and disabled.

## Fixed height

Without `height`, the calendar's overall size follows its content: a month can render 4, 5, or 6 weeks depending on where it starts, and week view is naturally much shorter than month view. Set `height` (e.g. `28rem`) to cap the grid at a fixed size with an internal scrollbar instead — useful when the calendar sits in a layout that shouldn't jump around as the user navigates or switches views. Each day cell itself already has a fixed height regardless of how many events it holds; a day with more events than `max-events-per-day` gets its own small internal scrollbar once "+N more" is expanded, rather than growing the row.

## Navigation

Previous/next/today controls and PageUp/PageDown (Shift+PageUp/PageDown for the year in month view, or the month in week view) rebuild the grid in the browser using the `events` already passed in — no round trip. Set `client-navigation="false"` for a server-driven calendar instead: navigation only fires `before-navigate`/`navigate` and the application re-renders (e.g. a fresh page, or your own Livewire/Inertia update).

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

The grid is a real `role="grid"` with `role="gridcell"` days and roving `tabindex` — arrow keys move focus by day, Home/End jump to the start/end of the row, PageUp/PageDown change the month or week, and Enter/Space selects. Event markers are real, independently focusable links or buttons, not decorations bolted onto a hand-rolled widget.

## Documentation

Full examples and the complete attribute tables are available at [bladewindui.com/component/calendar](https://bladewindui.com/component/calendar).

## License

MIT. See the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
