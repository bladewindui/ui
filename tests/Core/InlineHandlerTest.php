<?php

namespace Mkocansey\Bladewind\Tests\Core;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * #608 — no component may attach its own behaviour with an inline on* attribute.
 *
 * A strict Content-Security-Policy blocks them outright, and no nonce helps: a
 * nonce authorises <script> elements, not on* attributes. This library threads a
 * nonce prop through every <x-bladewind::script> precisely so a CSP can be used,
 * which was undermined by 30 inline handlers doing the actual work.
 *
 * A handler whose value is a consumer-supplied prop is the consumer's own
 * JavaScript and is left alone — passing a JS string is inherently inline, and
 * refusing to render it would break the documented API.
 */
class InlineHandlerTest extends TestCase
{
    use RendersComponents;

    private const EVENTS = 'onclick|onchange|oninput|onfocus|onblur|onmouseover|onmouseout|onkeydown|onkeyup|onbeforeinput|onsubmit';

    /** @return list<string> */
    private function componentTemplates(): array
    {
        $root = __DIR__.'/../..';

        return array_merge(
            glob($root.'/packages/*/resources/views/components/*.blade.php'),
            glob($root.'/packages/*/resources/views/components/**/*.blade.php'),
        );
    }

    /**
     * A handler is the library's own when its value starts with a bare function
     * call or a statement — as opposed to a Blade echo of a consumer prop.
     */
    #[Test]
    public function no_component_attaches_its_own_behaviour_with_an_inline_handler(): void
    {
        $offenders = [];

        foreach ($this->componentTemplates() as $file) {
            preg_match_all(
                '/('.self::EVENTS.')="([^"]*)"/i',
                file_get_contents($file),
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as [$whole, $event, $value]) {
                $value = trim($value);

                // a consumer-supplied prop, echoed straight through
                if (str_starts_with($value, '{!!') || str_starts_with($value, '{{') || str_starts_with($value, '$')) {
                    continue;
                }

                $offenders[] = basename(dirname($file)).'/'.basename($file).' — '.$event.'="'.substr($value, 0, 48).'"';
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "These components attach their own behaviour inline, which a strict CSP blocks.\n"
            ."Use bwOn() to bind a delegated listener instead:\n  "
            .implode("\n  ", $offenders)
        );
    }

    /**
     * The escape hatch has to keep working, or the fix would break the API.
     */
    #[Test]
    public function a_consumer_supplied_handler_is_still_rendered(): void
    {
        $card = $this->render('<x-bladewind::card url="/orders">c</x-bladewind::card>');
        $icon = $this->render('<x-bladewind::icon name="user" action="doThing()" />');
        $toggle = $this->render('<x-bladewind::toggle name="t" onclick="doThing()" />');

        $this->assertAttribute($card, $this->withClass('bw-card'), 'onclick', "location.href='/orders'");
        $this->assertAttribute($icon, '//a', 'onclick', 'doThing()');
        $this->assertAttribute($toggle, '//input[@type="checkbox"]', 'onclick', 'doThing()');
    }

    /**
     * Spot checks that the delegation hooks the templates now emit are the ones
     * the scripts actually bind to. A renamed attribute on one side only would
     * leave a component silently inert.
     */
    #[Test]
    public function the_markup_hooks_match_what_the_scripts_bind(): void
    {
        $hooks = [
            'data-bw-tag-value' => 'packages/core/public/js/helpers.js',
            'data-bw-focuses' => 'packages/core/public/js/helpers.js',
            'data-bw-modal-close' => 'packages/core/public/js/helpers.js',
            'data-bw-tab' => 'packages/core/public/js/helpers.js',
            'data-can-sort' => 'packages/core/public/js/table.js',
            'data-bw-select-search' => 'packages/core/public/js/select.js',
        ];

        foreach ($hooks as $hook => $script) {
            $this->assertStringContainsString(
                $hook,
                file_get_contents(__DIR__.'/../../'.$script),
                "{$script} does not bind to [{$hook}], so the markup emitting it is inert"
            );
        }
    }

    #[Test]
    public function the_delegation_helpers_are_exported(): void
    {
        $helpers = file_get_contents(__DIR__.'/../../packages/core/public/js/helpers.js');

        $this->assertStringContainsString('const bwOn =', $helpers);
        $this->assertStringContainsString('const bwActivateOnKey =', $helpers);
        $this->assertStringContainsString('bwOn,', $helpers);
    }
}
