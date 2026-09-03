<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class SpinnerTest extends TestCase
{
    use RendersComponents;

    private function spinner(): string
    {
        return $this->withClass('bw-spinner', 'svg');
    }

    #[Test]
    public function it_renders_the_default_markup(): void
    {
        $html = $this->render('<x-bladewind::spinner />');

        $this->assertHasClasses($html, $this->spinner(), ['animate-spin', 'h-6', 'w-6', 'text-slate-600']);
    }

    #[Test]
    #[DataProvider('sizeProvider')]
    public function size_controls_the_dimensions(string $size, string $expected): void
    {
        $html = $this->render('<x-bladewind::spinner size="'.$size.'" />');

        $this->assertHasClasses($html, $this->spinner(), [$expected]);
    }

    public static function sizeProvider(): array
    {
        return [
            'small' => ['small', 'h-6'],
            'medium' => ['medium', 'h-10'],
            'big' => ['big', 'h-14'],
            'xl' => ['xl', 'h-24'],
            'omg' => ['omg', 'h-36'],
        ];
    }

    /**
     * "gray" is remapped to "slate" so the spinner colour matches the rest of
     * the library's neutral palette; any other named colour passes straight
     * through.
     */
    #[Test]
    public function the_default_gray_colour_renders_as_slate(): void
    {
        $html = $this->render('<x-bladewind::spinner color="gray" />');

        $this->assertHasClasses($html, $this->spinner(), ['text-slate-600']);
    }

    #[Test]
    public function a_named_colour_is_used_directly(): void
    {
        $html = $this->render('<x-bladewind::spinner color="red" />');

        $this->assertHasClasses($html, $this->spinner(), ['text-red-600']);
    }

    #[Test]
    public function additional_classes_are_appended(): void
    {
        $html = $this->render('<x-bladewind::spinner class="mx-auto" />');

        $this->assertHasClasses($html, $this->spinner(), ['mx-auto']);
    }
}
