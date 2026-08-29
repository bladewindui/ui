<?php

namespace Mkocansey\Bladewind\Tests\E2E;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Writes the pages the Playwright specs run against.
 *
 * They are generated from the real Blade templates rather than hand-written, so a
 * component change cannot leave the browser fixtures testing markup the library
 * no longer produces. Not part of the default suite — `npm run test:e2e` runs
 * this first, then Playwright.
 */
class BuildFixturesTest extends TestCase
{
    use RendersComponents;

    private const OUT = __DIR__.'/public';

    /**
     * asset() gives absolute URLs against testbench's host; the fixture server
     * serves the same files from its own root.
     */
    private function relative(string $html): string
    {
        return str_replace(['http://localhost/vendor/', 'https://localhost/vendor/'], '/vendor/', $html);
    }

    private function page(string $title, string $body, string $scripts = ''): string
    {
        return <<<HTML
        <!doctype html>
        <html>
        <head>
        <meta charset="utf-8">
        <title>{$title}</title>
        <link href="/vendor/bladewind/css/bladewind-ui.min.css" rel="stylesheet">
        <style>
          body { font-family: system-ui; padding: 24px; }
          /* the wrapper every wide table needs, and the one that eats popups:
             overflow-x auto silently computes overflow-y to auto as well */
          .scroll-wrapper { overflow-x: auto; border: 2px dashed #94a3b8; padding: 12px;
                            max-width: 520px; height: 150px; }
          /* the trigger sits near the wrapper's bottom edge and away from the
             left, so an opening popup has to overhang to be visible at all, and
             so horizontal movement is measurable rather than clamped to x=0 */
          .wide { min-width: 900px; padding: 90px 0 0 120px; }
        </style>
        <script src="/vendor/bladewind/js/helpers.js"></script>
        </head>
        <body>
        {$body}
        {$scripts}
        </body>
        </html>
        HTML;
    }

    #[Test]
    public function it_writes_the_popup_clipping_fixture(): void
    {
        $data = [
            ['label' => 'Ghana', 'value' => 'GH'],
            ['label' => 'Kenya', 'value' => 'KE'],
            ['label' => 'Nigeria', 'value' => 'NG'],
            ['label' => 'Togo', 'value' => 'TG'],
        ];

        $select = $this->render(
            '<x-bladewind::select name="country" :data="$d" placeholder="Pick a country" />',
            ['d' => $data]
        );

        $dropmenu = $this->render(
            "<x-bladewind::dropmenu name=\"acts\" trigger=\"ellipsis-vertical-icon\" trigger_label=\"Actions\">\n"
            ."<x-bladewind::dropmenu.item>Edit</x-bladewind::dropmenu.item>\n"
            ."<x-bladewind::dropmenu.item>Duplicate</x-bladewind::dropmenu.item>\n"
            ."<x-bladewind::dropmenu.item>Delete</x-bladewind::dropmenu.item>\n"
            ."</x-bladewind::dropmenu>"
        );

        $popover = $this->render(
            '<x-bladewind::popover name="pop" trigger="Details" position="bottom">'
            .'Some detail that needs room to breathe.</x-bladewind::popover>'
        );

        $body = <<<HTML
        <h1>Popups inside a scrolling ancestor</h1>

        <div class="scroll-wrapper" id="select-wrapper">
          <div class="wide"><div style="width:280px">{$select}</div></div>
        </div>

        <div class="scroll-wrapper" id="dropmenu-wrapper" style="margin-top:24px">
          <div class="wide">{$dropmenu}</div>
        </div>

        <div class="scroll-wrapper" id="popover-wrapper" style="margin-top:24px">
          <div class="wide">{$popover}</div>
        </div>
        HTML;

        file_put_contents(
            self::OUT.'/popup-clipping.html',
            $this->relative($this->page('Popup clipping', $body))
        );

        $this->assertFileExists(self::OUT.'/popup-clipping.html');
    }

