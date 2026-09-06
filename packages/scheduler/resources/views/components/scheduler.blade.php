{{-- format-ignore-start --}}
@props([
    // day or week
    'view' => config('bladewind.scheduler.view', 'day'),
    // anchor date (Y-m-d). day view shows just that day, week view shows the
    // week containing it. defaults to today
    'date' => '',
    // day view's columns: [['id' => 'r1', 'label' => 'Room A'], ...].
    // ignored in week view, which always uses the 7 days of the week as columns
    'resources' => [],
    // ['id', 'label', 'start' => 'Y-m-d H:i', 'end' => 'Y-m-d H:i', 'color' => any bladewind colour,
    //  'resource_id' => matches a resources[]['id'] (day view), 'href']
    // an event outside the visible hour range is clipped to it, not hidden
    'events' => [],
    'startHour' => config('bladewind.scheduler.start_hour', 8),
    'endHour' => config('bladewind.scheduler.end_hour', 18),
    // grid line granularity in minutes: 60, 30, or 15
    'slotMinutes' => config('bladewind.scheduler.slot_minutes', 60),
    // 0 = Sunday, 1 = Monday. used by week view
    'weekStarts' => config('bladewind.scheduler.week_starts', 1),
    // purely a display label — pass events already converted to the
    // viewer's timezone; this component does not convert times itself
    'timezone' => '',
    // javascript function called as (columnId, "Y-m-d H:i") when an empty
    // slot is clicked, columnId being a resource id (day view) or a date (week view)
    'onSlotClick' => null,
    // javascript function called as (eventId) when an event is clicked
    'onEventClick' => null,
    'class' => '',
    'id' => uniqid('bw-scheduler-'),
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $view = in_array($view, ['day', 'week']) ? $view : 'day';
    $slotMinutes = in_array((int) $slotMinutes, [15, 30, 60], true) ? (int) $slotMinutes : 60;
    $startHour = max(0, min(23, (int) $startHour));
    $endHour = max($startHour + 1, min(24, (int) $endHour));
    $anchor = $date !== '' ? \Illuminate\Support\Carbon::parse($date) : \Illuminate\Support\Carbon::today();
    $today = \Illuminate\Support\Carbon::today();

    $slotHeightPx = 24;
    $slotsPerHour = 60 / $slotMinutes;
    $totalMinutes = ($endHour - $startHour) * 60;
    $totalSlots = (int) ($totalMinutes / $slotMinutes);
    $bodyHeightPx = $totalSlots * $slotHeightPx;
    $hourHeightPx = $slotsPerHour * $slotHeightPx;

    // build the columns: resources for day view, the week's dates for week view
    if ($view === 'day') {
        $columns = array_map(fn ($r) => [
            'id' => (string) ($r['id'] ?? $r['label']),
            'label' => $r['label'] ?? (string) ($r['id'] ?? ''),
            'sublabel' => null,
        ], $resources);
        if (! count($columns)) {
            $columns = [['id' => '_default', 'label' => $anchor->translatedFormat('l, F j'), 'sublabel' => null]];
        }
    } else {
        $weekStart = $anchor->copy()->startOfWeek($weekStarts === 1 ? \Illuminate\Support\Carbon::MONDAY : \Illuminate\Support\Carbon::SUNDAY);
        $columns = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $columns[] = [
                'id' => $day->toDateString(),
                'label' => $day->translatedFormat('D'),
                'sublabel' => $day->format('j'),
                'isToday' => $day->isSameDay($today),
            ];
        }
    }

    // group events by the column they belong to, converting each to
    // minutes-from-midnight clamped to the visible hour range
    $eventsByColumn = [];
    foreach ($events as $index => $event) {
        $start = \Illuminate\Support\Carbon::parse($event['start'] ?? null);
        $end = isset($event['end']) ? \Illuminate\Support\Carbon::parse($event['end']) : $start->copy()->addHour();

        $columnId = $view === 'day'
            ? (string) ($event['resource_id'] ?? '_default')
            : $start->toDateString();

        $startMinutes = max($startHour * 60, min($endHour * 60, $start->hour * 60 + $start->minute));
        $endMinutes = max($startHour * 60, min($endHour * 60, $end->hour * 60 + $end->minute));
        if ($endMinutes <= $startMinutes) {
            $endMinutes = min($endHour * 60, $startMinutes + 15);
        }

        $eventsByColumn[$columnId][] = [
            'index' => $index,
            'id' => $event['id'] ?? $index,
            'label' => $event['label'] ?? '',
            'color' => $event['color'] ?? 'primary',
            'href' => $event['href'] ?? null,
            'startLabel' => $start->format('g:i A'),
            'endLabel' => $end->format('g:i A'),
            'startMinutes' => $startMinutes,
            'endMinutes' => $endMinutes,
        ];
    }

    $packedByColumn = [];
    foreach ($eventsByColumn as $columnId => $columnEvents) {
        $packedByColumn[$columnId] = \Mkocansey\Bladewind\Scheduler\SchedulerEventPacker::pack($columnEvents);
    }

    $colourClasses = fn (string $colour) => "bg-$colour-100 dark:bg-$colour-500/20 border-$colour-300 dark:border-$colour-500/40 text-$colour-800 dark:text-$colour-200";
@endphp
{{-- format-ignore-end --}}

<div data-bw-scheduler="{{ $id }}" @class(['bw-scheduler rounded-lg border border-gray-200 dark:border-dark-700 overflow-hidden', $class])>
    @if($timezone !== '')
        <div class="px-3 py-1.5 text-xs text-gray-400 dark:text-dark-500 border-b border-gray-200 dark:border-dark-700">
            Times shown in {{ $timezone }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <div class="grid" style="grid-template-columns: 4rem repeat({{ count($columns) }}, minmax(9rem, 1fr));">
            <div class="border-b border-r border-gray-200 dark:border-dark-700"></div>
            @foreach($columns as $column)
                <div @class([
                        'px-2 py-2 text-center text-xs font-medium border-b border-gray-200 dark:border-dark-700',
                        'text-gray-600 dark:text-dark-300' => empty($column['isToday']),
                        'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-500/10' => ! empty($column['isToday']),
                     ])>
                    <div>{{ $column['label'] }}</div>
                    @if($column['sublabel'])
                        <div class="text-[11px] text-gray-400 dark:text-dark-500">{{ $column['sublabel'] }}</div>
                    @endif
                </div>
            @endforeach

            <div class="border-r border-gray-200 dark:border-dark-700" style="height: {{ $bodyHeightPx }}px;">
                @for($hour = $startHour; $hour < $endHour; $hour++)
                    <div class="relative text-right pr-2 text-[11px] text-gray-400 dark:text-dark-500" style="height: {{ $hourHeightPx }}px;">
                        <span class="absolute -top-2 right-2">{{ \Illuminate\Support\Carbon::createFromTime($hour)->format('g A') }}</span>
                    </div>
                @endfor
            </div>

            @foreach($columns as $column)
                <div data-column data-column-id="{{ $column['id'] }}"
                     class="relative border-r border-gray-100 dark:border-dark-800 last:border-r-0"
                     style="height: {{ $bodyHeightPx }}px; background-image: repeating-linear-gradient(to bottom, transparent, transparent {{ $slotHeightPx - 1 }}px, rgb(243 244 246) {{ $slotHeightPx }}px); background-size: 100% {{ $hourHeightPx }}px;">
                    @foreach($packedByColumn[$column['id']] ?? [] as $event)
                        @php
                            $topPx = (($event['startMinutes'] - $startHour * 60) / $totalMinutes) * $bodyHeightPx;
                            $heightPx = max(18, (($event['endMinutes'] - $event['startMinutes']) / $totalMinutes) * $bodyHeightPx);
                            $widthPercent = 100 / $event['totalCols'];
                            $leftPercent = $event['col'] * $widthPercent;
                        @endphp
                        <{{ $event['href'] ? 'a' : 'div' }}
                            @if($event['href']) href="{{ $event['href'] }}" @endif
                            data-event data-event-id="{{ $event['id'] }}"
                            @class([
                                'absolute rounded-md border px-1.5 py-0.5 text-[11px] leading-tight overflow-hidden cursor-pointer hover:brightness-95',
                                $colourClasses($event['color']),
                            ])
                            style="top: {{ $topPx }}px; height: {{ $heightPx }}px; left: calc({{ $leftPercent }}% + 1px); width: calc({{ $widthPercent }}% - 2px);">
                            <div class="font-medium truncate">{{ $event['label'] }}</div>
                            <div class="truncate opacity-80">{{ $event['startLabel'] }} – {{ $event['endLabel'] }}</div>
                        </{{ $event['href'] ? 'a' : 'div' }}>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>

@if($onSlotClick || $onEventClick)
    @once
        <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/scheduler.js') }}"></x-bladewind::script>
    @endonce
    <x-bladewind::script :nonce="$nonce">
        (() => {
            const root = document.querySelector('[data-bw-scheduler="{{ $id }}"]');
            if (root && root.dataset.bwInitialised === 'true') return;
            if (root) root.dataset.bwInitialised = 'true';

            new BladewindScheduler('{{ $id }}', {
                startHour: {{ $startHour }},
                endHour: {{ $endHour }},
                slotMinutes: {{ $slotMinutes }},
                bodyHeightPx: {{ $bodyHeightPx }},
                onSlotClick: @if($onSlotClick) {{ $onSlotClick }} @else null @endif,
                onEventClick: @if($onEventClick) {{ $onEventClick }} @else null @endif,
            });
        })();
    </x-bladewind::script>
@endif
