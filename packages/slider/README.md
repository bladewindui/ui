[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/slider)](https://packagist.org/packages/bladewindui/slider)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Slider

BladewindUI — Range slider component.

## Installation

```bash
composer require bladewindui/slider
```

Or install the full library:

```bash
composer require bladewindui/ui
```

## Documentation

Full documentation, live demos, and all available attributes are at **[bladewindui.com](https://bladewindui.com)**.

## Livewire

The hidden value input dispatches a native `change` event as the slider is dragged, so `wire:model` observes it. Handlers are assigned via `.oninput`, which a Livewire re-render safely reassigns rather than duplicating.

## License

MIT — see the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
