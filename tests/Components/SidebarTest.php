<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SidebarTest extends TestCase
{
    use RendersComponents;

    private function sidebar(string $attributes = '', string $children = ''): string
    {
        $children = $children ?: '<x-bladewind::sidebar.group name="workspace" label="Workspace" icon="squares-2x2" expanded="true">'
            .'<x-bladewind::sidebar.item name="overview" label="Overview" href="/dashboard" icon="home" />'
            .'<x-bladewind::sidebar.item name="orders" label="Orders" href="/orders" badge="12" />'
            .'</x-bladewind::sidebar.group>';

        return $this->render('<x-bladewind::sidebar '.$attributes.'>'.$children.'</x-bladewind::sidebar>');
    }

    #[Test]
    public function it_renders_named_accessible_list_navigation(): void
    {
        $html = $this->sidebar('name="workspace-navigation" label="Workspace navigation"');

        $this->assertElementCount($html, '//*[@data-bw-sidebar]', 1);
        $this->assertAttribute($html, '//*[@data-bw-sidebar]', 'data-name', 'workspace-navigation');
        $this->assertAttribute($html, '//*[@data-bw-sidebar]//nav', 'aria-label', 'Workspace navigation');
        $this->assertElementCount($html, '//*[@data-bw-sidebar]//nav/ul/li', 1);
    }

    #[Test]
    public function header_navigation_and_footer_are_distinct_regions(): void
    {
        $html = $this->sidebar('', '<x-slot:header>Acme</x-slot:header>'
            .'<x-bladewind::sidebar.item name="home" label="Home" href="/" />'
            .'<x-slot:footer>Ama</x-slot:footer>');

        $this->assertElementCount($html, '//*[@data-bw-sidebar]/header', 1);
        $this->assertElementCount($html, '//*[@data-bw-sidebar]/nav', 1);
        $this->assertElementCount($html, '//*[@data-bw-sidebar]/footer', 1);
        $this->assertStringContainsString('Acme', $html);
        $this->assertStringContainsString('Ama', $html);
    }

    #[Test]
    public function mobile_uses_one_existing_drawer_and_does_not_duplicate_navigation(): void
    {
        $html = $this->sidebar('name="workspace" mobile="drawer" placement="right"');

        $this->assertElementCount($html, '//*[@data-bw-drawer]', 1);
        $this->assertAttribute($html, '//*[@data-bw-drawer]', 'data-name', 'workspace-mobile');
        $this->assertAttribute($html, '//*[@data-bw-drawer]', 'data-position', 'right');
        $this->assertElementCount($html, '//nav[contains(@class, "bw-sidebar-navigation")]', 1);
        $this->assertElementCount($html, '//*[@data-bw-sidebar-mobile-host="workspace"]', 1);
    }

    #[Test]
    public function mobile_can_be_disabled_without_rendering_a_drawer(): void
    {
        $html = $this->sidebar('mobile="none"');

        $this->assertElementCount($html, '//*[@data-bw-drawer]', 0);
        $this->assertAttribute($html, '//*[@data-bw-sidebar]', 'data-mobile', 'none');
    }

    #[Test]
    public function left_right_and_logical_placements_are_exposed(): void
    {
        foreach (['left', 'right', 'start', 'end'] as $placement) {
            $html = $this->sidebar('placement="'.$placement.'"');
            $this->assertAttribute($html, '//*[@data-bw-sidebar]', 'data-placement', $placement);
        }
    }

    #[Test]
    public function nested_groups_keep_semantic_lists_and_deep_items(): void
    {
        $html = $this->sidebar('',
            '<x-bladewind::sidebar.group name="settings" label="Settings">'
            .'<x-bladewind::sidebar.group name="security" label="Security">'
            .'<x-bladewind::sidebar.group name="access" label="Access">'
            .'<x-bladewind::sidebar.item name="roles" label="Roles" href="/roles" />'
            .'</x-bladewind::sidebar.group></x-bladewind::sidebar.group></x-bladewind::sidebar.group>'
        );

        $this->assertElementCount($html, '//*[@data-bw-sidebar-group]', 3);
        $this->assertElementCount($html, '//*[@data-item-name="roles"]', 1);
        $this->assertElementCount($html, '//*[@data-bw-sidebar-group]/div/ul', 3);
    }

    #[Test]
    public function expanded_collapsed_and_disabled_groups_have_correct_aria_state(): void
    {
        $html = $this->sidebar('',
            '<x-bladewind::sidebar.group name="open" label="Open" expanded="true"><x-bladewind::sidebar.item name="a" label="A" /></x-bladewind::sidebar.group>'
            .'<x-bladewind::sidebar.group name="closed" label="Closed"><x-bladewind::sidebar.item name="b" label="B" /></x-bladewind::sidebar.group>'
            .'<x-bladewind::sidebar.group name="disabled" label="Disabled" disabled="true"><x-bladewind::sidebar.item name="c" label="C" /></x-bladewind::sidebar.group>'
        );

        $this->assertAttribute($html, '//*[@data-group-name="open"]/*[@data-bw-sidebar-group-trigger]', 'aria-expanded', 'true');
        $this->assertAttribute($html, '//*[@data-group-name="closed"]/*[@data-bw-sidebar-group-trigger]', 'aria-expanded', 'false');
        $this->assertAttribute($html, '//*[@data-group-name="closed"]/*[contains(@class, "bw-sidebar-group-panel")]', 'hidden', '');
        $this->assertAttribute($html, '//*[@data-group-name="disabled"]/*[@data-bw-sidebar-group-trigger]', 'disabled', 'disabled');
    }

    #[Test]
    public function canonical_root_active_state_wins_and_expands_ancestors(): void
    {
        $html = $this->sidebar('active="orders"',
            '<x-bladewind::sidebar.group name="sales" label="Sales">'
            .'<x-bladewind::sidebar.item name="overview" label="Overview" href="/" active="true" />'
            .'<x-bladewind::sidebar.item name="orders" label="Orders" href="/orders" />'
            .'</x-bladewind::sidebar.group>'
        );

        $this->assertAttribute($html, '//*[@data-item-name="orders"]/*', 'aria-current', 'page');
        $this->assertAttribute($html, '//*[@data-item-name="overview"]/*', 'aria-current', null);
        $this->assertAttribute($html, '//*[@data-group-name="sales"]', 'data-expanded', 'true');
    }

    #[Test]
    public function explicit_item_active_state_is_available_when_root_active_is_omitted(): void
    {
        $html = $this->sidebar('', '<x-bladewind::sidebar.item name="home" label="Home" href="/" active="true" />');

        $this->assertAttribute($html, '//*[@data-item-name="home"]/*', 'aria-current', 'page');
        $this->assertAttribute($html, '//*[@data-item-name="home"]', 'data-initial-active', 'true');
    }

    #[Test]
    public function disabled_items_cannot_be_activated(): void
    {
        $html = $this->sidebar('active="locked"', '<x-bladewind::sidebar.item name="locked" label="Locked" href="/locked" disabled="true" />');

        $this->assertElementCount($html, '//*[@data-item-name="locked"]/span', 1);
        $this->assertAttribute($html, '//*[@data-item-name="locked"]/*', 'aria-disabled', 'true');
        $this->assertAttribute($html, '//*[@data-item-name="locked"]/*', 'href', null);
        $this->assertAttribute($html, '//*[@data-item-name="locked"]/*', 'aria-current', null);
    }

    #[Test]
    public function links_buttons_and_external_destinations_are_supported(): void
    {
        $html = $this->sidebar('',
            '<x-bladewind::sidebar.item name="link" label="Link" href="/link" />'
            .'<x-bladewind::sidebar.item name="action" label="Action" />'
            .'<x-bladewind::sidebar.item name="docs" label="Docs" href="https://example.com" external="true" />'
        );

        $this->assertElementCount($html, '//*[@data-item-name="link"]/a[@href="/link"]', 1);
        $this->assertElementCount($html, '//*[@data-item-name="action"]/button[@type="button"]', 1);
        $this->assertAttribute($html, '//*[@data-item-name="docs"]/a', 'target', '_blank');
        $this->assertAttribute($html, '//*[@data-item-name="docs"]/a', 'rel', 'noopener noreferrer');
    }

    #[Test]
    public function icons_descriptions_badges_and_custom_content_render(): void
    {
        $html = $this->sidebar('',
            '<x-bladewind::sidebar.item name="orders" label="Orders" icon="shopping-bag" icon-type="solid" icon-dir="vendor/bladewind/icons/solid" description="Review fulfilment" badge="12" badge-label="12 open orders" />'
            .'<x-bladewind::sidebar.item name="custom" label="Custom"><strong>Custom content</strong></x-bladewind::sidebar.item>'
        );

        $this->assertElementCount($html, '//*[@data-item-name="orders"]//*[name()="svg"]', 1);
        $this->assertStringContainsString('Review fulfilment', $html);
        $this->assertStringContainsString('12 open orders', $html);
        $this->assertElementCount($html, '//*[@data-item-name="custom"]//strong', 1);
        $this->assertStringNotContainsString('icon-dir=', $html);
    }

    #[Test]
    public function desktop_state_persistence_height_and_forwarded_attributes_are_exposed(): void
    {
        $html = $this->sidebar('name="admin" collapsible="true" collapsed="true" persist="true" persist-groups="true" storage-key="custom-key" height="content" class="custom-sidebar" data-test="sidebar"');

        $this->assertAttribute($html, '//*[@data-bw-sidebar]', 'data-state', 'collapsed');
        $this->assertAttribute($html, '//*[@data-bw-sidebar]', 'data-persist', 'true');
        $this->assertAttribute($html, '//*[@data-bw-sidebar]', 'data-persist-groups', 'true');
        $this->assertAttribute($html, '//*[@data-bw-sidebar]', 'data-storage-key', 'custom-key');
        $this->assertHasClasses($html, '//*[@data-bw-sidebar]', ['bw-sidebar-height-content', 'custom-sidebar']);
        $this->assertAttribute($html, '//*[@data-bw-sidebar]', 'data-test', 'sidebar');
    }

    #[Test]
    public function multiple_named_sidebars_produce_unique_group_ids(): void
    {
        $first = $this->sidebar('name="first"');
        $second = $this->sidebar('name="second"');
        preg_match_all('/id="([^"]+)"/', $first.$second, $matches);

        $this->assertSame($matches[1], array_values(array_unique($matches[1])));
        $this->assertAttribute($first, '//*[@data-bw-sidebar]', 'data-name', 'first');
        $this->assertAttribute($second, '//*[@data-bw-sidebar]', 'data-name', 'second');
    }

    #[Test]
    public function labels_names_urls_descriptions_badges_and_attributes_are_escaped(): void
    {
        $html = $this->render(
            '<x-bladewind::sidebar :name="$name" :label="$nav"><x-bladewind::sidebar.item :name="$item" :label="$label" :href="$href" :description="$description" :badge="$badge" data-note="&lt;safe&gt;" /></x-bladewind::sidebar>',
            [
                'name' => 'nav" onmouseover="bad',
                'nav' => 'Main <unsafe>',
                'item' => 'item" onclick="bad',
                'label' => '<script>bad</script>',
                'href' => '/orders?x=" onmouseover="bad',
                'description' => '<img src=x onerror=bad>',
                'badge' => '<b>12</b>',
            ]
        );

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertAttribute($html, '//*[@data-bw-sidebar]//nav', 'aria-label', 'Main <unsafe>');
        $this->assertAttribute($html, '//*[@data-bw-sidebar-item]/*', 'data-note', '<safe>');
    }

    #[Test]
    public function configuration_defaults_are_applied(): void
    {
        config()->set('bladewind.sidebar.placement', 'right');
        config()->set('bladewind.sidebar.collapsible', true);
        config()->set('bladewind.sidebar.collapsed', true);
        config()->set('bladewind.sidebar.close_on_navigate', false);

        $html = $this->sidebar();

        $this->assertAttribute($html, '//*[@data-bw-sidebar]', 'data-placement', 'right');
        $this->assertAttribute($html, '//*[@data-bw-sidebar]', 'data-state', 'collapsed');
        $this->assertAttribute($html, '//*[@data-bw-sidebar]', 'data-close-on-navigate', 'false');
    }
}
