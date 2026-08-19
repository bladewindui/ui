<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * #597 — the DOM-structure half of accessibility, which is what a render test can
 * actually see. Keyboard behaviour lives in select.js and dropmenu.js.
 *
 * When this work started, one component in the whole library used an aria-
 * attribute and two used role=.
 */
class AccessibilityTest extends TestCase
{
    use RendersComponents;

    private const DATA = '[{"label":"Ghana","value":"GH"},{"label":"Kenya","value":"KE"}]';

    private function select(string $attributes = ''): string
    {
        return $this->render(
            '<x-bladewind::select name="country" :data="$data" '.$attributes.' />',
            ['data' => json_decode(self::DATA, true)]
        );
    }

    // ---- select ---------------------------------------------------------

    #[Test]
    public function the_select_trigger_is_the_combobox(): void
    {
        $html = $this->select();

        $trigger = $this->withClass('clickable');

        $this->assertAttribute($html, $trigger, 'role', 'combobox');
        $this->assertAttribute($html, $trigger, 'aria-haspopup', 'listbox');
        $this->assertAttribute($html, $trigger, 'aria-expanded', 'false');
        $this->assertAttribute($html, $trigger, 'tabindex', '0');
    }

    #[Test]
    public function the_trigger_points_at_the_listbox_it_controls(): void
    {
        $html = $this->select();

        $controls = $this->firstNode($html, $this->withClass('clickable'))->getAttribute('aria-controls');

        $this->assertNotSame('', $controls);
        $this->assertElementCount($html, '//*[@id="'.$controls.'"]', 1);
        $this->assertAttribute($html, '//*[@id="'.$controls.'"]', 'role', 'listbox');
    }

    #[Test]
    public function every_option_is_an_option(): void
    {
        $html = $this->select();

        $this->assertElementCount($html, '//*[@role="option"]', 3);
        $this->assertAttribute($html, '//div[@data-value="GH"]', 'aria-selected', 'false');
    }

    #[Test]
    public function a_preselected_option_says_so(): void
    {
        $html = $this->select('selected_value="KE"');

        $this->assertAttribute($html, '//div[@data-value="KE"]', 'aria-selected', 'true');
        $this->assertAttribute($html, '//div[@data-value="GH"]', 'aria-selected', 'false');
    }

    #[Test]
    public function the_unselectable_empty_state_option_is_marked_disabled(): void
    {
        $html = $this->select();

        $this->assertAttribute($html, $this->withClass('empty-state', 'div'), 'aria-disabled', 'true');
    }

    #[Test]
    public function a_multiple_select_advertises_it(): void
    {
        $html = $this->select('multiple="true"');

        $this->assertAttribute($html, '//*[@role="listbox"]', 'aria-multiselectable', 'true');
    }

    #[Test]
    public function required_and_disabled_reach_assistive_tech(): void
    {
        $this->assertAttribute($this->select('required="true"'), $this->withClass('clickable'), 'aria-required', 'true');
        $this->assertAttribute($this->select('disabled="true"'), $this->withClass('clickable'), 'aria-disabled', 'true');
    }

    #[Test]
    public function the_trigger_carries_an_accessible_name(): void
    {
        $labelled = $this->select('label="Country"');
        $placeheld = $this->select('placeholder="Pick a country"');

        $this->assertAttribute($labelled, $this->withClass('clickable'), 'aria-label', 'Country');
        $this->assertAttribute($placeheld, $this->withClass('clickable'), 'aria-label', 'Pick a country');
    }

    // ---- dropmenu -------------------------------------------------------

    #[Test]
    public function the_dropmenu_trigger_advertises_its_menu(): void
    {
        $html = $this->render(
            "<x-bladewind::dropmenu name=\"acts\" trigger_label=\"Actions\">\n<x-bladewind::dropmenu.item>Edit</x-bladewind::dropmenu.item>\n</x-bladewind::dropmenu>"
        );

        $trigger = $this->withClass('bw-trigger');

        $this->assertAttribute($html, $trigger, 'role', 'button');
        $this->assertAttribute($html, $trigger, 'aria-haspopup', 'menu');
        $this->assertAttribute($html, $trigger, 'aria-expanded', 'false');
        $this->assertAttribute($html, $trigger, 'aria-label', 'Actions');
        $this->assertAttribute($html, $trigger, 'tabindex', '0');
    }

    #[Test]
    public function the_dropmenu_panel_is_a_menu_of_menuitems(): void
    {
        $html = $this->render(
            "<x-bladewind::dropmenu name=\"acts\">\n<x-bladewind::dropmenu.item>Edit</x-bladewind::dropmenu.item>\n<x-bladewind::dropmenu.item>Delete</x-bladewind::dropmenu.item>\n</x-bladewind::dropmenu>"
        );

        $controls = $this->firstNode($html, $this->withClass('bw-trigger'))->getAttribute('aria-controls');

        $this->assertAttribute($html, '//*[@id="'.$controls.'"]', 'role', 'menu');
        $this->assertElementCount($html, '//*[@role="menuitem"]', 2);
    }

    // ---- live regions ---------------------------------------------------

    #[Test]
    #[DataProvider('alertProvider')]
    public function an_alert_announces_itself_according_to_severity(string $type, string $role, string $live): void
    {
        $html = $this->render('<x-bladewind::alert type="'.$type.'">Something</x-bladewind::alert>');

        $this->assertAttribute($html, $this->withClass('bw-alert'), 'role', $role);
        $this->assertAttribute($html, $this->withClass('bw-alert'), 'aria-live', $live);
    }

    public static function alertProvider(): array
    {
        return [
            'error interrupts' => ['error', 'alert', 'assertive'],
            'warning interrupts' => ['warning', 'alert', 'assertive'],
            'info is polite' => ['info', 'status', 'polite'],
            'success is polite' => ['success', 'status', 'polite'],
        ];
    }

    #[Test]
    public function the_notification_container_is_a_live_region(): void
    {
        $html = $this->render('<x-bladewind::notification />');

        $container = $this->withClass('bw-notification-container');

        $this->assertAttribute($html, $container, 'role', 'status');
        $this->assertAttribute($html, $container, 'aria-live', 'polite');
    }

    // ---- icon-only buttons ----------------------------------------------

    #[Test]
    public function an_icon_only_button_derives_a_name_from_its_title(): void
    {
        $html = $this->render('<x-bladewind::button.circle icon="trash" title="Delete order" />');

        $this->assertAttribute($html, '//button', 'aria-label', 'Delete order');
    }

    #[Test]
    public function a_button_with_text_needs_no_derived_label(): void
    {
        $html = $this->render('<x-bladewind::button>Save changes</x-bladewind::button>');

        $this->assertAttribute($html, '//button', 'aria-label', null);
    }

    #[Test]
    public function an_explicit_aria_label_is_never_overwritten(): void
    {
        $html = $this->render(
            '<x-bladewind::button.circle icon="trash" title="Delete order" aria-label="Remove this order" />'
        );

        $this->assertAttribute($html, '//button', 'aria-label', 'Remove this order');
    }
}
