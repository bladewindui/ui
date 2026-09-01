[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/timepicker)](https://packagist.org/packages/bladewindui/timepicker)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Timepicker

BladewindUI — Timepicker component.

## Installation

```bash
composer require bladewindui/timepicker
```

Or install the full library:

```bash
composer require bladewindui/ui
```

## Documentation

Full documentation, live demos, and all available attributes are at **[bladewindui.com](https://bladewindui.com)**.

## Livewire

The time value input dispatches a native `change` event whenever the time is set or cleared, so `wire:model` observes it. The delegated bindings (and the `@once`-guarded modal variant) are idempotent, so a Livewire re-render does not create duplicate listeners.

## License

MIT — see the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
