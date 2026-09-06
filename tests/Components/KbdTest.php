<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class KbdTest extends TestCase
{
    use RendersComponents;

    private const KBD = '//kbd';

    #[Test]
    public function it_renders_the_slot_as_a_single_key(): void
    {
        $html = $this->render('<x-bladewind::kbd>Ctrl</x-bladewind::kbd>');

        $this->assertStringContainsString('Ctrl', $html);
        $this->assertElementCount($html, self::KBD, 1);
        $this->assertHasClasses($html, self::KBD, ['bw-kbd']);
    }

    #[Test]
    public function a_keys_array_renders_a_combo_joined_by_plus_signs(): void
    {
        $html = $this->render('<x-bladewind::kbd :keys="[\'Ctrl\', \'Shift\', \'K\']" />');

        $this->assertElementCount($html, self::KBD, 3);
        $this->assertElementCount($html, '//span[@aria-hidden="true"]', 2);
        $this->assertStringContainsString('Ctrl', $html);
        $this->assertStringContainsString('Shift', $html);
        $this->assertStringContainsString('K', $html);
    }

    #[Test]
    public function keys_takes_priority_over_the_slot(): void
    {
        $html = $this->render('<x-bladewind::kbd :keys="[\'Esc\']">Ignored</x-bladewind::kbd>');

        $this->assertStringNotContainsString('Ignored', $html);
        $this->assertStringContainsString('Esc', $html);
    }

    #[Test]
    public function size_controls_the_padding_and_text_scale(): void
    {
        $html = $this->render('<x-bladewind::kbd size="regular">Enter</x-bladewind::kbd>');

        $this->assertHasClasses($html, self::KBD, ['px-2', 'py-1', 'text-sm']);
    }

    #[Test]
    public function an_invalid_size_falls_back_to_small(): void
    {
        $html = $this->render('<x-bladewind::kbd size="huge">Enter</x-bladewind::kbd>');

        $this->assertHasClasses($html, self::KBD, ['px-1.5', 'py-0.5', 'text-xs']);
    }

    #[Test]
    public function consumer_classes_are_appended_to_a_single_key(): void
    {
        $html = $this->render('<x-bladewind::kbd class="ml-2">Tab</x-bladewind::kbd>');

        $this->assertHasClasses($html, self::KBD, ['ml-2']);
    }

    #[Test]
    public function consumer_classes_are_appended_to_the_combo_wrapper(): void
    {
        $html = $this->render('<x-bladewind::kbd :keys="[\'Ctrl\', \'K\']" class="ml-2" />');

        $this->assertHasClasses($html, '//span[contains(@class, "bw-kbd-group")]', ['ml-2']);
    }

    #[Test]
    public function config_supplies_the_default_size(): void
    {
        config(['bladewind.kbd.size' => 'regular']);

        $html = $this->render('<x-bladewind::kbd>Enter</x-bladewind::kbd>');

        $this->assertHasClasses($html, self::KBD, ['text-sm']);
    }
}
