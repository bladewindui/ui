<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Scheduler component
    |--------------------------------------------------------------------------
    */
    'scheduler' => [
        'view' => 'day',
        'start_hour' => 8,
        'end_hour' => 18,
        // grid line granularity in minutes. 60, 30, or 15
        'slot_minutes' => 30,
        // 0 = Sunday, 1 = Monday, used by week view
        'week_starts' => 1,
    ],
];
