<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class InlineEditTest extends TestCase
{
    use RendersComponents;

    private const DISPLAY_TEXT = '//span[@data-display-text]';
    private const INPUT = '//input[@data-input]';
    private const HIDDEN = '//input[@data-hidden-value]';

    #[Test]
    public function it_displays_the_given_value(): void
    {
        $html = $this->render('<x-bladewind::inline-edit name="title" value="Hello world" />');

        $this->assertStringContainsString('Hello world', $html);
        $this->assertMissingClasses($html, self::DISPLAY_TEXT, ['italic']);
    }

    #[Test]
    public function an_empty_value_shows_the_placeholder_in_a_muted_style(): void
    {
        $html = $this->render('<x-bladewind::inline-edit name="title" placeholder="Untitled" />');

        $this->assertStringContainsString('Untitled', $html);
        $this->assertHasClasses($html, self::DISPLAY_TEXT, ['italic', 'text-gray-400']);
    }

    #[Test]
    public function the_hidden_field_carries_the_current_value_for_normal_form_submission(): void
    {
        $html = $this->render('<x-bladewind::inline-edit name="title" value="Hello world" />');

        $this->assertAttribute($html, self::HIDDEN, 'name', 'title');
        $this->assertAttribute($html, self::HIDDEN, 'value', 'Hello world');
    }

    #[Test]
    public function the_edit_input_is_prefilled_and_starts_hidden(): void
    {
        $html = $this->render('<x-bladewind::inline-edit name="title" value="Hello world" />');

        $this->assertAttribute($html, self::INPUT, 'value', 'Hello world');
        $this->assertHasClasses($html, '//div[@data-edit-form]', ['hidden']);
    }

    #[Test]
    public function maxlength_is_threaded_through_to_the_input(): void
    {
        $html = $this->render('<x-bladewind::inline-edit name="title" maxlength="50" />');

        $this->assertAttribute($html, self::INPUT, 'maxlength', '50');
    }

    #[Test]
    public function required_and_its_message_are_exposed_as_data_attributes(): void
    {
        $html = $this->render('<x-bladewind::inline-edit name="title" required="true" required-message="Please enter a title" />');

        $this->assertAttribute($html, '//div[contains(@class, "bw-inline-edit")]', 'data-required', '1');
        $this->assertAttribute($html, '//div[contains(@class, "bw-inline-edit")]', 'data-required-message', 'Please enter a title');
    }

    #[Test]
    public function config_supplies_the_default_required_state(): void
    {
        config(['bladewind.inline_edit.required' => true]);

        $html = $this->render('<x-bladewind::inline-edit name="title" />');

        $this->assertAttribute($html, '//div[contains(@class, "bw-inline-edit")]', 'data-required', '1');
    }

    #[Test]
    public function consumer_classes_are_appended(): void
    {
        $html = $this->render('<x-bladewind::inline-edit name="title" value="Hello" class="mt-2" />');

        $this->assertHasClasses($html, '//div[contains(@class, "bw-inline-edit")]', ['mt-2']);
    }
}
