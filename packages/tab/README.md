[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/tab)](https://packagist.org/packages/bladewindui/tab)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Tab Group

BladewindUI — Tab group component.

## Installation

```bash
composer require bladewindui/tab
```

Or install the full library:

```bash
composer require bladewindui/ui
```

## Documentation

Full documentation, live demos, and all available attributes are at **[bladewindui.com](https://bladewindui.com)**.

## Livewire

The active tab lives in the tab group's own DOM, not in Livewire's component state. A Livewire re-render that touches this markup for reasons unrelated to the tabs resets the active tab back to its initial value — wrap the tab group in `wire:ignore` if it sits inside such a component. The delegated event bindings are idempotent, so a re-render does not create duplicate listeners.

## License

MIT — see the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
