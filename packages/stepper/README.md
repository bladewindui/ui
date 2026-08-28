[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/mkocansey/bladewind-stepper)](https://packagist.org/packages/mkocansey/bladewind-stepper)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Stepper

Accessible horizontal and vertical progress indicators and multi-step wizards for Laravel Blade applications.

## Installation

```bash
composer require mkocansey/bladewind-stepper
```

The Navigation bundle also includes Stepper:

```bash
composer require mkocansey/bladewind-navigation
```

## Usage

```blade
<x-bladewind::stepper name="setup" current="profile" style="circles" linear="true" aria-label="Account setup">
    <x-bladewind::stepper.item name="account" label="Account" state="complete" />
    <x-bladewind::stepper.item name="profile" label="Profile" description="Personal details" />
    <x-bladewind::stepper.item name="security" label="Security" />

    <x-bladewind::stepper.content name="account">Account form</x-bladewind::stepper.content>
    <x-bladewind::stepper.content name="profile" has-border="false">Profile form</x-bladewind::stepper.content>
    <x-bladewind::stepper.content name="security">Security form</x-bladewind::stepper.content>
</x-bladewind::stepper>
```

Navigate with `showStepperStep(name, step)`, `nextStepperStep(name)`, `previousStepperStep(name)`, and `resetStepper(name)`. Listen for the cancelable `bladewind:stepper:before-change` event to validate a step. Successful changes emit `bladewind:stepper:changed`; advancing beyond the last enabled step emits `bladewind:stepper:complete`.

The root `current` prop is the canonical initial selection and takes precedence over a conflicting item `state="current"` value.

Choose `circles`, `chevrons`, `bars`, or `line` with the root `style` prop. The default is `circles`. Every style uses the same states, content panels, navigation helpers, events, keyboard behavior, and accessibility semantics.

`circles`, `bars`, and `line` support horizontal and vertical orientation. `chevrons` is horizontal-only; a vertical Chevron request falls back to `circles`.

Content panels have a border by default. Set `has-border="false"` on `stepper.content` when another component, such as Card, provides the visible content surface.

## Documentation

Full documentation and live examples are at **[bladewindui.com/component/stepper](https://bladewindui.com/component/stepper)**.

## License

MIT. See the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
