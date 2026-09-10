[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/drawer)](https://packagist.org/packages/bladewindui/drawer)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Drawer

An accessible, responsive drawer or sheet that opens from any physical edge of the viewport.

## Installation

```bash
composer require bladewindui/drawer
```

## Usage

```blade
<x-bladewind::drawer name="customer-details" title="Customer details">
    Drawer content
</x-bladewind::drawer>

<x-bladewind::button onclick="showDrawer('customer-details')">Open drawer</x-bladewind::button>
```

Use `showDrawer(name)`, `hideDrawer(name)`, and `toggleDrawer(name)` to control a drawer. See the [full Drawer documentation](https://bladewindui.com/component/drawer) for positions, sizes, slots, modal behavior, and accessibility guidance.

## Livewire

Open/closed state (`data-state`) lives in the drawer's own DOM, not in Livewire's component state. A Livewire re-render that touches this markup for reasons unrelated to the drawer silently closes it — wrap the drawer in `wire:ignore` if it sits inside such a component, especially one that can re-render while the drawer is open.

## License

MIT, see the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
