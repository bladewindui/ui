[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/command-palette)](https://packagist.org/packages/bladewindui/command-palette)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Command Palette

A keyboard-first, searchable action launcher for Laravel Blade. Command Palette opens with a configurable shortcut or a helper call, filters grouped actions as the user types, and supports full keyboard navigation, dark mode, and asynchronous results.

## Installation

```bash
composer require bladewindui/command-palette
```

The package installs Bladewind Core and Icon automatically.

## Usage

```blade
<x-bladewind::command-palette name="app-commands" label="Command palette" placeholder="Search for a command or page…">
    <x-bladewind::command-palette.group name="navigate" label="Navigate">
        <x-bladewind::command-palette.item name="dashboard" label="Dashboard" href="/dashboard" icon="home" />
        <x-bladewind::command-palette.item name="orders" label="Orders" description="Review recent orders" href="/orders" icon="shopping-bag" />
    </x-bladewind::command-palette.group>

    <x-bladewind::command-palette.group name="actions" label="Actions">
        <x-bladewind::command-palette.item name="new-order" label="Create order" icon="plus" shortcut="Ctrl+N" keywords="add new" />
        <x-bladewind::command-palette.item name="logout" label="Sign out" icon="arrow-right-on-rectangle" />
    </x-bladewind::command-palette.group>
</x-bladewind::command-palette>

<button type="button" onclick="openCommandPalette('app-commands')">Search…</button>
```

Command Palette renders hidden and opens with its keyboard shortcut (`Ctrl+K` or `Cmd+K` by default) or a helper call. It is not tied to a trigger button; wire any element to call `openCommandPalette('app-commands')`.

## Searching

Typing in the search field filters items by label, description, and an optional `keywords` attribute, matched case-insensitively as a substring. Groups with no visible items are hidden. The palette emits `bladewind:command-palette:search` with the current query on every keystroke, so applications can also drive server-side search — set `loading="true"` (or call `setCommandPaletteLoading(name, true)`) while results are in flight and swap the item list once they arrive.

## Keyboard behavior

Up Arrow and Down Arrow move the highlighted item; Home and End jump to the first and last visible item. Enter activates the highlighted item. Escape closes the palette. Tab is trapped between the search field and the close button while the palette is open. Disabled and filtered-out items are skipped.

## JavaScript API

Every helper returns `true` when it completes or the requested state already applies, and `false` when the target is missing or a cancelable event was prevented.

```javascript
openCommandPalette('app-commands');
closeCommandPalette('app-commands');
toggleCommandPalette('app-commands');
resetCommandPalette('app-commands');
setCommandPaletteLoading('app-commands', true);
```

## Events

Command Palette emits cancelable `before-open`, `before-close`, and `before-select` events, and `opened`, `closed`, `select`, and `search` after changes. All event names start with `bladewind:command-palette:`. Event details include the palette name and, for item events, the item name and link destination.

## Components

- `command-palette`: named root, label, placeholder, search label, keyboard shortcut, size, initial open state, loading state, empty text with an optional supporting description, loading text, close-on-select, backdrop and escape dismissal, and an optional footer slot appended after the built-in keyboard hints.
- `command-palette.group`: name and heading label for a set of items.
- `command-palette.item`: name, label, description, icon, icon type, icon directory, display shortcut, extra search keywords, link or button behavior, disabled state, external state, target, and custom slot content.

Every component forwards supported custom classes and HTML attributes through its Blade attribute bag.

## Documentation

Full examples and the complete attribute tables are available at [bladewindui.com/component/command-palette](https://bladewindui.com/component/command-palette).

## Livewire

Open/closed state and the current search filter live in the palette's own DOM, not in Livewire's component state. A Livewire re-render that touches this markup for reasons unrelated to the palette resets it to closed — wrap the palette in `wire:ignore` if it sits inside such a component. The delegated event bindings are idempotent, so a re-render does not create duplicate listeners.

## License

MIT. See the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