    /**
     * Tooltips get their own fixture because the trigger is hovered rather than
     * clicked, and because the table's action icons carry data-tooltip directly
     * — the same attribute, reached without the tooltip component.
     */
    #[Test]
    public function it_writes_the_tooltip_clipping_fixture(): void
    {
        $tooltip = $this->render(
            '<x-bladewind::tooltip text="Archive this order" position="bottom">'
            .'<x-bladewind::button size="tiny">Archive</x-bladewind::button>'
            .'</x-bladewind::tooltip>'
        );

        $icons = $this->render(
            '<x-bladewind::table name="orders" :columns="[\'ref\']" :rows="$rows" :action_icons="$icons" />',
            [
                'rows' => [['ref' => 'ORD-1', 'id' => 1]],
                'icons' => [['icon' => 'pencil', 'tip' => 'Edit this order, change its line items, adjust the delivery window, and leave a note for the warehouse team before it is dispatched, or hand it back to the account manager if the customer has asked for changes that cannot be made here', 'click' => 'editOrder']],
            ]
        );

        $body = <<<HTML
        <h1>Tooltips inside a scrolling ancestor</h1>

        <div class="scroll-wrapper" id="tooltip-wrapper">
          <div class="wide">{$tooltip}</div>
        </div>

        <div class="scroll-wrapper" id="icons-wrapper" style="margin-top:24px">
          <div class="wide" style="padding-top:0">{$icons}</div>
        </div>
        HTML;

        file_put_contents(
            self::OUT.'/tooltip-clipping.html',
            $this->relative($this->page('Tooltip clipping', $body))
        );

        $this->assertFileExists(self::OUT.'/tooltip-clipping.html');
    }

    /**
     * #608 moved every library-owned handler from an inline on* attribute onto a
     * delegated listener. Nothing in PHP can tell whether clicking still does
     * anything, so the behaviours get a fixture of their own.
     */
    #[Test]
    public function it_writes_the_delegated_handler_fixture(): void
    {
        $accordion = $this->render(
            "<x-bladewind::accordion name=\"grp\">\n"
            ."<x-bladewind::accordion.item title=\"First\">first body</x-bladewind::accordion.item>\n"
            ."</x-bladewind::accordion>"
        );

        $tabs = $this->render(
            "<x-bladewind::tab name=\"t\">\n"
            ."<x-slot:headings>\n"
            ."<x-bladewind::tab.heading name=\"one\" label=\"One\" active=\"true\" />\n"
            ."<x-bladewind::tab.heading name=\"two\" label=\"Two\" />\n"
            ."</x-slot:headings>\n"
            ."<x-bladewind::tab.content name=\"one\" active=\"true\">panel one</x-bladewind::tab.content>\n"
            ."<x-bladewind::tab.content name=\"two\">panel two</x-bladewind::tab.content>\n"
            ."</x-bladewind::tab>"
        );

        $table = $this->render(
            '<x-bladewind::table name="orders" :columns="$cols" :rows="$rows" />',
            [
                'cols' => [['key' => 'ref', 'label' => 'Ref', 'sortable' => true]],
                'rows' => [['ref' => 'c'], ['ref' => 'a'], ['ref' => 'b']],
            ]
        );

        $rating = $this->render('<x-bladewind::rating name="r" rating="1" clickable="true" />');
        $tag = $this->render('<x-bladewind::tag label="Closable" can_close="true" />');
        $alert = $this->render('<x-bladewind::alert type="info" show_close_icon="true">Dismiss me</x-bladewind::alert>');

        $body = <<<HTML
        <h1>Delegated handlers</h1>
        <section id="accordion">{$accordion}</section>
        <section id="tabs">{$tabs}</section>
        <section id="table">{$table}</section>
        <section id="rating">{$rating}</section>
        <section id="tag">{$tag}</section>
        <section id="alert">{$alert}</section>
        HTML;

        file_put_contents(
            self::OUT.'/delegated-handlers.html',
            $this->relative($this->page('Delegated handlers', $body))
        );

        $this->assertFileExists(self::OUT.'/delegated-handlers.html');
    }

