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
        'small' => 'rounded-lg',
        'medium' => 'rounded-xl',
        'large' => 'rounded-2xl',
        'xl' => 'rounded-3xl',
    ];
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