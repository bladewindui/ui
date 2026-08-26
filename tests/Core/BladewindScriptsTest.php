<?php

namespace Mkocansey\Bladewind\Tests\Core;

use Mkocansey\Bladewind\Core\BladewindScripts;
use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * #595 — @bladewindScripts, replacing the hand-picked script list every consuming
 * layout was keeping in step by hand.
 */
class BladewindScriptsTest extends TestCase
{
    use RendersComponents;

    #[Test]
    public function helpers_always_comes_first(): void
    {
        $this->assertSame(['helpers.js'], BladewindScripts::resolve());
        $this->assertSame('helpers.js', BladewindScripts::resolve(['select'])[0]);
    }

    #[Test]
    public function a_component_name_pulls_in_its_script(): void
    {
        $this->assertSame(
            ['helpers.js', 'select.js', 'dropmenu.js'],
            BladewindScripts::resolve(['select', 'dropmenu'])
        );
    }

    #[Test]
    public function names_are_case_and_whitespace_tolerant(): void
    {
        $this->assertSame(['helpers.js', 'select.js'], BladewindScripts::resolve([' Select ']));
    }

    #[Test]
    public function an_unknown_component_is_ignored_rather_than_fatal(): void
    {
        $this->assertSame(['helpers.js'], BladewindScripts::resolve(['nonsense']));
    }

    #[Test]
    public function scripts_are_not_repeated(): void
    {
        $this->assertSame(
            ['helpers.js', 'select.js'],
            BladewindScripts::resolve(['select', 'select'])
        );
    }

    #[Test]
    public function the_directive_emits_script_tags(): void
    {
        $html = $this->render("@bladewindScripts('select')");

        $this->assertElementCount($html, '//script', 2);
        $this->assertAttributeContains($html, '//script[1]', 'src', 'vendor/bladewind/js/helpers.js');
        $this->assertAttributeContains($html, '//script[2]', 'src', 'vendor/bladewind/js/select.js');
    }

    #[Test]
    public function the_directive_works_with_no_arguments(): void
    {
        $html = $this->render('@bladewindScripts');

        $this->assertElementCount($html, '//script', 1);
    }

    #[Test]
    public function a_configured_nonce_is_applied_to_every_tag(): void
    {
        config(['bladewind.script.nonce' => 'abc123']);

        $html = $this->render("@bladewindScripts('select')");

        $this->assertAttribute($html, '//script[1]', 'nonce', 'abc123');
        $this->assertAttribute($html, '//script[2]', 'nonce', 'abc123');
    }

    /**
     * The heavy dependencies stay out. chart.js, FilePond and cropper are ~610KB
     * between them, and their components already emit their own script tag when
     * they render, so a page that does not use them never fetches them.
     */
    #[Test]
    public function the_heavy_dependencies_are_not_in_the_map(): void
    {
        $all = implode(' ', array_merge(...array_values(BladewindScripts::MAP)));

        $this->assertStringNotContainsString('chart', $all);
        $this->assertStringNotContainsString('filepond', $all);
        $this->assertStringNotContainsString('cropper', $all);
    }

    /**
     * Every script the map points at has to exist, or the directive emits a 404.
     */
    #[Test]
    public function every_mapped_script_exists_on_disk(): void
    {
        $missing = [];

        foreach (BladewindScripts::resolve(BladewindScripts::known()) as $script) {
            $path = __DIR__.'/../../packages/core/public/js/'.$script;

            if (! file_exists($path)) {
                $missing[] = $script;
            }
        }

        $this->assertSame([], $missing, 'Mapped scripts missing from packages/core/public/js: '.implode(', ', $missing));
    }
}
