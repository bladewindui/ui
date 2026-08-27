<p><img src="https://img.shields.io/github/license/bladewindui/bladewindui" alt="License" /></p>
<p><img src="https://bladewindui.com/assets/images/bw-logo.png" style="height: 30px; margin-bottom:10px" /></p>

BladewindUI is a collection of UI components built with TailwindCSS, Laravel Blade templates, and vanilla JavaScript.
Every component is simple to use and ships with sensible defaults you can override per-project.

# Installation

### Install everything (recommended starting point)

This pulls in all components.

```bash
composer require bladewindui/bladewindui
```

### Install a component group

Each logical group is available as its own package. Install a group when you only need a subset of BladewindUI:

```bash
composer require mkocansey/bladewind-forms       # all form components
composer require mkocansey/bladewind-content     # all content & display components
composer require mkocansey/bladewind-navigation  # all navigation components
```

### Install a single component

Every component is its own Composer package. Install exactly what you need.

```bash
composer require mkocansey/bladewind-table
composer require mkocansey/bladewind-accordion
composer require mkocansey/bladewind-datepicker
```

Shared dependencies (icon, script, spinner, etc.) are pulled in automatically via Composer's dependency resolution.

## First-time setup

After installing, publish the compiled CSS, JavaScript, and language files:

```bash
php artisan vendor:publish --tag=bladewind-public --force
php artisan vendor:publish --tag=bladewind-lang --force
```

Add the stylesheet to the `<head>` of your layout:

```html

<link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet"/>
```

Add the JavaScript before the closing `</body>` tag:

```html

<script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
```

You are ready to use any component:

```html

<x-bladewind::button>Save User</x-bladewind::button>
```

