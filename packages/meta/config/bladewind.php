<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default attribute definitions
    |--------------------------------------------------------------------------
    |
    | BladewindUO makes common UI assumptions which may bot be in line with
    | what you need in your project. To prevent you from defining several
    | attributes just to get your components to conform to your design, you
    | can define the default attributes here, and they will be applied to all
    | your BladewindUI components. https://bladewindui.com/customize#defaults
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Laravel form-state integration
    |--------------------------------------------------------------------------
    |
    | Shared by input, textarea, select, checkbox, radio, datepicker and
    | filepicker. Both switches default to false so that upgrading changes
    | nothing about what your existing markup renders — an app already printing
    | its own validation messages would otherwise print every one of them twice.
    |
    | Turn them on here once and every form component starts speaking Laravel,
    | or set the matching prop on a single field to override this.
    |
    | fill_from_old         repopulate a field from old() after a validation
    |                       redirect. filepicker is exempt: a file input cannot
    |                       be repopulated
    | show_validation_error give the field its error state and render
    |                       $errors->first() beneath it
    | error_bag             which bag to read; null uses Laravel's default
    |
    */

    'forms' => [
        'fill_from_old' => false,
        'show_validation_error' => false,
        'error_bag' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert component
    |--------------------------------------------------------------------------
    */
    'alert' => [
        'shade' => 'faint',
        'show_icon' => true,
        'color' => null,
        'size' => 'tiny',
        'show_ring' => false
    ],

    /*
    |--------------------------------------------------------------------------
    | Avatar component
    |--------------------------------------------------------------------------
    */
    'avatars' => [
        'size' => 'regular',
        'show_ring' => true,
        'dot_color' => 'primary',
        'bg_color' => null,
        'dot_position' => 'bottom',
        'dotted' => false,
        'stacked' => false,
    ],

    'avatar' => [
        'size' => 'regular',
        'dot_color' => 'primary',
        'dot_position' => 'bottom',
        'dotted' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Bell component
    |--------------------------------------------------------------------------
    */
    'bell' => [
        'show_dot' => true,
        'animate_dot' => false,
        'size' => 'small',
        'color' => 'primary',
    ],

    /*
    |--------------------------------------------------------------------------
    | Button component
    |--------------------------------------------------------------------------
    */
    'button' => [
        'size' => 'regular',
        'radius' => 'small',
        'show_focus_ring' => true,
        'tag' => 'button',
        'icon_right' => false,
        'outline' => false,
        'border_width' => 2,
        'ring_width' => '',
        'uppercasing' => true,
        // define default attributes for all circular buttons
        'circle' => [
            'size' => 'regular',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Card component
    |--------------------------------------------------------------------------
    */
    'card' => [
        'compact' => false,
        'no_padding' => false,
        'has_shadow' => true,
        'has_border' => true,
        'has_hover' => false,
        'radius' => 'small',
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Card component
    |--------------------------------------------------------------------------
    */
    'contact_card' => [
        'has_hover' => false,
        'has_shadow' => true,
        'has_border' => true,
        'no_padding' => false,
        'compact' => true,
        'lazy' => true,
        'centered' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Centered Content component
    |--------------------------------------------------------------------------
    */
    'centered_content' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Chart component
    |--------------------------------------------------------------------------
    */
    'chart' => [
        'show_axis_lines' => true,
        'show_x_axis_lines' => true,
        'show_y_axis_lines' => true,
        'show_axis_labels' => true,
        'show_x_axis_labels' => true,
        'show_y_axis_labels' => true,
        'show_borders' => true,
        'show_x_border' => true,
        'show_y_border' => true,
        'show_legend' => true,
        'show_line' => false,
        'legend_position' => 'top',
        'legend_alignment' => 'center',
    ],

    /*
    |--------------------------------------------------------------------------
    | Checkbox component
    |--------------------------------------------------------------------------
    */
    'checkbox' => [
        'add_clearing' => true,
        'color' => 'primary',
    ],

    /*
    |--------------------------------------------------------------------------
    | Checkcards component
    |--------------------------------------------------------------------------
    */
    'checkcards' => [
        'compact' => true,
        'show_error' => false,
        'auto_select_new' => true,
        'color' => 'primary',
        'radius' => 'medium',
        'avatar_size' => 'medium',
        'border_width' => 2,
        'border_color' => 'gray',
        'align_items' => 'top',
        'error_heading' => 'Max selection',
        'error_message' => 'You have selected the maximum cards allowed',
    ],

    /*
    |--------------------------------------------------------------------------
    | Colorpicker component
    |--------------------------------------------------------------------------
    */
    'colorpicker' => [
        'size' => 'regular',
        'show_value' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Datepicker component
    |--------------------------------------------------------------------------
    */
    'datepicker' => [
        'format' => 'yyyy-mm-dd',
        'week_starts' => 'sun',
        'validate' => false,
        'show_error_inline' => false,
        'stacked' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dropdown component
    |--------------------------------------------------------------------------
    */
    'dropdown' => [
        'append_value_to_url' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dropmenu component
    |--------------------------------------------------------------------------
    */
    'dropmenu' => [
        'trigger' => 'ellipsis-horizontal-icon',
        'trigger_on' => 'click',
        'icon_right' => false,
        'divided' => false,
        'padded' => true,
        // default attributes for dropmenu-item component
        'item' => [
            'dir' => '',
            'icon_right' => false,
            'hover' => true,
            'padded' => true,
            'divided' => false,
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | EmptyState component
    |--------------------------------------------------------------------------
    */
    'empty_state' => [
        // the public directory is the starting point
        // the default below is public/vendor/bladewind/images...
        'image' => '/vendor/bladewind/images/empty-state.svg',
        'show_image' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Filepicker component
    |--------------------------------------------------------------------------
    */
    'filepicker' => [
        'accepted_file_types' => 'image/*,audio/*,video/*,application/pdf',
        'placeholder_line1' => 'Choose files or drag and drop to upload',
        'placeholder_line2' => '%s up to %s',
        'can_browse' => true,
        'can_drop' => true,
        'validate_file_size' => true,
        'base64' => false,
        'base64_output' => 'url',
        'show_credits' => false,
        'auto_upload' => false,
        'max_files' => 1,
        'max_file_size' => '5mb',
        'max_total_file_size' => null,
        'add_new_files_to' => 'top',
        'max_file_size_exceeded_label' => 'File is too large',
        'max_file_size_label' => 'Maximum file size is {filesize}',
        'max_total_file_size_exceeded_label' => 'Maximum total file size exceeded',
        'max_total_file_size_label' => 'Maximum total file size is {filesize}',
        'invalid_file_type_label' => 'Wrong file type uploaded',
        'expected_file_types_label' => 'Only {allButLastType} and {lastType} files allowed',
        'show_image_preview' => true,
        'can_resize_image' => false,
        'image_resize_width' => null,
        'image_resize_height' => null,
        'can_crop' => false,
        'crop_aspect_ratio' => '16:9',
    ],

    /*
    |--------------------------------------------------------------------------
    | Horizontal Line Graph component
    |--------------------------------------------------------------------------
    */
    'horizontal_line_graph' => [
        'shade' => 'faint',
        'color' => 'primary',
        'percentage_label_opacity' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Icon component
    |--------------------------------------------------------------------------
    */
    'icon' => [
        'type' => 'outline',
        'dir' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Input component
    |--------------------------------------------------------------------------
    */
    'input' => [
        'add_clearing' => true,
        'show_error_inline' => false,
        'show_placeholder_always' => false,
        'error_heading' => 'Error',
        'transparent_prefix' => true,
        'transparent_suffix' => true,
        'clearable' => false,
        'size' => 'medium',
    ],

    /*
    |--------------------------------------------------------------------------
    | List View component
    |--------------------------------------------------------------------------
    */
    'list_view' => [
        'compact' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Modal component
    |--------------------------------------------------------------------------
    */
    // __('bladewind::bladewind.cancel', 'cancel') fails because the translation file is not loaded yet
    // throws BindingResolutionException: Target class [translator] does not exist.
    'modal' => [
        'align_buttons' => 'right',
        'ok_button_label' => 'okay',
        'cancel_button_label' => 'cancel',
        'close_after_action' => true,
        'backdrop_can_close' => true,
        'blur_backdrop' => true,
        'blur_size' => 'medium',
        'center_action_buttons' => false,
        'stretch_action_buttons' => false,
        'show_close_icon' => false,
        'size' => 'medium',
        'radius' => 'small',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification component
    |--------------------------------------------------------------------------
    */
    'notification' => [
        'position' => 'top-right',
    ],

    /*
    |--------------------------------------------------------------------------
    | Number component
    |--------------------------------------------------------------------------
    */
    'number' => [
        'with_dots' => true,
        'transparent_icons' => true,
        'size' => 'medium',
        'icon_type' => 'outline',
    ],

    /*
    |--------------------------------------------------------------------------
    | Popover component
    |--------------------------------------------------------------------------
    */
    'popover' => [
        'trigger' => 'information-circle-icon',
        'trigger_on' => 'click',
        'position' => 'bottom',
        'width' => 280,
    ],

    /*
    |--------------------------------------------------------------------------
    | Progress bar component
    |--------------------------------------------------------------------------
    */
    'progress_bar' => [
        'show_percentage_label' => false,
        'show_percentage_label_inline' => true,
        'shade' => 'faint',
        'percentage_label_opacity' => '100',
    ],

    /*
    |--------------------------------------------------------------------------
    | Progress Circle component
    |--------------------------------------------------------------------------
    */
    'progress_circle' => [
        'animate' => true,
        'show_label' => false,
        'show_percent' => false,
        'shade' => 'faint',
        'size' => 'medium',
    ],

    /*
    |--------------------------------------------------------------------------
    | Radio Button component
    |--------------------------------------------------------------------------
    */
    'radio_button' => [
        'add_clearing' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rating component
    |--------------------------------------------------------------------------
    */
    'rating' => [
        'type' => 'star',
        'clickable' => true,
        'size' => 'small',
    ],

    /*
    |--------------------------------------------------------------------------
    | Select component
    |--------------------------------------------------------------------------
    */
    'select' => [
        'placeholder' => 'Select One',
        'search_placeholder' => 'Type here...',
        'empty_placeholder' => 'No options available',
        'label' => null,
        'add_clearing' => true,
        'max_error_message' => 'Please select only %s items',
        'modular' => false,
        'size' => 'medium',
    ],

    /*
    |--------------------------------------------------------------------------
    | Slider component
    |--------------------------------------------------------------------------
    */
    'slider' => [
        'show_values' => true,
        'range' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Spinner component
    |--------------------------------------------------------------------------
    */
    'spinner' => [
        'color' => 'gray',
        'size' => 'small',
    ],

    /*
    |--------------------------------------------------------------------------
    | Statistic component
    |--------------------------------------------------------------------------
    */
    'statistic' => [
        'currency' => '',
        'label_position' => 'top',
        'currency_position' => 'left',
        'icon_position' => 'left',
        'has_shadow' => true,
        'has_border' => true,
        // named tone for the note and the trend arrow. the colour map is owned
        // by the library. neutral, positive, negative, warning, info
        'tone' => 'neutral',
        // for metrics where down is good — arrears, churn, cost per unit
        'invert_direction' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Script component
    |--------------------------------------------------------------------------
    */
    'script' => [
        'nonce' => null,
        'defer' => false,
        'async' => false,
        'modular' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tab component
    |--------------------------------------------------------------------------
    */
    'tab' => [
        'group' => [
            'style' => 'simple',
            'color' => 'primary',
        ],
        'body' => [
            'class' => '',
        ],
        'content' => [
            'class' => '',
        ],
        'heading' => [
            'icon_type' => 'outline',
            'icon_dir' => '', // starts from your-project/public
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Table component
    |--------------------------------------------------------------------------
    */
    'table' => [
        'striped' => false,
        'has_shadow' => false,
        'has_border' => false,
        'divided' => true,
        'divider' => 'regular',
        'has_hover' => true,
        'compact' => false,
        'uppercasing' => true,
        'celled' => false,
        'searchable' => false,
        'selectable' => false,
        'checkable' => false,
        'transparent' => false,
        'search_placeholder' => 'Search table below...',
        'no_data_message' => 'No records to display',
        'message_as_empty_state' => false,
        'show_image' => true,
        'sortable' => false,
        'paginated' => false,
        'pagination_style' => 'arrows',
        'page_size' => 25,
        'show_row_numbers' => false,
        'show_page_number' => false,
        'show_total_pages' => false,
        'show_total' => true,
        'total_label' => 'Showing :a to :b of :c records',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tags component
    |--------------------------------------------------------------------------
    */
    'tags' => [
        'color' => 'primary',
        'shade' => 'faint',
        'rounded' => false,
        'uppercasing' => true,
        'tiny' => false,
        'outline' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tag component
    |--------------------------------------------------------------------------
    */
    'tag' => [
        'rounded' => false,
        'uppercasing' => true,
        'shade' => 'faint',
        'color' => 'primary',
        'outline' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Textarea component
    |--------------------------------------------------------------------------
    */
    'textarea' => [
        'add_clearing' => true,
        'rows' => 3,
        'error_heading' => 'Error',
        'show_error_inline' => false,
        'toolbar' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeline component
    |--------------------------------------------------------------------------
    */
    'timeline' => [
        'stacked' => false,
        // defaults for timeline-group
        'group' => [
            'stacked' => false,
            'anchor' => 'small',
            'color' => 'gray',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Timepicker component
    |--------------------------------------------------------------------------
    */
    'timepicker' => [
        'hour_label' => 'HH',
        'minute_label' => 'MM',
        'format_label' => '--',
        'format' => '12',
        'style' => 'popup',
        'placeholder' => 'HH:MM',
    ],

    /*
    |--------------------------------------------------------------------------
    | Toggle component
    |--------------------------------------------------------------------------
    */
    'toggle' => [
        'label_position' => 'left',
        'justified' => false,
        'bar' => 'thick',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tooltip component
    |--------------------------------------------------------------------------
    */
    'tooltip' => [
        'position' => 'top',
        'color' => 'dark',
        'size' => 'small',
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification Code component
    |--------------------------------------------------------------------------
    */
    'code' => [
        'total_digits' => 4,
        'size' => 'regular',
        'mask' => false,
    ],

];
