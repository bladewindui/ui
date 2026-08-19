<?php

return [
    /*
     | Laravel form-state integration, shared by every form component.
     |
     | Both default to false so that upgrading changes nothing about what existing
     | markup renders. Turn them on once here and every input, textarea, select,
     | checkbox, radio, datepicker and filepicker starts speaking Laravel; or set
     | the matching prop per field.
     |
     | fill_from_old            repopulate a field from old() when a validation
     |                          redirect brings the user back to the form
     | show_validation_error    give the field its error state and render
     |                          $errors->first() underneath it
     */
    'forms' => [
        'fill_from_old' => false,
        'show_validation_error' => false,
        // null uses Laravel's default error bag
        'error_bag' => null,
    ],
];
