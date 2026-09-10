<?php

return [
    'context_menu' => [
        'padded' => true,
        // block the browser's native context menu on the wrapped region even
        // when no menu item exists (e.g. the region is empty). true keeps the
        // behaviour consistent regardless of content.
        'disable_native' => true,
        'item' => [
            'dir' => '',
            'padded' => true,
        ],
    ],
];
