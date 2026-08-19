<?php

const DEFAULT_BLADEWIND_COLOUR = 'primary';
const ACCEPTED_BLADEWIND_COLOURS = [
    'primary',
    'blue',
    'red',
    'yellow',
    'green',
    'orange',
    'purple',
    'cyan',
    'pink',
    'gray',
    'black',
    'violet',
    'indigo',
    'fuchsia'
];
const ACCEPTED_BLADEWIND_ASPECT_RATIOS = [
    'null',
    'NaN',
    '16:9',
    '4:3',
    '2:3',
    '1:1',
];
const ACCEPTED_BLADEWIND_SIZES = [
    'tiny',
    'small',
    'medium',
    'big',
    'large',
    'xl',
    'omg',
];

function isValidBladewindColour($colour): bool
{
    return in_array($colour, ACCEPTED_BLADEWIND_COLOURS);
}

function isValidAspectRatio($ratio): bool
{
    return in_array($ratio, ACCEPTED_BLADEWIND_ASPECT_RATIOS);
}

function defaultBladewindColour($colour, $default = DEFAULT_BLADEWIND_COLOUR): string
{
    if (!isValidBladewindColour($colour)) {
        return $default;
    }
    return $colour;
}

function parseBladewindVariable($variable, $parse_as = 'bool')
{
    switch ($parse_as) {
        case 'str':
        case 'string':
            // FILTER_SANITIZE_STRING was deprecated in PHP 8.1 and is going away.
            // It stripped tags and encoded quotes; the quote half is redundant here
            // because every caller passes the result through Blade, which escapes on
            // output. strip_tags() keeps the half that mattered.
            return strip_tags((string) $variable);
        case 'int':
            return filter_var($variable, FILTER_VALIDATE_INT);
        case 'bool':
        case 'boolean':
            return filter_var($variable, FILTER_VALIDATE_BOOLEAN);
        default:
            return $variable;
    }
}

function defaultBladewindName($prefix = 'blwd_'): string
{
    return parseBladewindName(uniqid($prefix));
}

function parseBladewindName($name): string
{
    return preg_replace('/[\s-]/', '_', $name);
}

function formatJsonForChart($json): string
{
    $output = json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return preg_replace_callback('/"JS::(.*?)"/s', fn($m) => $m[1], $output);
}

function paginationRow($row_number, $pageSize = 25, $defaultPage = 1): string
{
    $row_id = uniqid();
    $row_page = ($row_number < $pageSize) ? 1 : ceil($row_number / $pageSize);
    return sprintf("data-id=%s data-page=%s class=%s", $row_id, $row_page, ($row_page != $defaultPage ? 'hidden' : ''));
}

function pagination_row($row_number, $pageSize = 25, $defaultPage = 1): string
{
    return paginationRow($row_number, $pageSize, $defaultPage);
}

function getRadiusString($radius, $prefix = null): string
{
    $roundness = [
        'none' => 'rounded-none',
        'tiny' => 'rounded-sm',
        'small' => 'rounded-lg',
        'medium' => 'rounded-xl',
        'large' => 'rounded-2xl',
        'xl' => 'rounded-3xl',
        'omg' => 'rounded-4xl',
        'full' => 'rounded-full',
    ];

    // anything that already looks like a tailwind radius utility passes through,
    // so radius="rounded-l-none" needs no new named entry. see #590
    if (is_string($radius) && str_starts_with($radius, 'rounded')) {
        return $radius;
    }
    // an unrecognised radius yields no rounding class rather than a fatal. the
    // `?? ''` this replaces could never run: $roundness[$radius] was evaluated
    // first, so a typo in a radius prop raised "Undefined array key" and took the
    // whole page down through a ViewException. see #603.
    $rounded = $roundness[$radius] ?? '';

    if ($rounded === '' || empty($prefix)) {
        return $rounded;
    }

    return str_replace('rounded-', "rounded-$prefix-", $rounded);
}

/**
 * Translate a field name into the dot key old() and the error bag use.
 *
 * items[0][qty] -> items.0.qty
 */
function bladewindFieldKey($name): string
{
    return trim(str_replace(['][', '[', ']'], ['.', '.', ''], (string) $name), '.');
}

/**
 * The value a field should render with, preferring flashed old input.
 *
 * Off unless the caller opts in, because repopulating a field that a consumer is
 * already populating by hand would change what their existing markup renders.
 */
function bladewindOldInput($name, $default = '', bool $enabled = true)
{
    if (! $enabled || (string) $name === '') {
        return $default;
    }

    try {
        return old(bladewindFieldKey($name), $default);
    } catch (\Throwable) {
        // no request or session bound — rendering outside an HTTP context
        return $default;
    }
}

/**
 * Was there any flashed old input at all — i.e. did a submission bounce back?
 *
 * Checkboxes need this. An unticked box submits nothing, so "this field is absent
 * from old input" only means unchecked if there *was* a submission. Without the
 * distinction, a first render would silently clear a box the consumer set with
 * checked="true".
 */
function bladewindHasOldInput(bool $enabled = true): bool
{
    if (! $enabled) {
        return false;
    }

    try {
        return ! empty(session()->getOldInput());
    } catch (\Throwable) {
        return false;
    }
}

/**
 * The first validation error for a field, or an empty string.
 *
 * Reads the shared $errors bag first, which is what Blade itself sees, and falls
 * back to the session for contexts where the view has not been given one.
 */
function bladewindValidationError($name, bool $enabled = true, ?string $bag = null): string
{
    if (! $enabled || (string) $name === '') {
        return '';
    }

    $errors = null;

    try {
        $errors = view()->shared('errors') ?: session('errors');
    } catch (\Throwable) {
        return '';
    }

    if (! $errors instanceof \Illuminate\Support\ViewErrorBag) {
        return '';
    }

    $key = bladewindFieldKey($name);
    $bag = $errors->getBag($bag ?: 'default');

    return $bag->has($key) ? (string) $bag->first($key) : '';
}
