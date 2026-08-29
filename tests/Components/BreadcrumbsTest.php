<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class BreadcrumbsTest extends TestCase
{
    use RendersComponents;

    private function trail(string $attributes = '', string $items = ''): string
    {
        $items = $items ?: '<x-bladewind::breadcrumbs.item href="/">Home</x-bladewind::breadcrumbs.item>'
            .'<x-bladewind::breadcrumbs.item current>Customers</x-bladewind::breadcrumbs.item>';

        return $this->render('<x-bladewind::breadcrumbs '.$attributes.'>'.$items.'</x-bladewind::breadcrumbs>');
    }

    #[Test]
    public function it_renders_semantic_navigation_and_an_ordered_list(): void
    {
        $html = $this->trail();

        $this->assertElementCount($html, '//nav/ol', 1);
        $this->assertAttribute($html, '//nav', 'aria-label', 'Breadcrumb');
        $this->assertElementCount($html, '//nav/ol/li[contains(@class, "bw-breadcrumb-item")]', 2);
    }

    #[Test]
    public function it_renders_linked_and_unlinked_items(): void
    {
        $html = $this->trail();

        $this->assertAttribute($html, '//li[contains(@class, "bw-breadcrumb-item")][1]/a', 'href', '/');
        $this->assertElementCount($html, '//li[contains(@class, "bw-breadcrumb-item")][2]/span[contains(@class, "bw-breadcrumb-link")]', 1);
        $this->assertNoElement($html, '//li[contains(@class, "bw-breadcrumb-item")][2]/a');
    }

    #[Test]
    public function a_current_item_has_page_semantics_and_is_only_linked_when_requested(): void
    {
        $plain = $this->trail();
        $linked = $this->trail('', '<x-bladewind::breadcrumbs.item href="/current" current>Current</x-bladewind::breadcrumbs.item>');

        $this->assertAttribute($plain, '//*[@aria-current="page"]', 'aria-current', 'page');
        $this->assertNoElement($plain, '//*[@aria-current="page"][self::a]');
        $this->assertAttribute($linked, '//a[@aria-current="page"]', 'href', '/current');
    }

    #[Test]
    public function multiple_items_keep_every_destination_in_document_order(): void
    {
        $items = '<x-bladewind::breadcrumbs.item href="/">Home</x-bladewind::breadcrumbs.item>'
            .'<x-bladewind::breadcrumbs.item href="/customers">Customers</x-bladewind::breadcrumbs.item>'
            .'<x-bladewind::breadcrumbs.item href="/customers/42">Ada</x-bladewind::breadcrumbs.item>'
            .'<x-bladewind::breadcrumbs.item current>Invoices</x-bladewind::breadcrumbs.item>';
        $html = $this->trail('', $items);

        $this->assertElementCount($html, '//a', 3);
        $this->assertElementCount($html, '//li[contains(@class, "bw-breadcrumb-item")]', 4);
        $this->assertStringContainsString('Home', $html);
        $this->assertStringContainsString('Invoices', $html);
    }

    #[Test]
    public function it_uses_chevrons_by_default(): void
    {
        $html = $this->trail();

        $this->assertElementCount($html, '//*[contains(@class, "bw-breadcrumb-separator")]//*[name()="svg"]', 3);
    }

    #[Test]
    #[DataProvider('separatorProvider')]
    public function it_supports_the_named_and_custom_separators(string $separator, string $expected): void
    {
        $html = $this->trail('separator="'.$separator.'"');

        $this->assertStringContainsString($expected, $html);
    }

    public static function separatorProvider(): array
    {
        return [
            'slash' => ['slash', '/'],
            'dot' => ['dot', '&bull;'],
            'custom' => ['→', '→'],
        ];
    }

    #[Test]
    public function separators_are_hidden_from_assistive_technology(): void
    {
        $html = $this->trail('separator="slash"');

        $this->assertElementCount($html, '//*[contains(@class, "bw-breadcrumb-separator") and @aria-hidden="true"]', 3);
        $this->assertAttribute($html, '//li[contains(@class, "bw-breadcrumb-overflow-marker")]', 'aria-hidden', 'true');
    }

    #[Test]
    public function item_icons_use_the_public_icon_component(): void
    {
        $html = $this->trail('', '<x-bladewind::breadcrumbs.item href="/" icon="home" icon-type="solid">Home</x-bladewind::breadcrumbs.item>');

        $this->assertElementCount($html, '//a//*[name()="svg" and contains(@class, "bw-breadcrumb-icon")]', 1);
        $this->assertStringContainsString('Home', $html);
    }

    #[Test]
    public function item_icons_accept_the_public_icon_directory_contract(): void
    {
        $html = $this->trail('', '<x-bladewind::breadcrumbs.item href="/" icon="home" icon-dir="vendor/bladewind/icons/outline">Home</x-bladewind::breadcrumbs.item>');

        $this->assertElementCount($html, '//a//*[name()="svg" and contains(@class, "bw-breadcrumb-icon")]', 1);
        $this->assertStringNotContainsString('icon-dir=', $html);
    }

    #[Test]
    public function container_and_item_attributes_are_forwarded_without_leaking_props(): void
    {
        $html = $this->trail(
            'aria-label="Checkout path" class="mt-6" data-testid="crumbs"',
            '<x-bladewind::breadcrumbs.item href="/orders" class="font-bold" rel="up" data-id="orders">Orders</x-bladewind::breadcrumbs.item>'
        );

        $this->assertAttribute($html, '//nav', 'aria-label', 'Checkout path');
        $this->assertAttribute($html, '//nav', 'data-testid', 'crumbs');
        $this->assertHasClasses($html, '//nav', ['bw-breadcrumbs', 'mt-6']);
        $this->assertAttribute($html, '//a', 'rel', 'up');
        $this->assertAttribute($html, '//a', 'data-id', 'orders');
        $this->assertHasClasses($html, '//a', ['font-bold', 'bw-breadcrumb-link']);
        $this->assertStringNotContainsString('separator=', $html);
        $this->assertStringNotContainsString('collapse=', $html);
    }

    #[Test]
    #[DataProvider('sizeProvider')]
    public function it_supports_the_public_size_scale(string $size, string $class): void
    {
        $html = $this->trail('size="'.$size.'"');

        $this->assertHasClasses($html, '//nav', [$class]);
    }

    public static function sizeProvider(): array
    {
        return [
            'tiny' => ['tiny', 'text-xs'],
            'small' => ['small', 'sm:text-sm'],
            'regular' => ['regular', 'text-sm'],
            'medium' => ['medium', 'text-base'],
            'big' => ['big', 'text-lg'],
            'large' => ['large', 'text-lg'],
        ];
    }

    #[Test]
    public function responsive_collapse_is_enabled_by_default_and_can_be_disabled(): void
    {
        $collapsed = $this->trail();
        $expanded = $this->trail('collapse="false"');

        $this->assertHasClasses($collapsed, '//nav', ['collapsible']);
        $this->assertMissingClasses($expanded, '//nav', ['collapsible']);
        $this->assertElementCount($collapsed, '//li[contains(@class, "bw-breadcrumb-overflow-marker")]', 1);
    }

    #[Test]
    public function rtl_direction_and_separator_direction_are_present_in_output(): void
    {
        $html = $this->trail('dir="rtl"');

        $this->assertAttribute($html, '//nav', 'dir', 'rtl');
        $this->assertHasClasses($html, '//*[name()="svg" and contains(@class, "rtl:rotate-180")]', ['rtl:rotate-180']);
    }

    #[Test]
    public function labels_attributes_and_custom_separators_are_escaped(): void
    {
        $html = $this->render(
            '<x-bladewind::breadcrumbs :separator="$separator">'
            .'<x-bladewind::breadcrumbs.item :href="$href" :title="$title">{{ $label }}</x-bladewind::breadcrumbs.item>'
            .'</x-bladewind::breadcrumbs>',
            [
                'separator' => '<script>separator</script>',
                'href' => 'https://example.test/?q=" onclick="alert(1)',
                'title' => '" onmouseover="alert(1)',
                'label' => '<img src=x onerror=alert(1)>',
            ]
        );

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;script&gt;separator&lt;/script&gt;', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        $this->assertAttribute($html, '//a', 'href', 'https://example.test/?q=" onclick="alert(1)');
        $this->assertAttribute($html, '//a', 'title', '" onmouseover="alert(1)');
    }
}
