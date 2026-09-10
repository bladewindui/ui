[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/sidebar)](https://packagist.org/packages/bladewindui/sidebar)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Sidebar

Responsive application navigation for Laravel Blade. Sidebar supports nested groups, explicit active state, desktop collapse, a Drawer presentation on mobile, keyboard navigation, optional state persistence, dark mode, and RTL layouts.

## Installation

```bash
composer require bladewindui/sidebar
```

The package installs Bladewind Core, Icon, and Drawer automatically.

## Usage

```blade
<x-bladewind::sidebar
    name="workspace-navigation"
    label="Workspace navigation"
    active="orders"
    collapsible="true"
    mobile="drawer">

    <x-slot:header>
        <a href="/dashboard" class="flex items-center gap-3">
            <x-bladewind::icon name="building-office-2" />
            <span>Acme Workspace</span>
        </a>
    </x-slot:header>

    <x-bladewind::sidebar.group name="workspace" label="Workspace" icon="squares-2x2" expanded="true">
        <x-bladewind::sidebar.item name="overview" label="Overview" href="/dashboard" icon="home" />
        <x-bladewind::sidebar.item name="orders" label="Orders" href="/orders" icon="shopping-bag" badge="12" />
        <x-bladewind::sidebar.item name="customers" label="Customers" href="/customers" icon="users" />
    </x-bladewind::sidebar.group>

    <x-slot:footer>
        <x-bladewind::avatar image="/images/avatar.png" name="Ama Mensah" />
    </x-slot:footer>
</x-bladewind::sidebar>
```

Use `active` on the Sidebar as the canonical active item name. When it is set, it takes precedence over every item-level `active` value. When it is omitted, the first explicitly active enabled item wins unless `multiple-active="true"` is set. Sidebar never matches the current request URL automatically.

## Responsive behavior

Desktop Sidebar appears at 1024 pixels and wider. Set `collapsible="true"` to enable compact icon-only mode. Compact mode keeps every destination in the DOM and gives each action an accessible label and native tooltip.

On smaller viewports, `mobile="drawer"` moves the same navigation DOM into Bladewind Drawer. This avoids duplicate IDs and landmarks. Drawer provides focus trapping, focus restoration, Escape, backdrop, and body scroll behavior. `close-on-navigate` defaults to `true`. Set `mobile="none"` to omit the mobile Drawer.

`placement` accepts `left`, `right`, `start`, and `end`. Physical values stay on their named edge. Logical values respond to the computed text direction.

## State persistence

Persistence is disabled by default. Set `persist="true"` for desktop collapse state and `persist-groups="true"` for group state. Each Sidebar uses `bladewind:sidebar:{name}` unless `storage-key` is supplied. Unreadable or invalid browser storage is ignored.

## JavaScript API

Every helper returns `true` when it completes or the requested state already applies, and `false` when the target is missing, disabled, unsupported, or canceled.

```javascript
openSidebar('workspace-navigation');
closeSidebar('workspace-navigation');
toggleSidebar('workspace-navigation');
collapseSidebar('workspace-navigation');
expandSidebar('workspace-navigation');
toggleSidebarGroup('workspace-navigation', 'settings');
expandSidebarGroup('workspace-navigation', 'settings');
collapseSidebarGroup('workspace-navigation', 'settings');
resetSidebar('workspace-navigation');
```

## Events

Sidebar emits cancelable `before-open`, `before-close`, `before-collapse`, `before-expand`, and `group:before-change` events. It emits `opened`, `closed`, `collapsed`, `expanded`, and `group:changed` after changes. Button-like items emit cancelable `item-activate`. Links emit cancelable `before-navigate` before optional mobile auto-close.

All event names start with `bladewind:sidebar:`. Event details include the Sidebar name, presentation, placement, source, and triggering element. State events also include previous and next state. Group events include the group name. Item events include item name and link destination when relevant.

## Keyboard behavior

Enter and Space activate group buttons and button-like items. Up Arrow and Down Arrow move through visible enabled controls. Home and End move to the first and last visible control. Right Arrow opens or enters a group in left-to-right layouts, and Left Arrow closes or returns to its parent. These horizontal keys reverse in RTL layouts. Collapsed and disabled descendants are removed from keyboard navigation.

## Components

- `sidebar`: named root, label, active item, placement, mobile mode, mobile size, collapse options, persistence options, height, and header and footer slots.
- `sidebar.group`: name, label, icon, icon type, icon directory, expanded state, disabled state, and nested items or groups.
- `sidebar.item`: name, label, link or button behavior, icon, icon type, icon directory, description, badge, badge label, explicit active state, disabled state, external state, target, and custom slot content.

All three components forward supported custom classes and HTML attributes through their Blade attribute bags.

## Documentation

Full examples and the complete attribute tables are available at [bladewindui.com/component/sidebar](https://bladewindui.com/component/sidebar).

## Livewire

Collapsed/expanded group state and, on mobile, open/closed state live in the sidebar's own DOM, not in Livewire's component state — persistence options re-hydrate that state from storage on init, but a Livewire re-render that touches this markup mid-interaction for reasons unrelated to the sidebar still resets it. Wrap the sidebar in `wire:ignore` if it sits inside such a component. The delegated event bindings are idempotent, so a re-render does not create duplicate listeners.

## License

MIT. See the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
