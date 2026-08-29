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
    // date/end may also carry a time ('2026-09-01 15:00'): an event with a time
    // is a timed event, positioned in week view's hour grid. An event without a
    // time is an all-day event, shown as a marker in month view and as a banner
    // across the days it spans in week view's all-day row.
    'events' => [],
    'maxEventsPerDay' => null,

    // render (dimmed, disabled) days from the adjacent month to fill the grid
    'showOtherMonthDays' => null,

    'showWeekNumbers' => null,

    // fixes the grid at this height (e.g. '28rem') with an internal scrollbar,
    // so switching between months with different week counts, or between month
    // and week view, never changes the calendar's overall height. Defaults to
    // room for a 6-week month, the tallest a month view ever renders. Pass an
    // empty string to fall back to natural, content-driven sizing instead.
    'height' => null,

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
    $height = $height ?? config('bladewind.calendar.height', '40rem');
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

    // A timed event carries a clock time in `date` ("2026-09-01 15:00"): it is
    // positioned in week view's hour grid. Anything else is an all-day event: a
    // marker in month view, a banner spanning its days in week view's all-day
    // row. Cross-midnight timed events are clamped to end at 23:59 the same
    // day — out of scope for v1, same as the multi-day span guard below.
    $isTimedEvent = fn ($value) => (bool) preg_match('/\d{1,2}:\d{2}/', (string) $value);

    // per-date lists behind month view's markers: all-day events as-is, timed
    // events prefixed with their start time, timed ones sorted after all-day
    // ones and among themselves by start time
    $eventsByDate = [];
    // per-date lists of timed events (only built out fully for week view, see
    // $timedLayout below), keyed the same way
    $timedByDate = [];

    foreach ((array) $events as $event) {
        if (empty($event['date'])) continue;

        if ($isTimedEvent($event['date'])) {
            $start = \Illuminate\Support\Carbon::parse($event['date']);
            $end = ! empty($event['end']) && $isTimedEvent($event['end'])
                ? \Illuminate\Support\Carbon::parse($event['end'])
                : $start->copy()->addHour();
            if ($end->lte($start)) $end = $start->copy()->addHour();
            $dayEnd = $start->copy()->endOfDay();
            if ($end->gt($dayEnd)) $end = $dayEnd;

            $key = $start->toDateString();
            $timedByDate[$key] ??= [];
            $timedByDate[$key][] = [
                'label' => (string) ($event['label'] ?? ''),
                'type' => (string) ($event['type'] ?? 'info'),
                'href' => $event['href'] ?? null,
                'start' => $start,
                'end' => $end,
                'startMinutes' => $start->hour * 60 + $start->minute,
                'endMinutes' => $end->hour * 60 + $end->minute,
            ];
            continue;
        }

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

    // fold timed events into month view's per-day markers too, chronologically,
    // after any all-day events already on that date
    foreach ($timedByDate as $key => $list) {
        usort($list, fn ($a, $b) => $a['startMinutes'] <=> $b['startMinutes']);
        foreach ($list as $timed) {
            $eventsByDate[$key] ??= [];
            $eventsByDate[$key][] = [
                'label' => $timed['start']->format('g:ia').' '.$timed['label'],
                'type' => $timed['type'],
                'href' => $timed['href'],
            ];
        }
    }

    $dayNameByDow = [
        0 => __('bladewind::bladewind.sun'),
        1 => __('bladewind::bladewind.mon'),
        2 => __('bladewind::bladewind.tue'),
        3 => __('bladewind::bladewind.wed'),
        4 => __('bladewind::bladewind.thu'),
        5 => __('bladewind::bladewind.fri'),
        6 => __('bladewind::bladewind.sat'),
    ];

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
            'dayName' => mb_substr($dayNameByDow[$cursor->dayOfWeek], 0, 3),
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

    $hourRowHeight = 3; // rem per hour in week view's hour grid
    $hoursInDay = 24;

    // week view only: all-day banners, clipped to the visible week and spanning
    // the day columns they cover, stacked into as few rows as they need so
    // overlapping banners don't sit on top of each other
    $allDayBanners = [];
    $allDayRowCount = 0;
    // week view only: each day's timed events packed into side-by-side columns
    // — events that don't overlap anything share column 0; a run of mutually
    // overlapping events gets one column each, sized to fit the widest moment
    // in that run. Simple and robust rather than perfectly space-optimal.
    $timedLayout = [];

    if ($view === 'week') {
        $rawAllDay = [];
        foreach ((array) $events as $event) {
            if (empty($event['date']) || $isTimedEvent($event['date'])) continue;
            $start = \Illuminate\Support\Carbon::parse($event['date'])->startOfDay();
            $end = ! empty($event['end']) ? \Illuminate\Support\Carbon::parse($event['end'])->startOfDay() : $start->copy();
            if ($end->lt($start)) $end = $start->copy();

            $clipStart = $start->lt($gridStart) ? $gridStart->copy() : $start;
            $clipEnd = $end->gt($gridEnd) ? $gridEnd->copy() : $end;
            if ($clipStart->gt($gridEnd) || $clipEnd->lt($gridStart)) continue;

            $rawAllDay[] = [
                'label' => (string) ($event['label'] ?? ''),
                'type' => (string) ($event['type'] ?? 'info'),
                'href' => $event['href'] ?? null,
                'startIndex' => (int) $gridStart->diffInDays($clipStart),
                'span' => (int) $gridStart->diffInDays($clipEnd) - (int) $gridStart->diffInDays($clipStart) + 1,
            ];
        }
        usort($rawAllDay, fn ($a, $b) => $a['startIndex'] <=> $b['startIndex']);
        $rowEnds = [];
        foreach ($rawAllDay as $banner) {
            $bannerEnd = $banner['startIndex'] + $banner['span'] - 1;
            $placedRow = null;
            foreach ($rowEnds as $rowIndex => $rowEnd) {
                if ($banner['startIndex'] > $rowEnd) { $placedRow = $rowIndex; break; }
            }
            $placedRow ??= count($rowEnds);
            $rowEnds[$placedRow] = $bannerEnd;
            $banner['row'] = $placedRow;
            $allDayBanners[] = $banner;
        }
        $allDayRowCount = max(1, count($rowEnds));

        foreach ($weeks[0] as $day) {
            $iso = $day['iso'];
            $dayEvents = $timedByDate[$iso] ?? [];
            usort($dayEvents, fn ($a, $b) => $a['startMinutes'] <=> $b['startMinutes']);

            $placed = [];
            $cluster = [];
            $clusterEndMinutes = null;
            $flush = function () use (&$cluster, &$placed) {
                if (! $cluster) return;
                $columns = [];
                $startAt = count($placed);
                foreach ($cluster as $item) {
                    $placedCol = null;
                    foreach ($columns as $colIndex => $colEndMinutes) {
                        if ($item['startMinutes'] >= $colEndMinutes) { $placedCol = $colIndex; break; }
                    }
                    $placedCol ??= count($columns);
                    $columns[$placedCol] = $item['endMinutes'];
                    $item['col'] = $placedCol;
                    $placed[] = $item;
                }
                $totalCols = count($columns);
                for ($i = $startAt; $i < count($placed); $i++) $placed[$i]['totalCols'] = $totalCols;
                $cluster = [];
            };
            foreach ($dayEvents as $event) {
                if ($clusterEndMinutes !== null && $event['startMinutes'] >= $clusterEndMinutes) {
                    $flush();
                    $clusterEndMinutes = null;
                }
                $cluster[] = $event;
                $clusterEndMinutes = $clusterEndMinutes === null ? $event['endMinutes'] : max($clusterEndMinutes, $event['endMinutes']);
            }
            $flush();

            $timedLayout[$iso] = $placed;
        }
    }

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

    <div class="bw-calendar-scroll" data-bw-calendar-scroll @if($height) style="height: {{ $height }}" @endif>
    @if($view === 'week')
        <div class="bw-calendar-week" data-bw-calendar-week aria-labelledby="{{ $titleId }}">
            <div class="bw-calendar-week-header-row" role="row">
                <div class="bw-calendar-week-gutter">
                    @if($showWeekNumbers)<span class="bw-calendar-week-gutter-number">W{{ $weeks[0][0]['weekNumber'] }}</span>@endif
                </div>
                @foreach($weeks[0] as $day)
                    <div role="gridcell"
                        data-bw-calendar-day
                        data-date="{{ $day['iso'] }}"
                        tabindex="{{ $day['iso'] === $focusIso ? '0' : '-1' }}"
                        @if($selectable !== 'none') aria-selected="{{ $day['isSelected'] ? 'true' : 'false' }}" @endif
                        @if($day['isToday']) aria-current="date" @endif
                        @if($day['isDisabled']) aria-disabled="true" @endif
                        aria-label="{{ $day['label'] }}"
                        @class([
                            'bw-calendar-week-day-header',
                            'bw-calendar-cell-today' => $day['isToday'],
                            'bw-calendar-cell-selected' => $day['isSelected'],
                            'bw-calendar-cell-disabled' => $day['isDisabled'],
                        ])>
                        <span class="bw-calendar-week-day-name" aria-hidden="true">{{ $day['dayName'] }}</span>
                        <span class="bw-calendar-cell-date">{{ $day['day'] }}</span>
                    </div>
                @endforeach
            </div>

            @if(count($allDayBanners))
                <div class="bw-calendar-week-allday-row" role="row" style="--bw-calendar-allday-rows: {{ $allDayRowCount }}">
                    <div class="bw-calendar-week-gutter bw-calendar-week-allday-label">All day</div>
                    <div class="bw-calendar-week-allday-track">
                        @foreach($allDayBanners as $banner)
                            @if($banner['href'])
                                <a href="{{ $banner['href'] }}" class="bw-calendar-event bw-calendar-week-allday-banner bw-calendar-event-{{ $banner['type'] }}"
                                   style="grid-column: {{ $banner['startIndex'] + 1 }} / span {{ $banner['span'] }}; grid-row: {{ $banner['row'] + 1 }}">{{ $banner['label'] }}</a>
                            @else
                                <span class="bw-calendar-event bw-calendar-week-allday-banner bw-calendar-event-{{ $banner['type'] }}"
                                   style="grid-column: {{ $banner['startIndex'] + 1 }} / span {{ $banner['span'] }}; grid-row: {{ $banner['row'] + 1 }}">{{ $banner['label'] }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bw-calendar-week-body" data-bw-calendar-week-body style="height: {{ $hoursInDay * $hourRowHeight }}rem">
                <div class="bw-calendar-week-hours" aria-hidden="true">
                    @for($h = 0; $h < $hoursInDay; $h++)
                        <div class="bw-calendar-week-hour-label" style="top: {{ $h * $hourRowHeight }}rem">{{ \Illuminate\Support\Carbon::createFromTime($h, 0)->format('g A') }}</div>
                    @endfor
                </div>
                <div class="bw-calendar-week-days">
                    @foreach($weeks[0] as $day)
                        @php $iso = $day['iso']; @endphp
                        <div class="bw-calendar-week-day-column @if($day['isToday']) bw-calendar-week-day-column-today @endif" data-date="{{ $iso }}">
                            @for($h = 1; $h < $hoursInDay; $h++)
                                <div class="bw-calendar-week-hour-line" style="top: {{ $h * $hourRowHeight }}rem" aria-hidden="true"></div>
                            @endfor
                            @foreach(($timedLayout[$iso] ?? []) as $event)
                                @php
                                    $top = ($event['startMinutes'] / 60) * $hourRowHeight;
                                    $height = max(1.25, (($event['endMinutes'] - $event['startMinutes']) / 60) * $hourRowHeight);
                                    $widthPct = 100 / $event['totalCols'];
                                    $leftPct = $widthPct * $event['col'];
                                @endphp
                                @if($event['href'])
                                    <a href="{{ $event['href'] }}" class="bw-calendar-event bw-calendar-week-timed-event bw-calendar-event-{{ $event['type'] }}"
                                       style="top: {{ $top }}rem; height: {{ $height }}rem; left: {{ $leftPct }}%; width: calc({{ $widthPct }}% - 2px)">
                                        <span class="bw-calendar-week-timed-event-time">{{ $event['start']->format('g:ia') }}</span>
                                        <span class="bw-calendar-week-timed-event-label">{{ $event['label'] }}</span>
                                    </a>
                                @else
                                    <span class="bw-calendar-event bw-calendar-week-timed-event bw-calendar-event-{{ $event['type'] }}"
                                       style="top: {{ $top }}rem; height: {{ $height }}rem; left: {{ $leftPct }}%; width: calc({{ $widthPct }}% - 2px)">
                                        <span class="bw-calendar-week-timed-event-time">{{ $event['start']->format('g:ia') }}</span>
                                        <span class="bw-calendar-week-timed-event-label">{{ $event['label'] }}</span>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
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
                                <div class="bw-calendar-cell-inner">
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
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    </div>

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