    /**
     * Input clearing used to add 16px to the flex line, stretching the attached
     * button below the input instead of leaving the two controls level.
     */
    #[Test]
    public function it_writes_the_input_group_fixture(): void
    {
        $matched = $this->render(
            '<x-bladewind::input-group>'
            .'<x-bladewind::input id="matched-input" name="orders" size="medium" placeholder="Search orders" />'
            .'<x-bladewind::button id="matched-button" size="medium">Search</x-bladewind::button>'
            .'</x-bladewind::input-group>'
        );

        $mismatched = $this->render(
            '<x-bladewind::input-group>'
            .'<x-bladewind::input id="grouped-input" name="grouped" size="medium" placeholder="Medium input" />'
            .'<x-bladewind::button id="grouped-button" size="big">Big button</x-bladewind::button>'
            .'</x-bladewind::input-group>'
        );

        $referenceInput = $this->render(
            '<x-bladewind::input id="reference-input" name="reference" size="medium" add_clearing="false" />'
        );
        $referenceButton = $this->render(
            '<x-bladewind::button id="reference-button" size="big">Big button</x-bladewind::button>'
        );

        $body = '<div style="width:790px">'.$matched.$mismatched
            .'<div style="margin-top:24px">'.$referenceInput.$referenceButton.'</div></div>';

        file_put_contents(
            self::OUT.'/input-group.html',
            $this->relative($this->page('Input group', $body))
        );

        $this->assertFileExists(self::OUT.'/input-group.html');
    }

    #[Test]
    public function it_writes_the_breadcrumbs_fixture(): void
    {
        $trail = $this->render(
            '<x-bladewind::breadcrumbs aria-label="Order path">'
            .'<x-bladewind::breadcrumbs.item href="/home" icon="home">Home</x-bladewind::breadcrumbs.item>'
            .'<x-bladewind::breadcrumbs.item href="/sales">Sales</x-bladewind::breadcrumbs.item>'
            .'<x-bladewind::breadcrumbs.item href="/sales/orders">Orders and fulfilment</x-bladewind::breadcrumbs.item>'
            .'<x-bladewind::breadcrumbs.item href="/sales/orders/1042">Order 1042 with a deliberately long customer reference</x-bladewind::breadcrumbs.item>'
            .'<x-bladewind::breadcrumbs.item current>Shipment details</x-bladewind::breadcrumbs.item>'
            .'</x-bladewind::breadcrumbs>'
        );

        $rtl = $this->render(
            '<x-bladewind::breadcrumbs aria-label="RTL path" dir="rtl" separator="slash">'
            .'<x-bladewind::breadcrumbs.item href="/ar">الرئيسية</x-bladewind::breadcrumbs.item>'
            .'<x-bladewind::breadcrumbs.item href="/ar/orders">الطلبات</x-bladewind::breadcrumbs.item>'
            .'<x-bladewind::breadcrumbs.item current>التفاصيل</x-bladewind::breadcrumbs.item>'
            .'</x-bladewind::breadcrumbs>'
        );

        $body = '<section id="light" style="max-width:760px">'.$trail.'</section>'
            .'<section id="dark" class="dark" style="max-width:760px;background:#101114;padding:24px;margin-top:30px">'.$trail.'</section>'
            .'<section id="rtl" style="max-width:760px;margin-top:30px">'.$rtl.'</section>';

        file_put_contents(
            self::OUT.'/breadcrumbs.html',
            $this->relative($this->page('Breadcrumbs', $body))
        );

        $this->assertFileExists(self::OUT.'/breadcrumbs.html');
    }

