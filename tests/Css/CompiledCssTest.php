<?php

namespace Mkocansey\Bladewind\Tests\Css;

use Mkocansey\Bladewind\Tests\Support\CompiledStylesheet;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tier B of improvements.md item 10 — assertions against the built
 * packages/core/public/css/bladewind-ui.min.css rather than against Blade output.
 *
 * The Tailwind v4 co-existence failure (item 1 / #589) is a cascade failure: an
 * input renders borderless because a token resolves to nothing once the host app's
 * own v4 build lands. Nothing asserted on rendered markup can see that. These tests
 * can, and they are the guard that has to exist before item 1 is attempted.
 *
 * These do not need a Laravel container, so they extend PHPUnit's TestCase directly
 * rather than the Testbench one — they are static analysis of a build artefact.
 *
 * Run `npm run build` before these if you have changed a component's CSS source
 * under resources/assets/css; CI builds the bundle in the test job for the same
 * reason.
 */
class CompiledCssTest extends TestCase
{
    private CompiledStylesheet $css;

    protected function setUp(): void
    {
        parent::setUp();

        $this->css = new CompiledStylesheet();
    }

    #[Test]
    public function the_bundle_is_present_and_looks_like_a_real_build(): void
    {
        $this->assertGreaterThan(100_000, strlen($this->css->raw()));

        foreach (['theme', 'base', 'components', 'utilities'] as $layer) {
            $this->assertNotSame('', $this->css->layer($layer), "@layer {$layer} is missing from the bundle");
        }
    }

    /**
     * The invariant that makes the bundle survive a hostile host theme: if a
     * .bw-* rule reads a token, the bundle itself must also define that token.
     * A reference to a token only the host app defines is a component whose
     * appearance is decided by somebody else's build.
     */
    #[Test]
    public function every_token_a_component_rule_reads_is_defined_by_the_bundle_itself(): void
    {
        $defined = $this->css->definedCustomProperties();
        $registered = array_keys($this->css->registeredCustomProperties());
        $available = array_merge($defined, $registered);

        $orphans = array_diff(
            array_keys($this->css->unfallbackedTokensInComponentRules()),
            $available
        );

        $this->assertSame(
            [],
            array_values($orphans),
            'These custom properties are read by .bw-* rules but never defined in the bundle, '
            .'so their value comes entirely from the consuming app: '.implode(', ', $orphans)
        );
    }

    /**
     * The baseline only ever shrinks. Every entry is a place where a host app that
     * trims or overrides the token leaves a BladeWind component with no value —
     * `@theme { --color-gray-200: initial }` is a documented Tailwind v4 pattern and
     * it makes `border-color: var(--color-gray-200)` compute to nothing.
     *
     * Item 1 / #589 is the work of shortening this list by giving each reference a
     * fallback. Regenerate with `php bin/dump-unfallbacked-tokens.php` when it does.
     */
    #[Test]
    public function the_set_of_unfallbacked_tokens_does_not_grow(): void
    {
        $baseline = array_values(array_filter(
            file(__DIR__.'/../fixtures/css/unfallbacked-tokens.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
            fn (string $line): bool => ! str_starts_with($line, '#')
        ));

        $current = array_keys($this->css->unfallbackedTokensInComponentRules());

        $added = array_values(array_diff($current, $baseline));

        $this->assertSame(
            [],
            $added,
            'New unfallbacked token references appeared in .bw-* rules: '.implode(', ', $added)
            ."\nEach one is a component whose value disappears if the host app does not define that token. "
            .'Give each a fallback — var(--token, <literal>) — or, if this is deliberate, '
            .'regenerate the baseline with `php bin/dump-unfallbacked-tokens.php`.'
        );
    }

    /**
     * `border-style: var(--tw-border-style)` appears 45 times in .bw-* rules and is
     * the single most load-bearing token in the bundle: it only resolves because of
     * this @property registration's initial-value. Drop the registration and every
     * input, textarea, select and enabled select trigger renders with
     * border-style: none — which is exactly the borderless-input report behind item 1.
     */
    #[Test]
    public function the_border_style_token_is_registered_with_a_solid_initial_value(): void
    {
        $registered = $this->css->registeredCustomProperties();

        $this->assertArrayHasKey('--tw-border-style', $registered);
        $this->assertStringContainsString('initial-value:solid', $registered['--tw-border-style']);
        $this->assertStringContainsString('inherits:false', $registered['--tw-border-style']);
    }

    #[Test]
    public function the_outline_style_token_is_registered_with_a_solid_initial_value(): void
    {
        $registered = $this->css->registeredCustomProperties();

        $this->assertArrayHasKey('--tw-outline-style', $registered);
        $this->assertStringContainsString('initial-value:solid', $registered['--tw-outline-style']);
    }

    /**
     * The three form surfaces the consuming app's compat file had to restore.
     * Border width must be a literal, not a token: it is the one property whose
     * loss is invisible in markup and total on screen.
     */
    #[Test]
    public function the_form_surfaces_declare_a_literal_border_width(): void
    {
        $base = $this->css->rule('.bw-textarea,.bw-raw-select,.bw-input');

        $this->assertNotNull($base, 'The shared input/textarea/select base rule is gone from the bundle');
        $this->assertMatchesRegularExpression('/border-width:\s*\d/', $base);
        $this->assertStringContainsString('border-style:', $base);
    }

    #[Test]
    public function the_form_surfaces_declare_a_border_colour(): void
    {
        $declarations = $this->css->declarationsMatching('.bw-input:not(.has-error)');

        $this->assertStringContainsString('border-color:', $declarations);
    }

    #[Test]
    public function the_error_state_declares_its_own_border_colour(): void
    {
        $declarations = $this->css->declarationsMatching('.bw-input.has-error');

        $this->assertStringContainsString('border-color:', $declarations);
    }

    #[Test]
    public function the_enabled_select_trigger_declares_a_literal_border_width(): void
    {
        $rule = $this->css->rule('.bw-select div.clickable.enabled');

        $this->assertNotNull($rule, 'The enabled select trigger rule is gone from the bundle');
        $this->assertMatchesRegularExpression('/border-width:\s*\d/', $rule);
        $this->assertStringContainsString('border-color:', $rule);
    }

    /**
     * `.bw-card.shadowed` is on the consuming app's restore list, and card leans on
     * it entirely — the component emits no shadow utility of its own, just the class.
     */
    #[Test]
    public function the_card_shadow_resolves_to_a_literal_box_shadow(): void
    {
        $light = $this->css->rule('.bw-card.shadowed');
        $hover = $this->css->rule('.bw-card.shadowed-hover:hover');

        $this->assertNotNull($light, 'The light-mode card shadow rule is gone from the bundle');
        $this->assertNotNull($hover);
        $this->assertStringNotContainsString('var(', $light);
        $this->assertStringContainsString('box-shadow:', $light);
    }

    #[Test]
    public function the_card_shadow_has_a_dark_counterpart(): void
    {
        $this->assertNotNull($this->css->rule('.dark .bw-card.shadowed'));
    }

    /**
     * FINDING, pinned deliberately — see improvements.md item 1.
     *
     * The bundle ships Tailwind's full Preflight in @layer base, ~9.8 KB of global
     * reset including `*,:after,:before,::backdrop { border: 0 solid; margin: 0;
     * padding: 0 }`. A component library's stylesheet resetting every element in the
     * host document is the co-existence problem in its purest form: the consuming app
     * gets two resets, and which one wins depends on load order.
     *
     * Pinned rather than removed because dropping Preflight changes how the bundle
     * renders for every existing consumer, including those not on v4 at all.
     */
    #[Test]
    public function the_bundle_currently_ships_a_full_preflight_reset(): void
    {
        $base = $this->css->layer('base');

        $this->assertStringContainsString('*,:after,:before,::backdrop{', $base);
        $this->assertStringContainsString('border:0 solid', $base);
        $this->assertGreaterThan(5_000, strlen($base));
    }

    /**
     * FINDING, pinned deliberately — see improvements.md item 11.
     *
     * tailwind.css defines the dark variant as `&:where(.dark, .dark *)`. :where()
     * contributes zero specificity, so `.bw-input:where(.dark,.dark *)` ties exactly
     * with `.bw-input` and is decided by source order alone. Any host rule of equal
     * specificity that comes later wins in both colour schemes at once.
     *
     * Two rules in the bundle use the older `.dark .bw-card` descendant form instead,
     * which does carry specificity — so the bundle currently mixes both mechanisms.
     */
    #[Test]
    public function dark_variants_currently_compile_to_zero_specificity_where_clauses(): void
    {
        $raw = $this->css->raw();

        $this->assertGreaterThan(200, substr_count($raw, ':where(.dark,.dark *)'));
        $this->assertSame(2, preg_match_all('/\.dark\s+\./', $raw));
    }

    /**
     * The toggle knob is drawn entirely with ::after utilities. If the bundle is
     * rebuilt without them the switch renders as an empty track with no knob and no
     * movement on check — and nothing in the Blade output changes, so no render test
     * sees it. This is not hypothetical: these selectors were missing from the
     * committed bundle until Tier B caught the drift.
     */
    #[Test]
    public function the_bundle_covers_the_utilities_the_toggle_knob_is_drawn_with(): void
    {
        $raw = $this->css->raw();

        foreach ([
            '.after\\:content-\\[\\\'\\\'\\]:after',
            '.after\\:absolute:after',
            '.after\\:start-1:after',
            '.after\\:top-1\\/2:after',
            '.after\\:-translate-y-1\\/2:after',
        ] as $selector) {
            $this->assertStringContainsString(
                $selector,
                $raw,
                "The compiled bundle is missing [{$selector}], which toggle.blade.php emits. "
                .'Run `npm run build` and commit the result.'
            );
        }
    }

    #[Test]
    public function the_bundle_covers_the_utilities_the_dropmenu_positioning_fix_emits(): void
    {
        $raw = $this->css->raw();

        $this->assertStringContainsString('.\\!z-\\[9999\\]', $raw);
        $this->assertStringContainsString('.-left-1', $raw);
    }

    /**
     * #590: BladeWind used to ship 78 `!important` declarations inside .bw-* rules,
     * and because CSS Cascade 5 reverses layer order for important declarations —
     * earlier layers win, unlayered important is weakest — a consumer could not
     * override any of them. Not with a plain utility, not with a Tailwind `!`
     * utility in the later utilities layer, not with `!important` in their own
     * stylesheet. There was no override path at all.
     *
     * Zero is the invariant now. A component rule that needs importance to win is a
     * component rule that a consumer cannot restyle.
     *
     * Importance aimed at third-party CSS is a different matter and lives outside
     * .bw-* selectors: the Quill rules in input.css, sortable.css (fighting
     * SortableJS inline styles, published standalone) and the vendored popup
     * stylesheet all keep theirs.
     */
    /**
     * #589 — the hardening pass. Every var() a .bw-* rule reads now carries a
     * literal fallback taken from the bundle's own theme, so a host app that
     * trims the palette or the spacing scale (`@theme { --color-*: initial }` is
     * the documented v4 way) can no longer leave a component with no value.
     *
     * Verified in a browser: an un-hardened bundle under such a theme loses its
     * border colour and its padding-left entirely; a hardened one holds.
     */
    #[Test]
    public function component_rules_read_tokens_with_a_literal_fallback(): void
    {
        $unfallbacked = $this->css->unfallbackedTokensInComponentRules();

        // the two gradient position tokens are deliberately valueless — Tailwind
        // sets them per-utility, and there is no sensible literal to stand in
        $this->assertSame(
            ['--tw-gradient-position', '--tw-gradient-stops'],
            array_keys($unfallbacked),
            'Unexpected unfallbacked token references. Run `npm run build`, which applies '
            .'bin/harden-css.mjs, and commit the result.'
        );
    }

    /**
     * The Preflight-free variant exists for apps that compile their own Tailwind
     * and should not have the document reset twice by two stylesheets.
     */
    #[Test]
    public function a_preflight_free_variant_ships_alongside_the_full_bundle(): void
    {
        $variant = dirname(CompiledStylesheet::PATH).'/bladewind-ui-no-preflight.min.css';

        $this->assertFileExists($variant);

        $css = new CompiledStylesheet(file_get_contents($variant));

        // only Preflight goes. the forms plugin's base styles stay, because the
        // components are built on top of them and would look wrong without.
        $this->assertStringNotContainsString('*,:after,:before,::backdrop{', $css->raw());
        $this->assertStringNotContainsString('border:0 solid', $css->layer('base'));
        $this->assertStringContainsString('input:where([type=checkbox])', $css->layer('base'));
        $this->assertLessThan(
            strlen($this->css->layer('base')),
            strlen($css->layer('base')),
            'The variant should have a smaller base layer than the full bundle'
        );
    }

    #[Test]
    public function the_two_bundles_carry_identical_component_rules(): void
    {
        $variant = new CompiledStylesheet(
            file_get_contents(dirname(CompiledStylesheet::PATH).'/bladewind-ui-no-preflight.min.css')
        );

        $normalise = fn (array $rules): array => array_map(
            fn (array $r): string => $r['selector'].'{'.$r['declarations'].'}',
            $rules
        );

        $this->assertSame(
            $normalise($this->css->componentRules()),
            $normalise($variant->componentRules()),
            'The two bundles have drifted. They are built from one shared entry so that cannot happen.'
        );
    }

    #[Test]
    public function no_component_rule_uses_important(): void
    {
        $offenders = [];

        foreach ($this->css->componentRules() as $rule) {
            $count = substr_count($rule['declarations'], '!important');

            if ($count > 0) {
                $offenders[] = $rule['selector'].' ('.$count.')';
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "These .bw-* rules use !important, which no consumer can override — not with a plain\n"
            ."utility, not with a Tailwind `!` utility, not with !important of their own:\n  "
            .implode("\n  ", $offenders)
        );
    }
}
