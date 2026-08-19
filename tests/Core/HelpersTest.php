<?php

namespace Mkocansey\Bladewind\Tests\Core;

use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * BladewindHelpers.php is autoloaded via composer's `files` entry and is the
 * shared spine of almost every component — every boolean-ish prop in the library
 * goes through parseBladewindVariable(), and items 1-3 all move code that calls it.
 */
class HelpersTest extends TestCase
{
    #[Test]
    #[DataProvider('booleanProvider')]
    public function parse_bladewind_variable_coerces_boolean_ish_props(mixed $input, bool $expected): void
    {
        $this->assertSame($expected, parseBladewindVariable($input));
    }

    public static function booleanProvider(): array
    {
        return [
            'true string' => ['true', true],
            'false string' => ['false', false],
            'one string' => ['1', true],
            'zero string' => ['0', false],
            'yes' => ['yes', true],
            'no' => ['no', false],
            'on' => ['on', true],
            'off' => ['off', false],
            'real true' => [true, true],
            'real false' => [false, false],
            'empty string' => ['', false],
            'null' => [null, false],
            'unrecognised string' => ['maybe', false],
        ];
    }

    #[Test]
    public function parse_bladewind_variable_casts_integers(): void
    {
        $this->assertSame(25, parseBladewindVariable('25', 'int'));
        $this->assertSame(0, parseBladewindVariable('0', 'int'));
        $this->assertFalse(parseBladewindVariable('abc', 'int'));
    }

    /**
     * BUG, pinned deliberately — do not "fix" this test, fix the helper.
     *
     * The 'string' branch uses FILTER_SANITIZE_STRING, deprecated since PHP 8.1
     * and slated for removal. Every render that passes parse_as='string' — table's
     * searchField does on every table — emits a deprecation notice, which is noise
     * in a consumer's logs today and a fatal on the PHP that drops the constant.
     */
    #[Test]
    public function parse_bladewind_variable_string_mode_currently_uses_a_deprecated_filter(): void
    {
        $deprecations = [];
        set_error_handler(
            function (int $severity, string $message) use (&$deprecations): bool {
                $deprecations[] = $message;

                return true;
            },
            E_DEPRECATED
        );

        try {
            $this->assertSame('a b', parseBladewindVariable('a b', 'string'));
        } finally {
            restore_error_handler();
        }

        $this->assertContains('Constant FILTER_SANITIZE_STRING is deprecated', $deprecations);
    }

    #[Test]
    public function parse_bladewind_variable_passes_unknown_modes_through(): void
    {
        $this->assertSame('untouched', parseBladewindVariable('untouched', 'nonsense'));
    }

    #[Test]
    public function parse_bladewind_name_collapses_spaces_and_dashes(): void
    {
        $this->assertSame('my_name', parseBladewindName('my name'));
        $this->assertSame('my_name', parseBladewindName('my-name'));
        $this->assertSame('a_b_c', parseBladewindName('a b-c'));
        $this->assertSame('already_fine', parseBladewindName('already_fine'));
    }

    #[Test]
    public function default_bladewind_name_is_prefixed_and_normalised(): void
    {
        $name = defaultBladewindName('input-');

        $this->assertStringStartsWith('input_', $name);
        $this->assertDoesNotMatchRegularExpression('/[\s-]/', $name);
        $this->assertNotSame($name, defaultBladewindName('input-'));
    }

    #[Test]
    #[DataProvider('radiusProvider')]
    public function get_radius_string_maps_the_named_scale(string $radius, string $expected): void
    {
        $this->assertSame($expected, getRadiusString($radius));
    }

    public static function radiusProvider(): array
    {
        return [
            ['none', 'rounded-none'],
            ['small', 'rounded-lg'],
            ['medium', 'rounded-xl'],
            ['large', 'rounded-2xl'],
            ['xl', 'rounded-3xl'],
        ];
    }

    #[Test]
    public function get_radius_string_applies_a_side_prefix(): void
    {
        $this->assertSame('rounded-t-lg', getRadiusString('small', 't'));
        $this->assertSame('rounded-br-2xl', getRadiusString('large', 'br'));
    }

    /**
     * BUG, pinned deliberately — do not "fix" this test, fix the helper.
     *
     * getRadiusString() indexes $roundness directly and only then applies `?? ''`,
     * so the coalesce can never run: an unrecognised radius raises "Undefined array
     * key" rather than falling back. Inside a Blade view that surfaces as a
     * ViewException, so a typo in a radius prop takes the whole page down. The
     * named scale in improvements.md item 2 needs to decide what unknown means.
     */
    #[Test]
    public function get_radius_string_currently_errors_on_an_unknown_radius(): void
    {
        $this->expectException(\ErrorException::class);

        getRadiusString('full');
    }

    #[Test]
    public function colour_validation_accepts_the_documented_palette(): void
    {
        $this->assertTrue(isValidBladewindColour('primary'));
        $this->assertTrue(isValidBladewindColour('red'));
        $this->assertFalse(isValidBladewindColour('chartreuse'));
    }

    #[Test]
    public function default_bladewind_colour_falls_back_for_unknown_colours(): void
    {
        $this->assertSame('red', defaultBladewindColour('red'));
        $this->assertSame('primary', defaultBladewindColour('chartreuse'));
        $this->assertSame('blue', defaultBladewindColour('chartreuse', 'blue'));
    }

    #[Test]
    public function aspect_ratio_validation_accepts_the_documented_ratios(): void
    {
        $this->assertTrue(isValidAspectRatio('16:9'));
        $this->assertFalse(isValidAspectRatio('21:9'));
    }

    #[Test]
    public function format_json_for_chart_unquotes_js_callbacks(): void
    {
        $output = formatJsonForChart(['label' => 'Sales', 'fn' => 'JS::function(v){return v;}']);

        $this->assertStringContainsString('"label":"Sales"', $output);
        $this->assertStringContainsString('"fn":function(v){return v;}', $output);
    }

    #[Test]
    public function pagination_row_hides_rows_outside_the_current_page(): void
    {
        $this->assertStringContainsString('data-page=1', paginationRow(1, 25, 1));
        $this->assertStringNotContainsString('class=hidden', paginationRow(1, 25, 1));
        $this->assertStringContainsString('class=hidden', paginationRow(30, 25, 1));
        $this->assertStringContainsString('data-page=2', paginationRow(30, 25, 1));
    }

    #[Test]
    public function the_snake_case_pagination_alias_matches_the_camel_case_helper(): void
    {
        $this->assertSame(
            preg_replace('/data-id=\w+/', '', paginationRow(30, 25, 1)),
            preg_replace('/data-id=\w+/', '', pagination_row(30, 25, 1))
        );
    }
}
