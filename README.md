<p><img src="https://img.shields.io/github/license/bladewindui/ui" alt="License" /></p>
<p><img src="https://bladewindui.com/assets/images/bw-logo.png" style="height: 30px; margin-bottom:10px" /></p>

BladewindUI is a modern, open-source UI component library designed to help developers build beautiful, consistent, and
responsive web interfaces faster. It provides a growing collection of thoughtfully designed, customizable components
that are simple to integrate, easy to use, and flexible enough for everything from dashboards and internal tools to
full-featured web applications. Every component is simple to use and ships with sensible defaults you can override
per-project.

# Installation

### Install everything (recommended starting point)

This pulls in all components.

```bash
composer require bladewindui/ui
```

### Install a component group

Each logical group is available as its own package. Install a group when you only need a subset of BladewindUI:

```bash
composer require bladewindui/forms       # all form components
composer require bladewindui/content     # all content & display components
composer require bladewindui/navigation  # all navigation components
```

### Install a single component

Every component is its own Composer package. Install exactly what you need.

```bash
composer require bladewindui/table
composer require bladewindui/accordion
composer require bladewindui/datepicker
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
Standalone packages (Button, Modal, Alert, Bell, Notification, Table, Data Grid, Calendar, Spinner) sit outside any
group.

### Standalone packages

These components are their own packages and are not bundled into any group.
They are pulled in as dependencies by other components that need them.

| Package                                                        | Composer name              | Component(s)                                   |
|----------------------------------------------------------------|----------------------------|------------------------------------------------|
| Core                                                           | `bladewindui/core`         | Shared helpers, CSS variables, `helpers.js`    |
| [Icon](https://bladewindui.com/component/icon)                 | `bladewindui/icon`         | SVG icon wrapper (Heroicons)                   |
| [Alert](https://bladewindui.com/component/alert)               | `bladewindui/alert`        | Alert                                          |
| [Bell](https://bladewindui.com/component/bell)                 | `bladewindui/bell`         | Bell                                           |
| [Button](https://bladewindui.com/component/button)             | `bladewindui/button`       | Button, Circle Button                          |
| [Modal](https://bladewindui.com/component/modal)               | `bladewindui/modal`        | Modal, Modal Icon                              |
| [Drawer](https://bladewindui.com/component/drawer)             | `bladewindui/drawer`       | Drawer                                         |
| [Notification](https://bladewindui.com/component/notification) | `bladewindui/notification` | Notification                                   |
| [Spinner](https://bladewindui.com/component/spinner)           | `bladewindui/spinner`      | Spinner, Shimmer, Processing, Process Complete |
| [Table](https://bladewindui.com/component/table)               | `bladewindui/table`        | Table, Table Icons                             |
| [Data Grid](https://bladewindui.com/component/data-grid)       | `bladewindui/data-grid`    | Data Grid                                      |
| [Calendar](https://bladewindui.com/component/calendar)         | `bladewindui/calendar`     | Calendar                                       |

### Forms group — `bladewindui/forms`

```bash
composer require bladewindui/forms

# or install any single component
composer require bladewindui/input
composer require bladewindui/datepicker
...
```

| Package                                                                  | Composer name             | Component(s)                     |
|--------------------------------------------------------------------------|---------------------------|----------------------------------|
| [Checkbox](https://bladewindui.com/component/checkbox)                   | `bladewindui/checkbox`    | Checkbox                         |
| [Checkcards](https://bladewindui.com/component/checkcard)                | `bladewindui/checkcards`  | Checkcards, Checkcard            |
| [Colorpicker](https://bladewindui.com/component/colorpicker)             | `bladewindui/colorpicker` | Colorpicker                      |
| [Datepicker](https://bladewindui.com/component/datepicker)               | `bladewindui/datepicker`  | Datepicker                       |
| [Filepicker](https://bladewindui.com/component/filepicker)               | `bladewindui/filepicker`  | Filepicker (powered by FilePond) |
| [Input](https://bladewindui.com/component/input)                         | `bladewindui/input`       | Input, Error                     |
| [Number](https://bladewindui.com/component/number)                       | `bladewindui/number`      | Number stepper                   |
| [Radio Button](https://bladewindui.com/component/radio-button)           | `bladewindui/radio`       | Radio Button                     |
| [Select](https://bladewindui.com/component/select)                       | `bladewindui/select`      | Select, Select Item              |
| [Slider](https://bladewindui.com/component/slider)                       | `bladewindui/slider`      | Slider                           |
| [Textarea](https://bladewindui.com/component/textarea)                   | `bladewindui/textarea`    | Textarea                         |
| [Timepicker](https://bladewindui.com/component/timepicker)               | `bladewindui/timepicker`  | Timepicker                       |
| [Toggle](https://bladewindui.com/component/toggle)                       | `bladewindui/toggle`      | Toggle                           |
| [Verification Code](https://bladewindui.com/component/verification-code) | `bladewindui/code`        | Verification Code / OTP          |

### Content group — `bladewindui/content`

```bash
composer require bladewindui/content

