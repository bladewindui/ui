<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CommandPaletteTest extends TestCase
{
    use RendersComponents;

    private function palette(string $attributes = '', string $children = ''): string
    {
        $children = $children ?: '<x-bladewind::command-palette.group name="navigate" label="Navigate">'
            .'<x-bladewind::command-palette.item name="dashboard" label="Dashboard" href="/dashboard" icon="home" />'
            .'<x-bladewind::command-palette.item name="orders" label="Orders" description="Review orders" />'
            .'</x-bladewind::command-palette.group>';

        return $this->render('<x-bladewind::command-palette '.$attributes.'>'.$children.'</x-bladewind::command-palette>');
    }

    #[Test]
    public function it_renders_a_named_hidden_dialog_with_a_searchable_listbox(): void
    {
        $html = $this->palette('name="app-commands" label="Command palette"');

        $this->assertElementCount($html, '//*[@data-bw-command-palette]', 1);
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'data-name', 'app-commands');
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'role', 'dialog');
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'aria-modal', 'true');
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'hidden', '');
        $this->assertElementCount($html, '//*[@data-bw-command-palette]//input[@role="combobox"]', 1);
        $this->assertElementCount($html, '//*[@data-bw-command-palette]//*[@role="listbox"]', 1);
    }

    #[Test]
    public function open_prop_renders_the_dialog_visible_and_unhidden(): void
    {
        $html = $this->palette('open="true"');

        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'data-state', 'open');
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'aria-hidden', 'false');
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'hidden', null);
    }

    #[Test]
    public function groups_and_items_expose_listbox_semantics(): void
    {
        $html = $this->palette();

        $this->assertElementCount($html, '//*[@data-bw-command-palette-group]', 1);
        $this->assertAttribute($html, '//*[@data-bw-command-palette-group]', 'role', 'group');
        $this->assertElementCount($html, '//*[@data-bw-command-palette-item]', 2);
        $this->assertAttribute($html, '//*[@data-item-name="dashboard"]', 'role', 'option');
        $this->assertAttribute($html, '//*[@data-item-name="dashboard"]', 'href', '/dashboard');
        $this->assertAttribute($html, '//*[@data-item-name="orders"]', 'role', 'option');
        $this->assertStringContainsString('Review orders', $html);
    }

    #[Test]
    public function links_buttons_and_disabled_items_use_the_correct_tag(): void
    {
        $html = $this->palette('', ''
            .'<x-bladewind::command-palette.item name="link" label="Link" href="/link" />'
            .'<x-bladewind::command-palette.item name="action" label="Action" />'
            .'<x-bladewind::command-palette.item name="locked" label="Locked" disabled="true" />'
            .'<x-bladewind::command-palette.item name="docs" label="Docs" href="https://example.com" external="true" />'
        );

        $this->assertElementCount($html, '//*[@data-item-name="link" and name()="a"]', 1);
        $this->assertElementCount($html, '//*[@data-item-name="action" and name()="button" and @type="button"]', 1);
        $this->assertElementCount($html, '//*[@data-item-name="locked" and name()="div"]', 1);
        $this->assertAttribute($html, '//*[@data-item-name="locked"]', 'aria-disabled', 'true');
        $this->assertAttribute($html, '//*[@data-item-name="docs"]', 'target', '_blank');
        $this->assertAttribute($html, '//*[@data-item-name="docs"]', 'rel', 'noopener noreferrer');
    }

    #[Test]
    public function shortcuts_render_as_individual_keys_and_feed_search_keywords(): void
    {
        $html = $this->palette('', '<x-bladewind::command-palette.item name="new" label="Create order" shortcut="Ctrl+N" keywords="add new" />');

        $this->assertElementCount($html, '//*[@data-item-name="new"]//kbd', 2);
        $this->assertAttribute($html, '//*[@data-item-name="new"]', 'data-keywords', 'create order  add new');
    }

    #[Test]
    public function icons_and_custom_slot_content_render(): void
    {
        $html = $this->palette('', ''
            .'<x-bladewind::command-palette.item name="dash" label="Dashboard" icon="home" />'
            .'<x-bladewind::command-palette.item name="custom" label="Custom"><strong>Custom content</strong></x-bladewind::command-palette.item>'
        );

        $this->assertElementCount($html, '//*[@data-item-name="dash"]//*[name()="svg"]', 1);
        $this->assertElementCount($html, '//*[@data-item-name="custom"]//strong', 1);
    }

    #[Test]
    public function shortcut_size_and_dismissal_options_are_forwarded(): void
    {
        $html = $this->palette('name="app" shortcut="mod+p" size="large" close-on-select="false" backdrop-can-close="false" escape-can-close="false" loading="true" class="custom-palette" data-test="palette"');

        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'data-shortcut', 'mod+p');
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'data-close-on-select', 'false');
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'data-backdrop-can-close', 'false');
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'data-escape-can-close', 'false');
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'data-loading', 'true');
        $this->assertHasClasses($html, '//*[@data-bw-command-palette]', ['bw-command-palette-large', 'custom-palette']);
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'data-test', 'palette');
    }

    #[Test]
    public function multiple_named_palettes_produce_unique_ids(): void
    {
        $first = $this->palette('name="first"');
        $second = $this->palette('name="second"');
        preg_match_all('/id="([^"]+)"/', $first.$second, $matches);

        $this->assertSame($matches[1], array_values(array_unique($matches[1])));
        $this->assertAttribute($first, '//*[@data-bw-command-palette]', 'data-name', 'first');
        $this->assertAttribute($second, '//*[@data-bw-command-palette]', 'data-name', 'second');
    }

    #[Test]
    public function labels_names_urls_descriptions_and_attributes_are_escaped(): void
    {
        $html = $this->render(
            '<x-bladewind::command-palette :name="$name" :label="$label"><x-bladewind::command-palette.item :name="$item" :label="$itemLabel" :href="$href" :description="$description" data-note="&lt;safe&gt;" /></x-bladewind::command-palette>',
            [
                'name' => 'app" onmouseover="bad',
                'label' => 'Main <unsafe>',
                'item' => 'item" onclick="bad',
                'itemLabel' => '<script>bad</script>',
                'href' => '/x?y=" onmouseover="bad',
                'description' => '<img src=x onerror=bad>',
            ]
        );

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'aria-label', 'Main <unsafe>');
        $this->assertAttribute($html, '//*[@data-bw-command-palette-item]', 'data-note', '<safe>');
    }

    #[Test]
    public function configuration_defaults_are_applied(): void
    {
        config()->set('bladewind.command_palette.shortcut', 'mod+p');
        config()->set('bladewind.command_palette.size', 'small');
        config()->set('bladewind.command_palette.close_on_select', false);

        $html = $this->palette();

        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'data-shortcut', 'mod+p');
        $this->assertHasClasses($html, '//*[@data-bw-command-palette]', ['bw-command-palette-small']);
        $this->assertAttribute($html, '//*[@data-bw-command-palette]', 'data-close-on-select', 'false');
    }
}
