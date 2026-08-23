<?php

namespace Mkocansey\Bladewind\Tests\E2E;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Writes the pages the Playwright specs run against.
 *
 * They are generated from the real Blade templates rather than hand-written, so a
 * component change cannot leave the browser fixtures testing markup the library
 * no longer produces. Not part of the default suite — `npm run test:e2e` runs
 * this first, then Playwright.
 */
class BuildFixturesTest extends TestCase
{
    use RendersComponents;

    private const OUT = __DIR__.'/public';

    /**
     * asset() gives absolute URLs against testbench's host; the fixture server
     * serves the same files from its own root.
     */
    private function relative(string $html): string
    {
        return str_replace(['http://localhost/vendor/', 'https://localhost/vendor/'], '/vendor/', $html);
    }

    private function page(string $title, string $body, string $scripts = ''): string
    {
        return <<<HTML
        <!doctype html>
        <html>
        <head>
        <meta charset="utf-8">
        <title>{$title}</title>
        <link href="/vendor/bladewind/css/bladewind-ui.min.css" rel="stylesheet">
        <style>
          body { font-family: system-ui; padding: 24px; }
          /* the wrapper every wide table needs, and the one that eats popups:
             overflow-x auto silently computes overflow-y to auto as well */
          .scroll-wrapper { overflow-x: auto; border: 2px dashed #94a3b8; padding: 12px;
                            max-width: 520px; height: 150px; }
          /* the trigger sits near the wrapper's bottom edge and away from the
             left, so an opening popup has to overhang to be visible at all, and
             so horizontal movement is measurable rather than clamped to x=0 */
          .wide { min-width: 900px; padding: 90px 0 0 120px; }
        </style>
        <script src="/vendor/bladewind/js/helpers.js"></script>
        </head>
        <body>
        {$body}
        {$scripts}
        </body>
        </html>
        HTML;
    }

    #[Test]
    public function it_writes_the_popup_clipping_fixture(): void
    {
        $data = [
            ['label' => 'Ghana', 'value' => 'GH'],
            ['label' => 'Kenya', 'value' => 'KE'],
            ['label' => 'Nigeria', 'value' => 'NG'],
            ['label' => 'Togo', 'value' => 'TG'],
        ];

        $select = $this->render(
            '<x-bladewind::select name="country" :data="$d" placeholder="Pick a country" />',
            ['d' => $data]
        );

        $dropmenu = $this->render(
            "<x-bladewind::dropmenu name=\"acts\" trigger=\"ellipsis-vertical-icon\" trigger_label=\"Actions\">\n"
            ."<x-bladewind::dropmenu.item>Edit</x-bladewind::dropmenu.item>\n"
            ."<x-bladewind::dropmenu.item>Duplicate</x-bladewind::dropmenu.item>\n"
            ."<x-bladewind::dropmenu.item>Delete</x-bladewind::dropmenu.item>\n"
            ."</x-bladewind::dropmenu>"
        );

        $popover = $this->render(
            '<x-bladewind::popover name="pop" trigger="Details" position="bottom">'
            .'Some detail that needs room to breathe.</x-bladewind::popover>'
        );

        $body = <<<HTML
        <h1>Popups inside a scrolling ancestor</h1>

        <div class="scroll-wrapper" id="select-wrapper">
          <div class="wide"><div style="width:280px">{$select}</div></div>
        </div>

        <div class="scroll-wrapper" id="dropmenu-wrapper" style="margin-top:24px">
          <div class="wide">{$dropmenu}</div>
        </div>

        <div class="scroll-wrapper" id="popover-wrapper" style="margin-top:24px">
          <div class="wide">{$popover}</div>
        </div>
        HTML;

        file_put_contents(
            self::OUT.'/popup-clipping.html',
            $this->relative($this->page('Popup clipping', $body))
        );

        $this->assertFileExists(self::OUT.'/popup-clipping.html');
    }
}
