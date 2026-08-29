{{-- format-ignore-start --}}
@props([
    // name of the calendar. Used for DOM scoping and, when selectable, as the
    // name posted with the hidden form input(s)
    'name' => null,

    // accessible name for the calendar grid
    'label' => 'Calendar',

    // month or week
    'view' => null,

    // anchor date (Y-m-d). Month view shows the month containing it, week view
    // shows the week containing it. Defaults to today.
    'date' => '',

    // first day of the week
    'weekStarts' => null,

    // none, single, or multiple (non-contiguous) date selection.
    // For a form-bound date-range input, use Datepicker's range option instead.
    'selectable' => null,

    // pre-selected date(s): a Y-m-d string, comma-separated string, or array
    'selected' => [],

    // navigable/selectable date range
    'minDate' => '',
    'maxDate' => '',

    // specific dates to disable regardless of range, e.g. holidays
    'disabledDates' => [],

    // ['date' => 'Y-m-d', 'end' => optional 'Y-m-d', 'label', 'type', 'href']
    'events' => [],
    'maxEventsPerDay' => null,

    // render (dimmed, disabled) days from the adjacent month to fill the grid
    'showOtherMonthDays' => null,

    'showWeekNumbers' => null,

    // rebuild the grid in the browser on navigation using the events already
    // passed in. Set false for a server-driven calendar: navigation only
    // emits before-navigate/navigate and the application re-renders.
    'clientNavigation' => null,

    'todayLabel' => null,
    'previousLabel' => null,
    'nextLabel' => null,

    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = preg_replace('/[^A-Za-z0-9_-]/', '-', trim((string) $name));
    if ($name === '') $name = defaultBladewindName('bw-calendar-');

    $view = in_array($view, ['month', 'week'], true) ? $view : config('bladewind.calendar.view', 'month');
    $weekStarts = in_array($weekStarts, ['sunday', 'monday'], true) ? $weekStarts : config('bladewind.calendar.week_starts', 'sunday');
    $selectable = in_array($selectable, ['none', 'single', 'multiple'], true) ? $selectable : config('bladewind.calendar.selectable', 'none');
    $maxEventsPerDay = max(0, (int) parseBladewindVariable($maxEventsPerDay ?? config('bladewind.calendar.max_events_per_day', 3), 'int'));
    $showOtherMonthDays = parseBladewindVariable($showOtherMonthDays ?? config('bladewind.calendar.show_other_month_days', true));
    $showWeekNumbers = parseBladewindVariable($showWeekNumbers ?? config('bladewind.calendar.show_week_numbers', false));
    $clientNavigation = parseBladewindVariable($clientNavigation ?? config('bladewind.calendar.client_navigation', true));
    $todayLabel = $todayLabel ?? config('bladewind.calendar.today_label', 'Today');
    $previousLabel = $previousLabel ?? config('bladewind.calendar.previous_label', 'Previous');
    $nextLabel = $nextLabel ?? config('bladewind.calendar.next_label', 'Next');

    $today = \Illuminate\Support\Carbon::today();
    $anchor = $date !== '' ? \Illuminate\Support\Carbon::parse($date) : $today->copy();

    $selected = is_array($selected) ? $selected : array_filter(explode(',', str_replace(' ', '', (string) $selected)));
    $selectedDates = array_values(array_unique(array_map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->toDateString(), $selected)));
    if ($selectable === 'single') $selectedDates = array_slice($selectedDates, 0, 1);
    if ($selectable === 'none') $selectedDates = [];

    $disabledDates = is_array($disabledDates) ? $disabledDates : array_filter(explode(',', str_replace(' ', '', (string) $disabledDates)));
    $disabledSet = array_map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->toDateString(), $disabledDates);
    $minCarbon = $minDate !== '' ? \Illuminate\Support\Carbon::parse($minDate)->startOfDay() : null;
    $maxCarbon = $maxDate !== '' ? \Illuminate\Support\Carbon::parse($maxDate)->startOfDay() : null;

    $weekStartConst = $weekStarts === 'monday' ? \Carbon\Carbon::MONDAY : \Carbon\Carbon::SUNDAY;
    $weekEndConst = $weekStarts === 'monday' ? \Carbon\Carbon::SUNDAY : \Carbon\Carbon::SATURDAY;

    if ($view === 'week') {
        $gridStart = $anchor->copy()->startOfWeek($weekStartConst);
        $gridEnd = $anchor->copy()->endOfWeek($weekEndConst);
        $periodLabel = $gridStart->isSameMonth($gridEnd)
            ? $gridStart->format('M j').' – '.$gridEnd->format('j, Y')
            : $gridStart->format('M j').' – '.$gridEnd->format('M j, Y');
    } else {
        $monthStart = $anchor->copy()->startOfMonth();
        $monthEnd = $anchor->copy()->endOfMonth();
        $gridStart = $monthStart->copy()->startOfWeek($weekStartConst);
        $gridEnd = $monthEnd->copy()->endOfWeek($weekEndConst);
        $periodLabel = $anchor->format('F Y');
    }

    // normalise events into a per-date map, expanding multi-day spans across the
    // dates they touch. Capped so a bad/huge span cannot blow up the grid.
    $eventsByDate = [];
    foreach ((array) $events as $event) {
        if (empty($event['date'])) continue;
        $start = \Illuminate\Support\Carbon::parse($event['date'])->startOfDay();
        $end = ! empty($event['end']) ? \Illuminate\Support\Carbon::parse($event['end'])->startOfDay() : $start->copy();
        if ($end->lt($start)) $end = $start->copy();

        $cursor = $start->copy();
        $guard = 0;
        while ($cursor->lte($end) && $guard < 366) {
            $key = $cursor->toDateString();
            $eventsByDate[$key] ??= [];
            $eventsByDate[$key][] = [
                'label' => (string) ($event['label'] ?? ''),
                'type' => (string) ($event['type'] ?? 'info'),
                'href' => $event['href'] ?? null,
            ];
            $cursor->addDay();
            $guard++;
        }
    }

    $weeks = [];
    $week = [];
    $cursor = $gridStart->copy();
    while ($cursor->lte($gridEnd)) {
        $iso = $cursor->toDateString();
        $inPeriod = $view === 'week' || $cursor->month === $anchor->month;
        $isDisabled = ! $inPeriod
            || ($minCarbon && $cursor->lt($minCarbon))
            || ($maxCarbon && $cursor->gt($maxCarbon))
            || in_array($iso, $disabledSet, true);
        $dayEvents = $eventsByDate[$iso] ?? [];

        $week[] = [
            'iso' => $iso,
            'day' => $cursor->day,
            'label' => $cursor->translatedFormat('l, F j, Y'),
            'inPeriod' => $inPeriod,
            'isToday' => $cursor->isSameDay($today),
            'isSelected' => in_array($iso, $selectedDates, true),
            'isDisabled' => $isDisabled,
            'events' => $dayEvents,
            'weekNumber' => $cursor->weekOfYear,
        ];

        if (count($week) === 7) { $weeks[] = $week; $week = []; }
        $cursor->addDay();
    }

    $dayKeys = $weekStarts === 'monday'
        ? ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']
        : ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    $weekdayLabels = array_map(fn ($k) => __('bladewind::bladewind.'.$k), $dayKeys);

    // the cell that starts with tabindex="0": the selected date if visible and
    // enabled, else today, else the first enabled day, else the grid's first cell
    $focusIso = null;
    foreach ([fn ($d) => $d['isSelected'], fn ($d) => $d['isToday'], fn ($d) => true] as $match) {
        foreach ($weeks as $w) {
            foreach ($w as $d) {
                if (! $d['isDisabled'] && $match($d)) { $focusIso = $d['iso']; break 3; }
            }
        }
    }
    $focusIso ??= $weeks[0][0]['iso'] ?? $gridStart->toDateString();

    $eventsPayload = collect($events)->map(fn ($e) => [
        'date' => (string) ($e['date'] ?? ''),
        'end' => ! empty($e['end']) ? (string) $e['end'] : null,
        'label' => (string) ($e['label'] ?? ''),
        'type' => (string) ($e['type'] ?? 'info'),
        'href' => $e['href'] ?? null,
    ])->filter(fn ($e) => $e['date'] !== '')->values();

    $titleId = 'bw-'.$name.'-title';
    $rootAttributes = $attributes->exceptPropAliases(get_defined_vars())->class(['bw-calendar']);
