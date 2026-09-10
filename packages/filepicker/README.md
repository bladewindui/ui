[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/filepicker)](https://packagist.org/packages/bladewindui/filepicker)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# File Picker

BladewindUI — File picker component (powered by FilePond).

## Installation

```bash
composer require bladewindui/filepicker
```

Or install the full library:

```bash
composer require bladewindui/ui
```

## Documentation

Full documentation, live demos, and all available attributes are at **[bladewindui.com](https://bladewindui.com)**.

## Livewire

The component guards against a Livewire re-render building a duplicate FilePond (and Cropper.js) instance around the same input — previously every re-render left the old instance's DOM and listeners behind. Base64-mode hidden inputs dispatch a native `change` event when created, so `wire:model` observes them. Because FilePond manages its own DOM inside the field, wrap the file picker in `wire:ignore` if it sits inside a component that can re-render mid-upload for reasons unrelated to the picker.

## License

MIT — see the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
