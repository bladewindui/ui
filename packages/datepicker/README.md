[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/datepicker)](https://packagist.org/packages/bladewindui/datepicker)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Datepicker

BladewindUI — Datepicker component.

## Installation

```bash
composer require bladewindui/datepicker
```

Or install the full library:

```bash
composer require bladewindui/ui
```

## Documentation

Full documentation, live demos, and all available attributes are at **[bladewindui.com](https://bladewindui.com)**.

## Livewire

The date field dispatches a native `change` event when a date is picked, so `wire:model` observes the selection. The component guards against a Livewire re-render building a duplicate calendar popup or a duplicate document click listener — previously each re-render left an orphaned popup behind. The popup's own open/closed state still lives outside the DOM and resets to closed if a Livewire component re-renders it for reasons unrelated to the datepicker — wrap the field in `wire:ignore` in that case.

## License

MIT — see the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
