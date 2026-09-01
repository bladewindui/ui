[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/popover)](https://packagist.org/packages/bladewindui/popover)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Popover

BladewindUI — Popover component.

## Installation

```bash
composer require bladewindui/popover
```

Or install the full library:

```bash
composer require bladewindui/ui
```

## Documentation

Full documentation, live demos, and all available attributes are at **[bladewindui.com](https://bladewindui.com)**.

## Livewire

The popover's open/closed state lives outside the DOM it renders and resets to closed if a Livewire component re-renders it — wrap the trigger and popover in `wire:ignore` if they sit inside a component that re-renders for reasons unrelated to the popover itself. The component guards against a Livewire re-render creating a duplicate popover instance or duplicate document click listeners, so re-renders no longer leak listeners.

## License

MIT — see the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
