<?php

return [
    'password_meter' => [
        'show_label' => true,
        // score thresholds a password's length must clear to earn a length point.
        // two lengths are checked: crossing the second earns a second point.
        'min_length' => 8,
        'strong_length' => 12,
    ],
];
