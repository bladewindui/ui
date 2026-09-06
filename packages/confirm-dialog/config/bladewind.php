<?php

return [
    'confirm_dialog' => [
        // danger | warning | info | primary
        'tone' => 'danger',
        // small | medium | large
        'size' => 'small',
        // a confirm dialog gates a deliberate choice, so the backdrop does not
        // dismiss it by default — unlike the generic Modal
        'backdrop_can_close' => false,
        // close the dialog once the confirm action's promise resolves
        'close_after_confirm' => true,
    ],
];
