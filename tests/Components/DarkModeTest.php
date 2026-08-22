<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\Support\CompiledStylesheet;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * #598 — the two components the dark audit caught rendering light on a dark page.
 *
 * Both failed the same way: a utility in the template beat the components-layer
 * dark rule, because utilities are a later cascade layer. The fix in both cases
 * is a dark: utility in the template, which competes on equal terms.
 */
class DarkModeTest extends TestCase
{
    use RendersComponents;

    #[Test]
    public function the_select_trigger_has_a_dark_background(): void
    {
        $html = $this->render(
            '<x-bladewind::select name="c" :data="$d" />',
            ['d' => [['label' => 'Ghana', 'value' => 'GH']]]
        );

        // bg-white is a utility, so it beat .bw-select div.clickable's dark rule
        // in the components layer and the trigger stayed white on a dark page
        $this->assertHasClasses($html, $this->withClass('clickable'), ['bg-white', 'dark:bg-transparent']);
    }

    #[Test]
    public function the_select_dropdown_panel_has_a_dark_background(): void
    {
        $html = $this->render(
            '<x-bladewind::select name="c" :data="$d" />',
            ['d' => [['label' => 'Ghana', 'value' => 'GH']]]
        );

        $this->assertHasClasses($html, $this->withClass('bw-select-items-container'), ['dark:bg-dark-700']);
    }

    #[Test]
    #[DataProvider('alertProvider')]
    public function a_faint_alert_dims_itself_in_dark_mode(string $type, string $colour): void
    {
        $html = $this->render('<x-bladewind::alert type="'.$type.'">Message</x-bladewind::alert>');

        $this->assertHasClasses($html, $this->withClass('bw-alert'), [
            'bg-'.$colour.'-100/70',
            'dark:bg-'.$colour.'-500/15',
            'dark:text-'.$colour.'-300',
        ]);
    }

    public static function alertProvider(): array
    {
        return [
            'info' => ['info', 'blue'],
            'error' => ['error', 'red'],
            'success' => ['success', 'green'],
            'warning' => ['warning', 'yellow'],
        ];
    }

    /**
     * Those alert classes are built by interpolation, so Tailwind cannot see them
     * in the template and will not generate them unless they are safelisted in
     * compile-for-tailwind.js. Without this the markup is right and the stylesheet
     * has no rule to match it — which is exactly how the first fix failed.
     */
    #[Test]
    public function the_interpolated_alert_dark_classes_are_actually_compiled(): void
    {
        $css = (new CompiledStylesheet())->raw();

        foreach (['blue', 'red', 'green', 'yellow'] as $colour) {
            $this->assertStringContainsString(
                'dark\\:bg-'.$colour.'-500\\/15',
                $css,
                "dark:bg-{$colour}-500/15 is emitted by alert.blade.php but was not compiled. "
                .'Add it to packages/core/public/js/compile-for-tailwind.js.'
            );
        }
    }
}