    #[Test]
    public function it_writes_the_drawer_fixture(): void
    {
        $drawers = '';
        foreach (['left', 'right', 'top', 'bottom'] as $position) {
            $drawers .= $this->render(
                '<x-bladewind::drawer name="'.$position.'" title="'.ucfirst($position).' drawer" position="'.$position.'">'
                .'<button type="button" data-first>First</button><button type="button" data-last>Last</button>'
                .'</x-bladewind::drawer>'
            );
        }
        $drawers .= $this->render(
            '<x-bladewind::drawer name="nonmodal" title="Non-modal" modal="false">'
            .'<button type="button">Inside</button></x-bladewind::drawer>'
        );
        $drawers .= $this->render(
            '<x-bladewind::drawer name="locked" title="Locked" backdrop_can_close="false" escape_can_close="false">Locked</x-bladewind::drawer>'
        );

        $buttons = '<button id="background">Background</button>';
        foreach (['left', 'right', 'top', 'bottom', 'nonmodal', 'locked'] as $name) {
            $buttons .= '<button type="button" data-open="'.$name.'">Open '.$name.'</button>';
        }
        $scripts = <<<'HTML'
        <script>
          document.querySelectorAll('[data-open]').forEach((button) => button.addEventListener('click', () => showDrawer(button.dataset.open)));
        </script>
        HTML;

        file_put_contents(
            self::OUT.'/drawer.html',
            $this->relative($this->page('Drawer', $buttons.$drawers, $scripts))
        );

        $this->assertFileExists(self::OUT.'/drawer.html');
    }

    #[Test]
    public function it_writes_the_stepper_fixture(): void
    {
        $linear = $this->render(
            '<x-bladewind::stepper name="setup" current="profile" aria-label="Account setup">'
            .'<x-bladewind::stepper.item name="account" label="Account details with a deliberately long label" state="complete" />'
            .'<x-bladewind::stepper.item name="profile" label="Profile" description="Personal details" state="upcoming" />'
            .'<x-bladewind::stepper.item name="security" label="Security" />'
            .'<x-bladewind::stepper.item name="optional" label="Optional" disabled="true" />'
            .'<x-bladewind::stepper.content name="account"><input aria-label="Account field"></x-bladewind::stepper.content>'
            .'<x-bladewind::stepper.content name="profile" has-border="false"><input aria-label="Profile field"></x-bladewind::stepper.content>'
            .'<x-bladewind::stepper.content name="security"><input aria-label="Security field"></x-bladewind::stepper.content>'
            .'</x-bladewind::stepper>'
        );
        $styles = collect(['chevrons', 'bars', 'line'])->map(fn (string $style) => $this->render(
            '<x-bladewind::stepper name="style-'.$style.'" current="two" style="'.$style.'" linear="false" aria-label="'.$style.' style">'
            .'<x-bladewind::stepper.item name="one" label="First stage" state="complete" />'
            .'<x-bladewind::stepper.item name="two" label="Second stage" />'
            .'<x-bladewind::stepper.item name="three" label="Third stage" />'
            .'<x-bladewind::stepper.content name="one">First panel</x-bladewind::stepper.content>'
            .'<x-bladewind::stepper.content name="two">Second panel</x-bladewind::stepper.content>'
            .'<x-bladewind::stepper.content name="three">Third panel</x-bladewind::stepper.content>'
            .'</x-bladewind::stepper>'
        ))->implode('');
        $nonlinear = $this->render(
            '<x-bladewind::stepper name="free" current="one" linear="false" orientation="vertical" aria-label="Free workflow">'
            .'<x-bladewind::stepper.item name="one" label="One" />'
            .'<x-bladewind::stepper.item name="two" label="Two" state="error" />'
            .'<x-bladewind::stepper.item name="three" label="Three" />'
            .'<x-bladewind::stepper.content name="one">Panel one</x-bladewind::stepper.content>'
            .'<x-bladewind::stepper.content name="two">Panel two</x-bladewind::stepper.content>'
            .'<x-bladewind::stepper.content name="three">Panel three</x-bladewind::stepper.content>'
            .'</x-bladewind::stepper>'
        );
        $rtl = $this->render(
            '<x-bladewind::stepper name="rtl" current="a" dir="rtl" linear="false" aria-label="RTL workflow">'
            .'<x-bladewind::stepper.item name="a" label="الأول" />'
            .'<x-bladewind::stepper.item name="b" label="الثاني" />'
            .'<x-bladewind::stepper.item name="c" label="الثالث" />'
            .'</x-bladewind::stepper>'
        );

        $scripts = <<<'HTML'
        <script>
          window.blockSecurity = false;
          window.stepperEvents = [];
          document.querySelector('[data-name="setup"]').addEventListener('bladewind:stepper:before-change', (event) => {
            window.stepperEvents.push({type: event.type, ...event.detail});
            if (window.blockSecurity && event.detail.nextStep === 'security') event.preventDefault();
          });
          document.querySelector('[data-name="setup"]').addEventListener('bladewind:stepper:changed', (event) => window.stepperEvents.push({type: event.type, ...event.detail}));
          document.querySelector('[data-name="setup"]').addEventListener('bladewind:stepper:complete', (event) => window.stepperEvents.push({type: event.type, ...event.detail}));
        </script>
        HTML;

        $body = '<section id="linear" style="max-width:720px">'.$linear.'</section>'
            .'<section id="nonlinear" class="dark" style="max-width:420px;background:#101114;padding:24px;margin-top:30px">'.$nonlinear.'</section>'
            .'<section id="rtl" style="max-width:620px;margin-top:30px">'.$rtl.'</section>'
            .'<section id="styles" style="max-width:720px;margin-top:30px">'.$styles.'</section>';

        file_put_contents(
            self::OUT.'/stepper.html',
            $this->relative($this->page('Stepper', $body, $scripts))
        );

        $this->assertFileExists(self::OUT.'/stepper.html');
    }