Full installation guide: [bladewindui.com/install](https://bladewindui.com/install)

## Components

Components are organised into groups. Each group maps directly to a Composer package.
Standalone packages (Button, Modal, Alert, Bell, Notification, Table, Spinner) sit outside any group.

### Standalone packages

These components are their own packages and are not bundled into any group.
They are pulled in as dependencies by other components that need them.

| Package                                                        | Composer name                      | Component(s)                                   |
|----------------------------------------------------------------|------------------------------------|------------------------------------------------|
| Core                                                           | `mkocansey/bladewind-core`         | Shared helpers, CSS variables, `helpers.js`    |
| [Icon](https://bladewindui.com/component/icon)                 | `mkocansey/bladewind-icon`         | SVG icon wrapper (Heroicons)                   |
| [Alert](https://bladewindui.com/component/alert)               | `mkocansey/bladewind-alert`        | Alert                                          |
| [Bell](https://bladewindui.com/component/bell)                 | `mkocansey/bladewind-bell`         | Bell                                           |
| [Button](https://bladewindui.com/component/button)             | `mkocansey/bladewind-button`       | Button, Circle Button                          |
| [Modal](https://bladewindui.com/component/modal)               | `mkocansey/bladewind-modal`        | Modal, Modal Icon                              |
| [Drawer](https://bladewindui.com/component/drawer)             | `mkocansey/bladewind-drawer`       | Drawer                                         |
| [Notification](https://bladewindui.com/component/notification) | `mkocansey/bladewind-notification` | Notification                                   |
| [Spinner](https://bladewindui.com/component/spinner)           | `mkocansey/bladewind-spinner`      | Spinner, Shimmer, Processing, Process Complete |
| [Table](https://bladewindui.com/component/table)               | `mkocansey/bladewind-table`        | Table, Table Icons                             |

### Forms group — `mkocansey/bladewind-forms`

```bash
composer require mkocansey/bladewind-forms

# or install any single component
composer require mkocansey/bladewind-input
composer require mkocansey/bladewind-datepicker
...
```

| Package                                                                  | Composer name                     | Component(s)                     |
|--------------------------------------------------------------------------|-----------------------------------|----------------------------------|
| [Checkbox](https://bladewindui.com/component/checkbox)                   | `mkocansey/bladewind-checkbox`    | Checkbox                         |
| [Checkcards](https://bladewindui.com/component/checkcard)                | `mkocansey/bladewind-checkcards`  | Checkcards, Checkcard            |
| [Colorpicker](https://bladewindui.com/component/colorpicker)             | `mkocansey/bladewind-colorpicker` | Colorpicker                      |
| [Datepicker](https://bladewindui.com/component/datepicker)               | `mkocansey/bladewind-datepicker`  | Datepicker                       |
| [Filepicker](https://bladewindui.com/component/filepicker)               | `mkocansey/bladewind-filepicker`  | Filepicker (powered by FilePond) |
| [Input](https://bladewindui.com/component/input)                         | `mkocansey/bladewind-input`       | Input, Error                     |
| [Number](https://bladewindui.com/component/number)                       | `mkocansey/bladewind-number`      | Number stepper                   |
| [Radio Button](https://bladewindui.com/component/radio-button)           | `mkocansey/bladewind-radio`       | Radio Button                     |
| [Select](https://bladewindui.com/component/select)                       | `mkocansey/bladewind-select`      | Select, Select Item              |
| [Slider](https://bladewindui.com/component/slider)                       | `mkocansey/bladewind-slider`      | Slider                           |
| [Textarea](https://bladewindui.com/component/textarea)                   | `mkocansey/bladewind-textarea`    | Textarea                         |
| [Timepicker](https://bladewindui.com/component/timepicker)               | `mkocansey/bladewind-timepicker`  | Timepicker                       |
| [Toggle](https://bladewindui.com/component/toggle)                       | `mkocansey/bladewind-toggle`      | Toggle                           |
| [Verification Code](https://bladewindui.com/component/verification-code) | `mkocansey/bladewind-code`        | Verification Code / OTP          |

### Content group — `mkocansey/bladewind-content`

```bash
composer require mkocansey/bladewind-content

# or install any single component
composer require mkocansey/bladewind-accordion
composer require mkocansey/bladewind-chart
...
```

| Package                                                                          | Composer name                               | Component(s)                  |
|----------------------------------------------------------------------------------|---------------------------------------------|-------------------------------|
| [Accordion](https://bladewindui.com/component/accordion)                         | `mkocansey/bladewind-accordion`             | Accordion, Accordion Item     |
| [Avatar](https://bladewindui.com/component/avatar)                               | `mkocansey/bladewind-avatar`                | Avatar, Avatars               |
| [Card](https://bladewindui.com/component/card)                                   | `mkocansey/bladewind-card`                  | Card, Contact Card            |
| [Centered Content](https://bladewindui.com/component/centered-content)           | `mkocansey/bladewind-centered-content`      | Centered Content              |
| [Chart](https://bladewindui.com/component/chart)                                 | `mkocansey/bladewind-chart`                 | Chart (line, bar, pie, donut) |
| [Contact Card](https://bladewindui.com/component/contact-card)                   | `mkocansey/bladewind-contact-card`          | Contact Card                  |
| [Empty State](https://bladewindui.com/component/empty-state)                     | `mkocansey/bladewind-empty-state`           | Empty State                   |
| [Horizontal Line Graph](https://bladewindui.com/component/horizontal-line-graph) | `mkocansey/bladewind-horizontal-line-graph` | Horizontal Line Graph         |
| [List View](https://bladewindui.com/component/list-view)                         | `mkocansey/bladewind-listview`              | List View, List View Item     |
| [Popover](https://bladewindui.com/component/popover)                             | `mkocansey/bladewind-popover`               | Popover                       |
| [Progress](https://bladewindui.com/component/progress-bar)                       | `mkocansey/bladewind-progress`              | Progress Bar, Progress Circle |
| [Rating](https://bladewindui.com/component/rating)                               | `mkocansey/bladewind-rating`                | Rating                        |
| [Sortable](https://bladewindui.com/component/sortable)                           | `mkocansey/bladewind-sortable`              | Sortable, Sortable Item       |
| [Drawer](https://bladewindui.com/component/drawer)                               | `mkocansey/bladewind-drawer`                | Drawer                        |
| [Statistic](https://bladewindui.com/component/statistic)                         | `mkocansey/bladewind-statistic`             | Statistic                     |
| [Tag](https://bladewindui.com/component/tag)                                     | `mkocansey/bladewind-tag`                   | Tag, Tags                     |
| [Timeline](https://bladewindui.com/component/timeline)                           | `mkocansey/bladewind-timeline`              | Timeline, Timelines           |
| [Tooltip](https://bladewindui.com/component/tooltip)                             | `mkocansey/bladewind-tooltip`               | Tooltip                       |

### Navigation group — `mkocansey/bladewind-navigation`

```bash
composer require mkocansey/bladewind-navigation

# or install any single component
composer require mkocansey/bladewind-breadcrumbs
composer require mkocansey/bladewind-tab
composer require mkocansey/bladewind-breadcrumbs
composer require mkocansey/bladewind-pagination
...
```

| Package                                                            | Composer name                        | Component(s)                            |
|--------------------------------------------------------------------|--------------------------------------|-----------------------------------------|
| [Breadcrumbs](https://bladewindui.com/component/breadcrumbs)       | `mkocansey/bladewind-breadcrumbs`    | Breadcrumbs, Breadcrumbs Item           |
| [Dropmenu](https://bladewindui.com/component/dropmenu)             | `mkocansey/bladewind-dropmenu`       | Dropmenu, Dropmenu Item                 |
| [Pagination](https://bladewindui.com/component/pagination)         | `mkocansey/bladewind-pagination`     | Pagination                              |
| [Tab](https://bladewindui.com/component/tab)                       | `mkocansey/bladewind-tab`            | Tab, Tab Body, Tab Content, Tab Heading |
| [Theme Switcher](https://bladewindui.com/component/theme-switcher) | `mkocansey/bladewind-theme-switcher` | Theme Switcher (light / dark)           |

## How groups work

The three group packages (`bladewind-forms`, `bladewind-content`, `bladewind-navigation`) contain **no code** — they are
pure Composer metapackages whose only job is to pull in the right leaf packages. This means:

- Installing `mkocansey/bladewind-content` is identical to installing every content leaf package individually.
- Uninstalling it and requiring just `mkocansey/bladewind-accordion` is clean and leaves nothing behind.
- Each leaf package registers its own Laravel service provider, so components are auto-discovered whether you install
  them individually or as part of a group.

## Customising defaults

Publish the config file (available when using the full `bladewindui/bladewindui` package):

```bash
php artisan vendor:publish --tag=bladewind-config
```

This creates `config/bladewind.php` in your project. Every attribute in every component has a default defined here.
Override them once and all component instances in your project will follow suit.

Full customisation guide: [bladewindui.com/customize](https://bladewindui.com/customize)

## Documentation

The complete documentation with extensive examples for each component is available
at [bladewindui.com](https://bladewindui.com)

## Questions and support

- Email: [mike@bladewindui.com](mailto:mike@bladewindui.com)
- Twitter / X: [@bladewindui](https://twitter.com/bladewindui)
- Security vulnerabilities: please e-mail rather than opening a public issue

## License

BladewindUI is open-sourced software licensed under the [MIT licence](https://opensource.org/licenses/MIT).