@endphp
{{-- format-ignore-end --}}

<div {{ $rootAttributes }}
    data-bw-calendar
    data-name="{{ $name }}"
    data-view="{{ $view }}"
    data-week-starts="{{ $weekStarts }}"
    data-selectable="{{ $selectable }}"
    data-max-events-per-day="{{ $maxEventsPerDay }}"
    data-show-other-month-days="{{ $showOtherMonthDays ? 'true' : 'false' }}"
    data-show-week-numbers="{{ $showWeekNumbers ? 'true' : 'false' }}"
    data-client-navigation="{{ $clientNavigation ? 'true' : 'false' }}"
    @if($minDate) data-min-date="{{ $minCarbon->toDateString() }}" @endif
    @if($maxDate) data-max-date="{{ $maxCarbon->toDateString() }}" @endif
    data-disabled-dates="{{ implode(',', $disabledSet) }}"
    data-anchor="{{ $anchor->toDateString() }}"
    data-today-label="{{ $todayLabel }}"
    data-previous-label="{{ $previousLabel }}"
    data-next-label="{{ $nextLabel }}"
    role="group" aria-roledescription="calendar" aria-label="{{ $label }}">

    <div class="bw-calendar-header">
        <div class="bw-calendar-title" id="{{ $titleId }}" data-bw-calendar-title aria-live="polite">{{ $periodLabel }}</div>
        <div class="bw-calendar-nav">
            <div class="bw-calendar-view-switch" role="group" aria-label="Calendar view">
                <button type="button" class="bw-calendar-view-button" data-bw-calendar-view="month" aria-pressed="{{ $view === 'month' ? 'true' : 'false' }}">Month</button>
                <button type="button" class="bw-calendar-view-button" data-bw-calendar-view="week" aria-pressed="{{ $view === 'week' ? 'true' : 'false' }}">Week</button>
            </div>
            <button type="button" class="bw-calendar-today-button" data-bw-calendar-today>{{ $todayLabel }}</button>
            <button type="button" class="bw-calendar-nav-button" data-bw-calendar-prev aria-label="{{ $previousLabel }}">
                <x-bladewind::icon name="chevron-left" class="!size-4" />
            </button>
            <button type="button" class="bw-calendar-nav-button" data-bw-calendar-next aria-label="{{ $nextLabel }}">
                <x-bladewind::icon name="chevron-right" class="!size-4" />
            </button>
        </div>
    </div>

    <table class="bw-calendar-grid" data-bw-calendar-table role="grid" aria-labelledby="{{ $titleId }}">
        <thead>
            <tr role="row">
                @if($showWeekNumbers)<th class="bw-calendar-week-number-header" scope="col"><span class="sr-only">Week</span></th>@endif
                @foreach($weekdayLabels as $weekdayLabel)
                    <th scope="col" abbr="{{ $weekdayLabel }}">
                        <span aria-hidden="true">{{ mb_substr($weekdayLabel, 0, 3) }}</span>
                        <span class="sr-only">{{ $weekdayLabel }}</span>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody data-bw-calendar-body>
            @foreach($weeks as $week)
                <tr role="row">
                    @if($showWeekNumbers)<td class="bw-calendar-week-number">{{ $week[0]['weekNumber'] }}</td>@endif
                    @foreach($week as $day)
                        @php $overflow = max(0, count($day['events']) - $maxEventsPerDay); @endphp
                        <td role="gridcell"
                            data-bw-calendar-day
                            data-date="{{ $day['iso'] }}"
                            tabindex="{{ $day['iso'] === $focusIso ? '0' : '-1' }}"
                            @if($selectable !== 'none') aria-selected="{{ $day['isSelected'] ? 'true' : 'false' }}" @endif
                            @if($day['isToday']) aria-current="date" @endif
                            @if($day['isDisabled']) aria-disabled="true" @endif
                            aria-label="{{ $day['label'] }}"
                            @class([
                                'bw-calendar-cell',
                                'bw-calendar-cell-outside' => ! $day['inPeriod'],
                                'bw-calendar-cell-today' => $day['isToday'],
                                'bw-calendar-cell-selected' => $day['isSelected'],
                                'bw-calendar-cell-disabled' => $day['isDisabled'],
                            ])
                            @if(! $day['inPeriod'] && ! $showOtherMonthDays) hidden @endif>
                            <span class="bw-calendar-cell-date">{{ $day['day'] }}</span>
                            @if(count($day['events']))
                                <div class="bw-calendar-cell-events">
                                    @foreach($day['events'] as $index => $event)
                                        @php $isOverflow = $index >= $maxEventsPerDay; @endphp
                                        @if($event['href'])
                                            <a href="{{ $event['href'] }}"
                                               class="bw-calendar-event bw-calendar-event-{{ $event['type'] }}"
                                               data-bw-calendar-overflow-event="{{ $isOverflow ? 'true' : 'false' }}"
                                               @if($isOverflow) hidden @endif>{{ $event['label'] }}</a>
                                        @else
                                            <span class="bw-calendar-event bw-calendar-event-{{ $event['type'] }}"
                                                  data-bw-calendar-overflow-event="{{ $isOverflow ? 'true' : 'false' }}"
                                                  @if($isOverflow) hidden @endif>{{ $event['label'] }}</span>
                                        @endif
                                    @endforeach
                                    @if($overflow > 0)
                                        <button type="button" class="bw-calendar-event-more" data-bw-calendar-more aria-expanded="false">+{{ $overflow }} more</button>
                                    @endif
                                </div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($selectable !== 'none')
        <div data-bw-calendar-inputs>
            @foreach($selectedDates as $selectedDate)
                <input type="hidden" name="{{ $selectable === 'multiple' ? $name.'[]' : $name }}" value="{{ $selectedDate }}" data-bw-calendar-input="{{ $selectedDate }}" />
            @endforeach
        </div>
    @endif
</div>

<x-bladewind::script :nonce="$nonce">
    initBladewindCalendar({
    name: "{{ $name }}",
    monthNames: [{!! collect(['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'])->map(fn ($k) => '"'.__('bladewind::bladewind.'.$k).'"')->implode(',') !!}],
    dayNames: [{!! collect(['sun','mon','tue','wed','thu','fri','sat'])->map(fn ($k) => '"'.__('bladewind::bladewind.'.$k).'"')->implode(',') !!}],
    events: {!! json_encode($eventsPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}
    });
</x-bladewind::script>