    #[Test]
    public function it_writes_the_sidebar_fixture(): void
    {
        $largeItems = '';
        foreach (range(1, 28) as $index) {
            $largeItems .= '<x-bladewind::sidebar.item name="report-'.$index.'" label="Regional operations report '.$index.' with a deliberately long destination label" href="#report-'.$index.'" icon="document-text" />';
        }

        $primary = $this->render(
            '<x-bladewind::sidebar name="workspace" label="Workspace navigation" active="orders" collapsible="true" persist="true" persist-groups="true" storage-key="sidebar-e2e" class="e2e-sidebar">'
            .'<x-slot:header><strong>Acme Workspace</strong></x-slot:header>'
            .'<x-bladewind::sidebar.group name="workspace" label="Workspace" icon="squares-2x2">'
            .'<x-bladewind::sidebar.item name="overview" label="Overview" href="#overview" icon="home" active="true" />'
            .'<x-bladewind::sidebar.item name="orders" label="Orders" href="#orders" icon="shopping-bag" description="Review fulfilment" badge="12" />'
            .'<x-bladewind::sidebar.group name="customers" label="Customers" icon="users">'
            .'<x-bladewind::sidebar.group name="segments" label="Segments">'
            .'<x-bladewind::sidebar.item name="enterprise" label="Enterprise accounts" href="#enterprise" />'
            .'</x-bladewind::sidebar.group></x-bladewind::sidebar.group>'
            .'<x-bladewind::sidebar.item name="locked" label="Locked" href="#locked" disabled="true" />'
            .'<x-bladewind::sidebar.item name="refresh" label="Refresh data" icon="arrow-path" />'
            .'</x-bladewind::sidebar.group>'
            .'<x-bladewind::sidebar.group name="reports" label="Reports">'.$largeItems.'</x-bladewind::sidebar.group>'
            .'<x-slot:footer><span>Ama Mensah</span></x-slot:footer>'
            .'</x-bladewind::sidebar>'
        );

        $secondary = $this->render(
            '<x-bladewind::sidebar name="secondary" label="Secondary navigation" placement="right" collapsible="true" mobile="drawer">'
            .'<x-slot:header><strong>Secondary</strong></x-slot:header>'
            .'<x-bladewind::sidebar.group name="workspace" label="Workspace" expanded="true">'
            .'<x-bladewind::sidebar.item name="overview" label="Secondary overview" href="#secondary" icon="home" />'
            .'</x-bladewind::sidebar.group></x-bladewind::sidebar>'
        );

        $rtl = $this->render(
            '<x-bladewind::sidebar name="rtl-sidebar" label="التنقل" placement="start" collapsible="true" dir="rtl">'
            .'<x-bladewind::sidebar.group name="account" label="الحساب">'
            .'<x-bladewind::sidebar.item name="profile" label="الملف الشخصي" href="#profile" icon="user" />'
            .'</x-bladewind::sidebar.group></x-bladewind::sidebar>'
        );

        $scripts = <<<'HTML'
        <script>
          window.sidebarEvents = [];
          window.blockGroup = false;
          window.blockClose = false;
          const workspace = document.querySelector('[data-bw-sidebar][data-name="workspace"]');
          ['before-open', 'opened', 'before-close', 'closed', 'before-collapse', 'collapsed', 'before-expand', 'expanded', 'group:before-change', 'group:changed', 'item-activate', 'before-navigate'].forEach((name) => {
            workspace.addEventListener(`bladewind:sidebar:${name}`, (event) => {
              window.sidebarEvents.push({type: event.type, sidebarName: event.detail.sidebarName, groupName: event.detail.groupName, itemName: event.detail.itemName, source: event.detail.source});
              if (window.blockGroup && name === 'group:before-change') event.preventDefault();
              if (window.blockClose && name === 'before-close') event.preventDefault();
            });
          });
          document.querySelectorAll('[data-open-sidebar]').forEach((button) => button.addEventListener('click', () => openSidebar(button.dataset.openSidebar, {triggeringElement: button, source: 'fixture'})));
        </script>
        HTML;

        $body = '<button type="button" id="open-workspace" data-open-sidebar="workspace">Open workspace</button>'
            .'<button type="button" id="open-secondary" data-open-sidebar="secondary">Open secondary</button>'
            .'<button type="button" id="open-rtl" data-open-sidebar="rtl-sidebar">Open RTL</button>'
            .'<main style="display:flex;gap:24px;height:620px;margin-top:16px;min-width:0">'
            .'<section id="primary-shell" style="height:620px">'.$primary.'</section>'
            .'<section id="secondary-shell" class="dark" style="height:620px;background:#101114">'.$secondary.'</section>'
            .'<section id="rtl-shell" style="height:620px" dir="rtl">'.$rtl.'</section>'
            .'</main>';

        file_put_contents(
            self::OUT.'/sidebar.html',
            $this->relative($this->page('Sidebar', $body, $scripts))
        );

        $this->assertFileExists(self::OUT.'/sidebar.html');
    }

