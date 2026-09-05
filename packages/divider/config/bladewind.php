<?php

return [
    'divider' => [
        // horizontal | vertical
        'orientation' => 'horizontal',
        // none | small | medium | large
        'spacing' => 'medium',
        // true: aria-hidden, purely visual. false: role="separator" for a
        // divider that carries real document structure (e.g. between form
        // sections a screen reader should announce).
        'decorative' => true,
        // any accepted BladewindUI colour, or null for the neutral slate default
        'color' => null,
    ],
];
