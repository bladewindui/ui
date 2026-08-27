[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/mkocansey/bladewind-breadcrumbs)](https://packagist.org/packages/mkocansey/bladewind-breadcrumbs)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Breadcrumbs

Accessible, responsive breadcrumb navigation for Laravel Blade applications.

## Installation

Install only Breadcrumbs:

```bash
composer require mkocansey/bladewind-breadcrumbs
```

Or install every navigation component:

```bash
composer require mkocansey/bladewind-navigation
```

## Usage

```blade
<x-bladewind::breadcrumbs aria-label="Breadcrumb">
    <x-bladewind::breadcrumbs.item href="/" icon="home">Home</x-bladewind::breadcrumbs.item>
    <x-bladewind::breadcrumbs.item href="/customers">Customers</x-bladewind::breadcrumbs.item>
    <x-bladewind::breadcrumbs.item current>Customer details</x-bladewind::breadcrumbs.item>
</x-bladewind::breadcrumbs>
```

The current item is plain text by default. Add `href` when the current item must also be a link. Long trails collapse visually on small screens, while every link stays available to keyboard and screen-reader users.

Use `separator="slash"`, `separator="dot"`, or any custom text separator. Available sizes are `tiny`, `small`, `regular`, `medium`, `big`, and `large`.

## Documentation

Full documentation, live demos, and all available attributes are at **[bladewindui.com/component/breadcrumbs](https://bladewindui.com/component/breadcrumbs)**.

## License

MIT, see the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.