    #[Test]
    public function it_writes_the_command_palette_fixture(): void
    {
        $primary = $this->render(
            '<x-bladewind::command-palette name="app-commands" label="Command palette" placeholder="Search for a command or page…" class="e2e-command-palette">'
            .'<x-bladewind::command-palette.group name="navigate" label="Navigate">'
            .'<x-bladewind::command-palette.item name="dashboard" label="Dashboard" description="Overview of your workspace" href="#dashboard" icon="home" />'
            .'<x-bladewind::command-palette.item name="orders" label="Orders" description="Review recent orders" href="#orders" icon="shopping-bag" />'
            .'<x-bladewind::command-palette.item name="locked" label="Locked report" href="#locked" disabled="true" />'
            .'</x-bladewind::command-palette.group>'
            .'<x-bladewind::command-palette.group name="actions" label="Actions">'
            .'<x-bladewind::command-palette.item name="new-order" label="Create order" icon="plus" shortcut="Ctrl+N" keywords="add new" />'
            .'<x-bladewind::command-palette.item name="docs" label="Documentation" href="https://example.com" external="true" />'
            .'</x-bladewind::command-palette.group>'
            .'</x-bladewind::command-palette>'
        );

        $secondary = $this->render(
            '<x-bladewind::command-palette name="secondary" label="Secondary palette" shortcut="mod+p">'
            .'<x-bladewind::command-palette.item name="secondary-item" label="Secondary action" />'
            .'</x-bladewind::command-palette>'
        );

        $scripts = <<<'HTML'
        <script>
          window.commandPaletteEvents = [];
          window.blockOpen = false;
          window.blockClose = false;
          window.blockSelect = false;
          const app = document.querySelector('[data-bw-command-palette][data-name="app-commands"]');
          ['before-open', 'opened', 'before-close', 'closed', 'before-select', 'select', 'search'].forEach((name) => {
            app.addEventListener(`bladewind:command-palette:${name}`, (event) => {
              window.commandPaletteEvents.push({type: event.type, itemName: event.detail.itemName, source: event.detail.source, query: event.detail.query});
              if (window.blockOpen && name === 'before-open') event.preventDefault();
              if (window.blockClose && name === 'before-close') event.preventDefault();
              if (window.blockSelect && name === 'before-select') event.preventDefault();
            });
          });
          document.querySelectorAll('[data-open-command-palette]').forEach((button) => button.addEventListener('click', () => openCommandPalette(button.dataset.openCommandPalette, {triggeringElement: button, source: 'fixture'})));
        </script>
        HTML;

        $body = '<button type="button" id="open-app" data-open-command-palette="app-commands">Open command palette</button>'
            .'<button type="button" id="open-secondary" data-open-command-palette="secondary">Open secondary</button>'
            .'<button type="button" id="after-palette">After</button>'
            .$primary.$secondary;

        file_put_contents(
            self::OUT.'/command-palette.html',
            $this->relative($this->page('Command Palette', $body, $scripts))
        );

        $this->assertFileExists(self::OUT.'/command-palette.html');
    }

