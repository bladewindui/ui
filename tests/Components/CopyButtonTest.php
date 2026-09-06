<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CopyButtonTest extends TestCase
{
    use RendersComponents;

    private const ROOT = '//span[contains(concat(" ", normalize-space(@class), " "), " bw-copy-button ")]';
    private const TRIGGER = '//button[@data-trigger]';
    private const CONTENT = '//span[@data-content]';
    private const LABEL = '//span[@data-label]';

    #[Test]
    public function an_explicit_value_is_exposed_as_a_data_attribute_with_no_wrapped_content(): void
    {
        $html = $this->render('<x-bladewind::copy-button value="secret-token" />');

        $this->assertAttribute($html, self::ROOT, 'data-value', 'secret-token');
        $this->assertNoElement($html, self::CONTENT);
    }

    #[Test]
    public function slot_content_renders_as_wrapped_text_beside_an_icon_only_trigger(): void
    {
        $html = $this->render('<x-bladewind::copy-button>npm install bladewindui</x-bladewind::copy-button>');

        $this->assertStringContainsString('npm install bladewindui', $html);
        $this->assertElementCount($html, self::CONTENT, 1);
        $this->assertNoElement($html, self::LABEL);
    }

    #[Test]
    public function a_label_renders_only_when_there_is_no_slot_content(): void
    {
        $html = $this->render('<x-bladewind::copy-button value="1234" label="Copy code" />');

        $this->assertStringContainsString('Copy code', $html);
        $this->assertElementCount($html, self::LABEL, 1);
    }

    #[Test]
    public function a_label_is_ignored_when_slot_content_is_present(): void
    {
        $html = $this->render('<x-bladewind::copy-button label="Ignored">Visible text</x-bladewind::copy-button>');

        $this->assertStringNotContainsString('Ignored', $html);
        $this->assertNoElement($html, self::LABEL);
    }

    #[Test]
    public function the_trigger_has_an_accessible_label(): void
    {
        $html = $this->render('<x-bladewind::copy-button value="1234" copy-label="Copy the code" />');

        $this->assertAttribute($html, self::TRIGGER, 'aria-label', 'Copy the code');
    }

    #[Test]
    public function messages_and_timeout_are_exposed_as_data_attributes(): void
    {
        $html = $this->render('<x-bladewind::copy-button value="1234" copied-message="Done!" failed-message="Oops" timeout="3000" />');

        $this->assertAttribute($html, self::ROOT, 'data-copied-message', 'Done!');
        $this->assertAttribute($html, self::ROOT, 'data-failed-message', 'Oops');
        $this->assertAttribute($html, self::ROOT, 'data-timeout', '3000');
    }

    #[Test]
    public function an_invalid_timeout_falls_back_to_the_default(): void
    {
        $html = $this->render('<x-bladewind::copy-button value="1234" timeout="not-a-number" />');

        $this->assertAttribute($html, self::ROOT, 'data-timeout', '1500');
    }

    #[Test]
    public function consumer_classes_are_appended(): void
    {
        $html = $this->render('<x-bladewind::copy-button value="1234" class="ml-2" />');

        $this->assertHasClasses($html, self::ROOT, ['ml-2']);
    }

    #[Test]
    public function config_supplies_the_default_timeout(): void
    {
        config(['bladewind.copy_button.timeout' => 5000]);

        $html = $this->render('<x-bladewind::copy-button value="1234" />');

        $this->assertAttribute($html, self::ROOT, 'data-timeout', '5000');
    }
}
