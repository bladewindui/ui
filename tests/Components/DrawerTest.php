<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class DrawerTest extends TestCase
{
    use RendersComponents;

    private function drawer(): string
    {
        return $this->withClass('bw-drawer');
    }

    #[Test]
    public function it_renders_a_named_closed_drawer(): void
    {
        $html = $this->render('<x-bladewind::drawer name="customer details">Body</x-bladewind::drawer>');
        $this->assertAttribute($html, $this->drawer(), 'data-name', 'customer-details');
        $this->assertAttribute($html, $this->drawer(), 'data-state', 'closed');
        $this->assertAttribute($html, $this->drawer(), 'hidden', '');
        $this->assertStringContainsString('Body', $html);
    }

    public static function positions(): array
    {
        return array_combine(['left', 'right', 'top', 'bottom'], array_map(fn ($v) => [$v], ['left', 'right', 'top', 'bottom']));
    }

    #[Test]
    #[DataProvider('positions')]
    public function it_supports_each_physical_position(string $position): void
    {
        $html = $this->render('<x-bladewind::drawer name="d" position="'.$position.'">B</x-bladewind::drawer>');
        $this->assertAttribute($html, $this->drawer(), 'data-position', $position);
    }

    public static function sizes(): array
    {
        return array_combine(['tiny', 'small', 'medium', 'big', 'large', 'xl', 'omg'], array_map(fn ($v) => [$v], ['tiny', 'small', 'medium', 'big', 'large', 'xl', 'omg']));
    }

    #[Test]
    #[DataProvider('sizes')]
    public function it_supports_size_variants(string $size): void
    {
        $html = $this->render('<x-bladewind::drawer name="d" size="'.$size.'">B</x-bladewind::drawer>');
        $this->assertAttribute($html, $this->drawer(), 'data-size', $size);
    }

    #[Test]
    public function modal_and_non_modal_semantics_are_distinct(): void
    {
        $modal = $this->render('<x-bladewind::drawer name="m">B</x-bladewind::drawer>');
        $nonModal = $this->render('<x-bladewind::drawer name="n" modal="false">B</x-bladewind::drawer>');
        $this->assertAttribute($modal, $this->drawer(), 'role', 'dialog');
        $this->assertAttribute($modal, $this->drawer(), 'aria-modal', 'true');
        $this->assertElementCount($modal, $this->withClass('bw-drawer-backdrop'), 1);
        $this->assertAttribute($nonModal, $this->drawer(), 'role', 'region');
        $this->assertAttribute($nonModal, $this->drawer(), 'aria-modal', null);
        $this->assertNoElement($nonModal, $this->withClass('bw-drawer-backdrop'));
    }

    #[Test]
    public function title_and_description_create_an_accessible_name_and_description(): void
    {
        $html = $this->render('<x-bladewind::drawer name="d" :title="$title" description="Account summary">B</x-bladewind::drawer>', ['title' => 'Customer & details']);
        $this->assertAttribute($html, $this->drawer(), 'aria-labelledby', 'bw-d-title');
        $this->assertAttribute($html, $this->drawer(), 'aria-describedby', 'bw-d-description');
        $this->assertStringContainsString('Customer &amp; details', $html);
    }

    #[Test]
    public function an_explicit_aria_label_is_forwarded_with_other_attributes_and_classes(): void
    {
        $html = $this->render('<x-bladewind::drawer name="d" aria-label="Filters &amp; options" id="filters" class="custom">B</x-bladewind::drawer>');
        $this->assertAttribute($html, $this->drawer(), 'aria-label', 'Filters & options');
        $this->assertAttribute($html, $this->drawer(), 'id', 'filters');
        $this->assertHasClasses($html, $this->drawer(), ['bw-drawer', 'custom']);
    }

    #[Test]
    public function header_body_and_footer_slots_render_inside_the_internal_layout(): void
    {
        $html = $this->render("<x-bladewind::drawer name=\"d\">\n<x-slot:header>Custom header</x-slot:header>\n<x-slot:body>Custom body</x-slot:body>\n<x-slot:footer>Custom footer</x-slot:footer>\n</x-bladewind::drawer>");
        $this->assertStringContainsString('Custom header', $html);
        $this->assertStringContainsString('Custom body', $html);
        $this->assertStringContainsString('Custom footer', $html);
        $this->assertElementCount($html, $this->withClass('bw-drawer-header', 'header'), 1);
        $this->assertElementCount($html, $this->withClass('bw-drawer-body'), 1);
        $this->assertElementCount($html, $this->withClass('bw-drawer-footer', 'footer'), 1);
    }

    #[Test]
    public function close_button_is_shown_by_default_and_can_be_hidden(): void
    {
        $shown = $this->render('<x-bladewind::drawer name="d">B</x-bladewind::drawer>');
        $hidden = $this->render('<x-bladewind::drawer name="d" show_close_button="false">B</x-bladewind::drawer>');
        $this->assertAttribute($shown, $this->withClass('bw-drawer-close', 'button'), 'data-bw-drawer-close', 'd');
        $this->assertNoElement($hidden, $this->withClass('bw-drawer-close', 'button'));
    }

    #[Test]
    public function icon_contract_is_forwarded_to_both_header_and_close_icons(): void
    {
        $source = file_get_contents(__DIR__.'/../../packages/drawer/resources/views/components/drawer.blade.php');
        $this->assertStringContainsString(':type="$iconType"', $source);
        $this->assertStringContainsString(':dir="$iconDir"', $source);
        $html = $this->render('<x-bladewind::drawer name="d" icon="user" icon_type="solid" icon_dir="vendor/bladewind/icons/solid">B</x-bladewind::drawer>');
        $this->assertGreaterThanOrEqual(2, $this->query($html, '//svg')->length);
    }

    #[Test]
    public function behavior_flags_and_initial_open_state_are_exposed_safely(): void
    {
        $html = $this->render('<x-bladewind::drawer name="d" open="true" backdrop_can_close="false" escape_can_close="false">B</x-bladewind::drawer>');
        $this->assertAttribute($html, $this->drawer(), 'data-backdrop-can-close', 'false');
        $this->assertAttribute($html, $this->drawer(), 'data-escape-can-close', 'false');
        $this->assertAttribute($html, $this->drawer(), 'data-state', 'open');
        $this->assertAttribute($html, $this->drawer(), 'hidden', null);
        $this->assertAttribute($html, $this->drawer(), 'aria-hidden', 'false');
    }

    #[Test]
    public function labels_and_attributes_are_escaped(): void
    {
        $html = $this->render('<x-bladewind::drawer name="d" :title="$title" :close-label="$close" data-note="&lt;bad&gt;">B</x-bladewind::drawer>', ['title' => '<script>bad</script>', 'close' => 'Close "now"']);
        $this->assertStringNotContainsString('<script>bad</script>', $html);
        $this->assertAttribute($html, $this->withClass('bw-drawer-close', 'button'), 'aria-label', 'Close "now"');
        $this->assertAttribute($html, $this->drawer(), 'data-note', '<bad>');
    }

    #[Test]
    public function contained_defaults_to_false_and_can_be_turned_on(): void
    {
        $default = $this->render('<x-bladewind::drawer name="d">B</x-bladewind::drawer>');
        $this->assertAttribute($default, $this->drawer(), 'data-contained', 'false');

        $contained = $this->render('<x-bladewind::drawer name="d" contained="true">B</x-bladewind::drawer>');
        $this->assertAttribute($contained, $this->drawer(), 'data-contained', 'true');
    }
}