    #[Test]
    public function it_writes_the_data_grid_fixture(): void
    {
        $orders = [];
        foreach (range(1, 7) as $index) {
            $orders[] = [
                'id' => $index,
                'reference' => sprintf('ORD-%03d', $index),
                'customer' => ['Ama Mensah', 'Kofi Addo', 'Akosua Owusu'][$index % 3],
                'total' => $index * 1500,
            ];
        }

        $columns = [
            ['key' => 'reference', 'label' => 'Reference', 'sortable' => true],
            ['key' => 'customer', 'label' => 'Customer', 'sortable' => true],
            ['key' => 'total', 'label' => 'Total', 'align' => 'right', 'sortable' => true, 'format' => fn ($value) => '$'.number_format($value / 100, 2)],
        ];

        $primary = $this->render(
            '<x-bladewind::data-grid name="orders" label="Orders" searchable="true" selectable="true"'
            .' paginated="true" page-size="3" :columns="$columns" :rows="$rows" class="e2e-data-grid" />',
            ['columns' => $columns, 'rows' => $orders]
        );

        $single = $this->render(
            '<x-bladewind::data-grid name="assignee" label="Assign to" selectable="true" selection-mode="single"'
            .' :columns="$columns" :rows="$rows" />',
            [
                'columns' => [['key' => 'name', 'label' => 'Name']],
                'rows' => [['id' => 'ama', 'name' => 'Ama Mensah'], ['id' => 'kofi', 'name' => 'Kofi Addo']],
            ]
        );

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($orders, 0, 3), count($orders), 3, 1,
            ['path' => '/data-grid.html', 'pageName' => 'page']
        );
        $server = $this->render(
            '<x-bladewind::data-grid name="server-orders" label="Server orders" :columns="$columns" :rows="$rows" :paginator="$paginator" />',
            ['columns' => $columns, 'rows' => $paginator->items(), 'paginator' => $paginator]
        );