# or install any single component
composer require bladewindui/accordion
composer require bladewindui/chart
...
```

| Package                                                                          | Composer name                       | Component(s)                  |
|----------------------------------------------------------------------------------|-------------------------------------|-------------------------------|
| [Accordion](https://bladewindui.com/component/accordion)                         | `bladewindui/accordion`             | Accordion, Accordion Item     |
| [Avatar](https://bladewindui.com/component/avatar)                               | `bladewindui/avatar`                | Avatar, Avatars               |
| [Card](https://bladewindui.com/component/card)                                   | `bladewindui/card`                  | Card, Contact Card            |
| [Centered Content](https://bladewindui.com/component/centered-content)           | `bladewindui/centered-content`      | Centered Content              |
| [Chart](https://bladewindui.com/component/chart)                                 | `bladewindui/chart`                 | Chart (line, bar, pie, donut) |
| [Contact Card](https://bladewindui.com/component/contact-card)                   | `bladewindui/contact-card`          | Contact Card                  |
| [Empty State](https://bladewindui.com/component/empty-state)                     | `bladewindui/empty-state`           | Empty State                   |
| [Horizontal Line Graph](https://bladewindui.com/component/horizontal-line-graph) | `bladewindui/horizontal-line-graph` | Horizontal Line Graph         |
| [List View](https://bladewindui.com/component/list-view)                         | `bladewindui/listview`              | List View, List View Item     |
| [Popover](https://bladewindui.com/component/popover)                             | `bladewindui/popover`               | Popover                       |
| [Progress](https://bladewindui.com/component/progress-bar)                       | `bladewindui/progress`              | Progress Bar, Progress Circle |
| [Rating](https://bladewindui.com/component/rating)                               | `bladewindui/rating`                | Rating                        |
| [Sortable](https://bladewindui.com/component/sortable)                           | `bladewindui/sortable`              | Sortable, Sortable Item       |
| [Drawer](https://bladewindui.com/component/drawer)                               | `bladewindui/drawer`                | Drawer                        |
| [Statistic](https://bladewindui.com/component/statistic)                         | `bladewindui/statistic`             | Statistic                     |
| [Tag](https://bladewindui.com/component/tag)                                     | `bladewindui/tag`                   | Tag, Tags                     |
| [Keyboard Key](https://bladewindui.com/component/kbd)                            | `bladewindui/kbd`                   | Keyboard Key                  |
| [Timeline](https://bladewindui.com/component/timeline)                           | `bladewindui/timeline`              | Timeline, Timelines           |
| [Tooltip](https://bladewindui.com/component/tooltip)                             | `bladewindui/tooltip`               | Tooltip                       |

### Navigation group — `bladewindui/navigation`

```bash
composer require bladewindui/navigation

# or install any single component
composer require bladewindui/breadcrumbs
composer require bladewindui/sidebar
composer require bladewindui/command-palette
composer require bladewindui/tab
composer require bladewindui/stepper
composer require bladewindui/pagination
...
```

| Package                                                              | Composer name                 | Component(s)                                                 |
|----------------------------------------------------------------------|-------------------------------|--------------------------------------------------------------|
| [Breadcrumbs](https://bladewindui.com/component/breadcrumbs)         | `bladewindui/breadcrumbs`     | Breadcrumbs, Breadcrumbs Item                                |
| [Sidebar](https://bladewindui.com/component/sidebar)                 | `bladewindui/sidebar`         | Sidebar, Sidebar Group, Sidebar Item                         |
| [Command Palette](https://bladewindui.com/component/command-palette) | `bladewindui/command-palette` | Command Palette, Command Palette Group, Command Palette Item |
| [Stepper](https://bladewindui.com/component/stepper)                 | `bladewindui/stepper`         | Stepper, Stepper Item, Stepper Content                       |
| [Dropmenu](https://bladewindui.com/component/dropmenu)               | `bladewindui/dropmenu`        | Dropmenu, Dropmenu Item                                      |
| [Pagination](https://bladewindui.com/component/pagination)           | `bladewindui/pagination`      | Pagination                                                   |
| [Tab](https://bladewindui.com/component/tab)                         | `bladewindui/tab`             | Tab, Tab Body, Tab Content, Tab Heading                      |
| [Theme Switcher](https://bladewindui.com/component/theme-switcher)   | `bladewindui/theme-switcher`  | Theme Switcher (light / dark)                                |

## How groups work

The three group packages (`bladewind-forms`, `bladewind-content`, `bladewind-navigation`) contain **no code** — they are
pure Composer metapackages whose only job is to pull in the right leaf packages. This means:

- Installing `bladewindui/content` is identical to installing every content leaf package individually.
- Uninstalling it and requiring just `bladewindui/accordion` is clean and leaves nothing behind.
- Each leaf package registers its own Laravel service provider, so components are auto-discovered whether you install
  them individually or as part of a group.

## Customising defaults

Publish the config file (available when using the full `bladewindui/ui` package):

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