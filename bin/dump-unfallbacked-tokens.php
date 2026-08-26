#!/usr/bin/env php
<?php

/**
 * Regenerate tests/fixtures/css/unfallbacked-tokens.txt from the compiled bundle.
 *
 * The baseline is the list of custom properties that .bw-* rules reference through
 * var() with no fallback value — the shape that resolves to nothing when a host app
 * on Tailwind v4 does not define the token. CompiledCssTest asserts the live list
 * never grows beyond this baseline.
 *
 * Run `npm run build` first, then this, and commit the diff alongside the CSS change
 * that caused it. A shrinking list is the point (improvements.md item 1 / #589); a
 * growing one needs a reason.
 */

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../tests/Support/CompiledStylesheet.php';

use Mkocansey\Bladewind\Tests\Support\CompiledStylesheet;

$baseline = __DIR__.'/../tests/fixtures/css/unfallbacked-tokens.txt';
$tokens = array_keys((new CompiledStylesheet())->unfallbackedTokensInComponentRules());

$before = is_file($baseline)
    ? count(array_filter(
        file($baseline, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
        fn (string $line): bool => ! str_starts_with($line, '#')
    ))
    : 0;

file_put_contents($baseline, implode("\n", [
    '# Custom properties that .bw-* rules reference through var() with no fallback.',
    '# Generated from packages/core/public/css/bladewind-ui.min.css.',
    '# See improvements.md item 1 / issue #589 — this list should only ever shrink.',
    '# Regenerate with: php bin/dump-unfallbacked-tokens.php',
    ...$tokens,
])."\n");

printf("%d tokens (was %d)%s\n", count($tokens), $before, count($tokens) < $before ? ' — shrunk' : '');
