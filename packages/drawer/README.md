[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/mkocansey/bladewind-drawer)](https://packagist.org/packages/mkocansey/bladewind-drawer)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Drawer

An accessible, responsive drawer or sheet that opens from any physical edge of the viewport.

## Installation

```bash
composer require mkocansey/bladewind-drawer
```

## Usage

```blade
<x-bladewind::drawer name="customer-details" title="Customer details">
    Drawer content
</x-bladewind::drawer>

<x-bladewind::button onclick="showDrawer('customer-details')">Open drawer</x-bladewind::button>
```

Use `showDrawer(name)`, `hideDrawer(name)`, and `toggleDrawer(name)` to control a drawer. See the [full Drawer documentation](https://bladewindui.com/component/drawer) for positions, sizes, slots, modal behavior, and accessibility guidance.

## License

MIT, see the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
