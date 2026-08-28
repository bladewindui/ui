<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class StepperTest extends TestCase
{
    use RendersComponents;

    private function stepper(string $attributes = '', string $children = ''): string
    {
        $children = $children ?: '<x-bladewind::stepper.item name="account" label="Account" state="complete" />'
            .'<x-bladewind::stepper.item name="profile" label="Profile" description="Personal details" state="current" />'
            .'<x-bladewind::stepper.item name="security" label="Security" />';

        return $this->render('<x-bladewind::stepper '.$attributes.'>'.$children.'</x-bladewind::stepper>');
    }

    #[Test]
    public function it_renders_named_semantic_navigation_with_an_ordered_step_list(): void
    {
        $html = $this->stepper('name="setup" aria-label="Account setup"');

        $this->assertElementCount($html, '//nav/ol', 1);
        $this->assertElementCount($html, '//nav/ol/li[contains(@class, "bw-stepper-item")]', 3);
        $this->assertAttribute($html, '//nav', 'data-name', 'setup');
        $this->assertAttribute($html, '//nav', 'aria-label', 'Account setup');
    }

    #[Test]
    public function it_supports_horizontal_and_vertical_orientations(): void
    {
        $horizontal = $this->stepper();
        $vertical = $this->stepper('orientation="vertical"');

        $this->assertHasClasses($horizontal, '//nav', ['bw-stepper-horizontal']);
        $this->assertHasClasses($vertical, '//nav', ['bw-stepper-vertical']);
        $this->assertAttribute($vertical, '//nav', 'data-orientation', 'vertical');
    }

    #[Test]
    public function it_supports_all_visual_styles_and_falls_back_to_circles(): void
    {
        foreach (['circles', 'chevrons', 'bars', 'line'] as $style) {
            $html = $this->stepper('style="'.$style.'"');

            $this->assertHasClasses($html, '//nav', ['bw-stepper-style-'.$style]);
            $this->assertAttribute($html, '//nav', 'data-style', $style);
            $this->assertStringNotContainsString(' style="'.$style.'"', $html);
        }

        $fallback = $this->stepper('style="unknown"');
        $this->assertHasClasses($fallback, '//nav', ['bw-stepper-style-circles']);
        $this->assertAttribute($fallback, '//nav', 'data-style', 'circles');

        $verticalChevron = $this->stepper('orientation="vertical" style="chevrons"');
        $this->assertHasClasses($verticalChevron, '//nav', ['bw-stepper-style-circles']);
        $this->assertAttribute($verticalChevron, '//nav', 'data-style', 'circles');

        foreach (['circles', 'bars', 'line'] as $verticalStyle) {
            $vertical = $this->stepper('orientation="vertical" style="'.$verticalStyle.'"');
            $this->assertHasClasses($vertical, '//nav', ['bw-stepper-style-'.$verticalStyle]);
            $this->assertAttribute($vertical, '//nav', 'data-style', $verticalStyle);
        }

        $lineComplete = $this->stepper('style="line"', '<x-bladewind::stepper.item name="done" label="Done" state="complete" />');
        $this->assertElementCount($lineComplete, '//*[contains(@class, "bw-stepper-complete-indicator")]', 0);
        $this->assertElementCount($lineComplete, '//*[contains(@class, "bw-stepper-error-indicator")]', 0);
    }

    #[Test]
    public function current_complete_upcoming_error_and_disabled_states_are_accessible(): void
    {
        $html = $this->stepper('',
            '<x-bladewind::stepper.item name="done" label="Done" state="complete" />'
            .'<x-bladewind::stepper.item name="now" label="Now" state="current" />'
            .'<x-bladewind::stepper.item name="later" label="Later" />'
            .'<x-bladewind::stepper.item name="bad" label="Needs attention" state="error" />'
            .'<x-bladewind::stepper.item name="locked" label="Locked" disabled="true" />'
        );

        foreach (['complete', 'current', 'upcoming', 'error', 'disabled'] as $state) {
            $this->assertElementCount($html, '//li[@data-state="'.$state.'"]', 1);
        }
        $this->assertAttribute($html, '//li[@data-state="current"]/button', 'aria-current', 'step');
        $this->assertAttribute($html, '//li[@data-state="disabled"]/button', 'aria-disabled', 'true');
        $this->assertAttribute($html, '//li[@data-state="disabled"]/button', 'disabled', 'disabled');
    }

    #[Test]
    public function root_current_is_recorded_as_the_canonical_initial_selection(): void
    {
        $html = $this->stepper('name="setup" current="security"');

        $this->assertAttribute($html, '//nav', 'data-current', 'security');
        $this->assertAttribute($html, '//nav', 'data-initial-current', 'security');
        $this->assertAttribute($html, '//li[@data-step="security"]/button', 'aria-current', 'step');
        $this->assertAttribute($html, '//li[@data-step="profile"]', 'data-state', 'upcoming');
    }

    #[Test]
    public function linear_clickable_and_number_options_are_exposed_without_prop_leaks(): void
    {
        $html = $this->stepper('linear="false" clickable="false" show_numbers="false" class="custom" data-test="steps"');

        $this->assertAttribute($html, '//nav', 'data-linear', 'false');
        $this->assertAttribute($html, '//nav', 'data-clickable', 'false');
        $this->assertAttribute($html, '//nav', 'data-show-numbers', 'false');
        $this->assertAttribute($html, '//nav', 'data-test', 'steps');
        $this->assertHasClasses($html, '//nav', ['bw-stepper', 'custom']);
        $this->assertStringNotContainsString('show_numbers=', $html);
    }

    #[Test]
    public function an_item_can_override_clickability_and_render_labels_descriptions_numbers_and_icons(): void
    {
        $html = $this->stepper('',
            '<x-bladewind::stepper.item name="profile" label="Profile" description="Personal details" number="4" clickable="false" icon="user" icon-type="solid" icon-dir="vendor/bladewind/icons/solid" class="custom-step" data-id="profile" />'
        );

        $this->assertStringContainsString('Profile', $html);
        $this->assertStringContainsString('Personal details', $html);
        $this->assertAttribute($html, '//button', 'data-clickable', 'false');
        $this->assertAttribute($html, '//button', 'data-id', 'profile');
        $this->assertHasClasses($html, '//button', ['bw-stepper-trigger', 'custom-step']);
        $this->assertElementCount($html, '//button//*[contains(@class, "bw-stepper-default-indicator")]/*[name()="svg"]', 1);
        $this->assertStringNotContainsString('icon-dir=', $html);
    }

    #[Test]
    public function custom_indicator_content_is_supported(): void
    {
        $html = $this->stepper('', '<x-bladewind::stepper.item name="custom" label="Custom"><span data-custom>VIP</span></x-bladewind::stepper.item>');

        $this->assertElementCount($html, '//*[contains(@class, "bw-stepper-custom-indicator")]//*[@data-custom]', 1);
    }

    #[Test]
    public function current_content_starts_visible_and_other_panels_are_hidden(): void
    {
        $html = $this->stepper('current="one"',
            '<x-bladewind::stepper.item name="one" label="One" />'
            .'<x-bladewind::stepper.item name="two" label="Two" />'
            .'<x-bladewind::stepper.content name="one">First panel</x-bladewind::stepper.content>'
            .'<x-bladewind::stepper.content name="two" has-border="false" class="custom-panel">Second panel</x-bladewind::stepper.content>'
        );

        $this->assertElementCount($html, '//*[@data-bw-stepper-panel]', 2);
        $this->assertAttribute($html, '//*[@data-bw-stepper-panel="one"]', 'hidden', null);
        $this->assertAttribute($html, '//*[@data-bw-stepper-panel="one"]', 'aria-hidden', 'false');
        $this->assertAttribute($html, '//*[@data-bw-stepper-panel="two"]', 'hidden', '');
        $this->assertAttribute($html, '//*[@data-bw-stepper-panel="two"]', 'inert', '');
        $this->assertAttribute($html, '//*[@data-bw-stepper-panel="one"]', 'role', 'region');
        $this->assertHasClasses($html, '//*[@data-bw-stepper-panel="two"]', ['bw-stepper-panel', 'custom-panel']);
        $this->assertMissingClasses($html, '//*[@data-bw-stepper-panel="one"]', ['bw-stepper-panel-borderless']);
        $this->assertHasClasses($html, '//*[@data-bw-stepper-panel="two"]', ['bw-stepper-panel-borderless']);
        $this->assertAttribute($html, '//*[@data-bw-stepper-panel="two"]', 'has-border', null);
    }

    #[Test]
    public function indicator_only_and_multiple_stepper_usage_render_independently(): void
    {
        $first = $this->stepper('name="first" current="account"');
        $second = $this->stepper('name="second" current="profile"');

        $this->assertStringNotContainsString('data-bw-stepper-panel', $first);
        $this->assertAttribute($first.$second, '//nav[1]', 'data-name', 'first');
        $this->assertAttribute($first.$second, '//nav[2]', 'data-name', 'second');
    }

    #[Test]
    public function labels_descriptions_names_and_attributes_are_escaped(): void
    {
        $html = $this->render(
            '<x-bladewind::stepper :name="$name" :aria-label="$aria"><x-bladewind::stepper.item :name="$step" :label="$label" :description="$description" /></x-bladewind::stepper>',
            [
                'name' => 'setup" onmouseover="bad',
                'aria' => 'Setup <unsafe>',
                'step' => 'profile" onclick="bad',
                'label' => '<script>bad</script>',
                'description' => '<img src=x onerror=bad>',
            ]
        );

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;script&gt;bad&lt;/script&gt;', $html);
        $this->assertAttribute($html, '//nav', 'aria-label', 'Setup <unsafe>');
        $this->assertAttribute($html, '//button', 'data-bw-stepper-step', 'profile" onclick="bad');
    }

    #[Test]
    public function configuration_defaults_are_applied(): void
    {
        config()->set('bladewind.stepper.orientation', 'vertical');
        config()->set('bladewind.stepper.linear', false);
        config()->set('bladewind.stepper.style', 'line');

        $html = $this->stepper();

        $this->assertHasClasses($html, '//nav', ['bw-stepper-vertical']);
        $this->assertHasClasses($html, '//nav', ['bw-stepper-style-line']);
        $this->assertAttribute($html, '//nav', 'data-linear', 'false');
    }
}
