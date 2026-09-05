<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chat component
    |--------------------------------------------------------------------------
    */
    'chat' => [
        // any valid CSS height/max-height value, e.g. "400px". null means
        // the thread grows with its content instead of scrolling internally
        'height' => null,
    ],

    'chat_message' => [
        'outgoing' => false,
        // hides the avatar and sender name, and tightens spacing, for a
        // message that follows another from the same sender
        'grouped' => false,
        'show_avatar' => true,
    ],
];