        $scripts = <<<'HTML'
        <script>
          window.dataGridEvents = [];
          window.blockSort = false;
          window.blockSelect = false;
          const grid = document.querySelector('[data-bw-data-grid][data-name="orders"]');
          ['before-sort-change', 'sort-change', 'before-select-change', 'select-change', 'before-page-change', 'page-change', 'search'].forEach((name) => {
            grid.addEventListener(`bladewind:data-grid:${name}`, (event) => {
              window.dataGridEvents.push({type: event.type, key: event.detail.key, direction: event.detail.direction, page: event.detail.page, selected: event.detail.selected, query: event.detail.query});
              if (window.blockSort && name === 'before-sort-change') event.preventDefault();
              if (window.blockSelect && name === 'before-select-change') event.preventDefault();
            });
          });
        </script>
        HTML;

        $body = '<main style="display:flex;flex-direction:column;gap:24px;padding:16px;max-width:900px">'
            .$primary.$single.$server
            .'</main>';

        file_put_contents(
            self::OUT.'/data-grid.html',
            $this->relative($this->page('Data Grid', $body, $scripts))
        );

        $this->assertFileExists(self::OUT.'/data-grid.html');
    }

    #[Test]
    public function it_writes_the_calendar_fixture(): void
    {
        $events = [
            ['date' => '2026-08-05', 'label' => 'Standup', 'type' => 'info'],
            ['date' => '2026-08-05', 'label' => 'Design review', 'type' => 'success'],
            ['date' => '2026-08-05', 'label' => 'Retro', 'type' => 'warning'],
            ['date' => '2026-08-05', 'label' => 'Overflow item', 'type' => 'danger'],
            ['date' => '2026-08-18', 'end' => '2026-08-20', 'label' => 'Conference', 'type' => 'success'],
        ];

        $primary = $this->render(
            '<x-bladewind::calendar name="team" label="Team calendar" date="2026-08-15" selectable="multiple"'
            .' selected="2026-08-10" class="e2e-calendar" :events="$events" />',
            ['events' => $events]
        );

        $restricted = $this->render(
            '<x-bladewind::calendar name="booking" label="Booking calendar" date="2026-08-15" selectable="single"'
            .' min-date="2026-08-10" max-date="2026-08-20" disabled-dates="2026-08-14" />'
        );

        $serverDriven = $this->render(
            '<x-bladewind::calendar name="remote" label="Server-driven calendar" date="2026-08-15" client-navigation="false" />'
        );

        $scripts = <<<'HTML'
        <script>
          window.calendarEvents = [];
          window.blockSelect = false;
          window.blockNavigate = false;
          const team = document.querySelector('[data-bw-calendar][data-name="team"]');
          ['before-navigate', 'navigate', 'before-view-change', 'view-change', 'before-select', 'select'].forEach((name) => {
            team.addEventListener(`bladewind:calendar:${name}`, (event) => {
              window.calendarEvents.push({type: event.type, date: event.detail.date, selected: event.detail.selected, anchor: event.detail.anchor, view: event.detail.view});
              if (window.blockSelect && name === 'before-select') event.preventDefault();
              if (window.blockNavigate && name === 'before-navigate') event.preventDefault();
            });
          });
          window.remoteCalendarEvents = [];
          document.querySelector('[data-bw-calendar][data-name="remote"]').addEventListener('bladewind:calendar:navigate', (event) => {
            window.remoteCalendarEvents.push({anchor: event.detail.anchor});
          });
        </script>
        HTML;

        $body = '<main style="display:flex;flex-direction:column;gap:24px;padding:16px;max-width:700px">'
            .$primary.$restricted.$serverDriven
            .'</main>';

        file_put_contents(
            self::OUT.'/calendar.html',
            $this->relative($this->page('Calendar', $body, $scripts))
        );

        $this->assertFileExists(self::OUT.'/calendar.html');
    }
}
