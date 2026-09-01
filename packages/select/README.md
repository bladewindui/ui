[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/select)](https://packagist.org/packages/bladewindui/select)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Select

BladewindUI — Select / dropdown component.

## Installation

```bash
composer require bladewindui/select
```

Or install the full library:

```bash
composer require bladewindui/ui
```

## Documentation

Full documentation, live demos, and all available attributes are at **[bladewindui.com](https://bladewindui.com)**.

## Livewire

The selected value is written to a hidden `<input>` that dispatches a native `change` event, so `wire:model` picks up selections without any extra wiring. Open/closed and search-filter state lives outside the DOM and resets if a Livewire component re-renders it for reasons unrelated to the select — wrap the select in `wire:ignore` in that case. The component guards against a Livewire re-render creating a duplicate instance or duplicate document click listeners.

## License

MIT — see the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
