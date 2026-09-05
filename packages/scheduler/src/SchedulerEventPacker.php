<?php

namespace Mkocansey\Bladewind\Scheduler;

/**
 * Side-by-side column layout for a set of possibly-overlapping timed events,
 * so a run of mutually overlapping events gets one column each instead of
 * stacking on top of one another. Events that overlap nothing take the full
 * column width. Mirrors the same greedy algorithm the Calendar component
 * uses for its week view, applied here per scheduler column.
 */
class SchedulerEventPacker
{
    /**
     * @param  list<array{startMinutes: int, endMinutes: int}>  $events
     * @return list<array{startMinutes: int, endMinutes: int, col: int, totalCols: int}>
     */
    public static function pack(array $events): array
    {
        usort($events, fn ($a, $b) => $a['startMinutes'] <=> $b['startMinutes']);

        $placed = [];
        $cluster = [];
        $clusterEnd = null;

        $flush = function () use (&$cluster, &$placed) {
            if (! count($cluster)) {
                return;
            }

            $columnEnds = [];
            $startAt = count($placed);

            foreach ($cluster as $item) {
                $placedCol = null;

                foreach ($columnEnds as $colIndex => $colEnd) {
                    if ($item['startMinutes'] >= $colEnd) {
                        $placedCol = $colIndex;
                        break;
                    }
                }

                $placedCol ??= count($columnEnds);
                $columnEnds[$placedCol] = $item['endMinutes'];
                $item['col'] = $placedCol;
                $placed[] = $item;
            }

            $totalCols = count($columnEnds);
            for ($i = $startAt, $n = count($placed); $i < $n; $i++) {
                $placed[$i]['totalCols'] = $totalCols;
            }

            $cluster = [];
        };

        foreach ($events as $event) {
            if ($clusterEnd !== null && $event['startMinutes'] >= $clusterEnd) {
                $flush();
                $clusterEnd = null;
            }

            $cluster[] = $event;
            $clusterEnd = $clusterEnd === null ? $event['endMinutes'] : max($clusterEnd, $event['endMinutes']);
        }

        $flush();

        return $placed;
    }
}
