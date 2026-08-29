# BladewindUI component backlog

Potential additions to the library, ordered by likely value to Laravel application teams.
Check a component off only after it satisfies the completion checklist at the end of this file.

Status key: `[ ]` not started · `[~]` in progress · `[x]` complete · `[!]` rejected or superseded

## Priority 1: common application building blocks

- [x] **Breadcrumbs** (`bladewind-breadcrumbs`, Navigation) — Linked hierarchy with current-page state, custom separators, collapsed overflow, and accessible navigation markup.
- [x] **Drawer / sheet** (`bladewind-drawer`, Content) — Edge-mounted overlay for mobile navigation, filters, forms, and detail panels; support all four edges and modal/non-modal modes.
- [x] **Stepper / wizard** (`bladewind-stepper`, Navigation) — Horizontal and vertical steps with complete, current, error, and disabled states; allow clickable and linear workflows.
- [x] **Sidebar navigation** (`bladewind-sidebar`, Navigation): Responsive nested application navigation with sections, icons, active states, badges, collapsible groups, and a mobile drawer mode.
- [x] **Command palette** (`bladewind-command-palette`, Navigation) — Keyboard-first searchable action launcher with grouped actions, shortcuts, empty states, and asynchronous results.
- [ ] **Tree view** (`bladewind-tree`, Content) — Expandable hierarchical data with single/multiple selection, checkboxes, lazy-loaded children, and full keyboard navigation.
- [x] **Data grid / Table v2** (`bladewind-data-grid`, Content) — A higher-level companion to Table with column sorting, filtering, selection, sticky headers, pagination hooks, and server-driven state.
- [ ] **Calendar** (`bladewind-calendar`, Content) — Inline month/week calendar for displaying and selecting dates or events, distinct from the input-focused Datepicker.
- [ ] **Context menu** (`bladewind-context-menu`, Navigation) — Pointer and keyboard-triggered action menu with nested items, separators, disabled actions, and viewport-aware placement.
- [ ] **Segmented control** (`bladewind-segmented-control`, Forms) — Compact mutually exclusive choice control with text/icon options and form-compatible values.

## Priority 2: forms and data entry

- [ ] **Fieldset / form section** (`bladewind-fieldset`, Forms) — Accessible grouping with legend, description, required/optional state, validation summary, and collapsible sections.
- [ ] **Masked input** (`bladewind-masked-input`, Forms) — Formatting and validation for phone numbers, currency, percentages, account numbers, and custom masks.
- [ ] **Cascader** (`bladewind-cascader`, Forms) — Hierarchical selection for country/region, categories, and organisational structures with searchable levels.
- [ ] **Transfer list** (`bladewind-transfer-list`, Forms) — Move one or many options between available and selected lists with search and select-all controls.
- [ ] **Mention input** (`bladewind-mention`, Forms) — Textarea-like input with `@`/`#` suggestions, keyboard selection, custom result rendering, and serialisable values.
- [ ] **Rich text editor** (`bladewind-editor`, Forms) — Opinionated, accessible editor with a deliberately small toolbar, sanitisation guidance, and pluggable upload handling.
- [ ] **Signature pad** (`bladewind-signature`, Forms) — Draw, clear, undo, and export a signature with touch/pointer support and a hidden form value.
- [ ] **Image cropper** (`bladewind-image-cropper`, Forms) — Preview, zoom, rotate, crop, and export an uploaded image; suitable for avatars and document capture.
- [ ] **Editable text** (`bladewind-inline-edit`, Forms) — Display-to-input editing with save, cancel, validation, loading, and optimistic-update hooks.

## Priority 3: content and feedback

- [ ] **Carousel** (`bladewind-carousel`, Content) — Responsive content/media slides with arrows, indicators, autoplay opt-in, swipe support, and reduced-motion handling.
- [ ] **Lightbox / gallery** (`bladewind-gallery`, Content) — Thumbnail grid with full-screen preview, captions, keyboard navigation, zoom, and lazy loading.
- [ ] **Guided tour / spotlight** (`bladewind-tour`, Content) — Anchored product walkthroughs with progress, skip/back controls, placement fallback, and persisted completion hooks.
- [ ] **Split button** (`bladewind-split-button`, Content) — Primary action plus related action menu with loading, disabled, and destructive variants.
- [ ] **Floating action button / speed dial** (`bladewind-speed-dial`, Content) — Fixed primary action that expands into labelled secondary actions with safe mobile positioning.
- [ ] **Banner** (`bladewind-banner`, Content) — Page-level or global announcement bar with tone, icon, actions, dismissibility, and optional persistence.
- [ ] **Code block** (`bladewind-code-block`, Content) — Syntax-highlighted code display with language label, line numbers/highlights, wrapping, and copy action; separate from the OTP Code component.
- [ ] **Keyboard key** (`bladewind-kbd`, Content) — Small semantic key/shortcut display for help text, menus, and command documentation.
- [ ] **Description list** (`bladewind-description-list`, Content) — Responsive label/value presentation for profiles, summaries, and record detail pages with action slots.
- [ ] **Chat / message thread** (`bladewind-chat`, Content) — Message bubbles, sender/avatar metadata, timestamps, delivery states, attachments, and grouped messages.
- [ ] **Meter / gauge** (`bladewind-meter`, Content) — Bounded value visualisation with thresholds and semantic low/medium/high states, distinct from progress completion.

## Priority 4: specialised application widgets

- [ ] **Kanban board** (`bladewind-kanban`, Content) — Columns and draggable cards with empty/loading states, movement hooks, and keyboard-accessible reordering.
- [ ] **Scheduler** (`bladewind-scheduler`, Content) — Day/week resource schedule with time slots, event rendering, selection hooks, and timezone-aware values.
- [ ] **Gantt chart** (`bladewind-gantt`, Content) — Project timeline with dependencies, milestones, zoom levels, progress, and horizontal virtualisation.
- [ ] **Organisation chart** (`bladewind-org-chart`, Content) — Expandable reporting hierarchy with custom node slots, pan/zoom, and accessible list fallback.

## Deliberate non-duplicates

Do not add separate components for these without first proving the existing component cannot reasonably own the behaviour:

- Searchable or multi-select: already supported by **Select**.
- Date range input: already supported by **Datepicker** through its `range` option.
- Toasts: already covered by **Notification**.
- Skeleton loaders: already covered by **Shimmer** in the Spinner package.
- Badges/chips: likely an extension of **Tag**, not a new package.
- Collapse/disclosure: likely an **Accordion** mode, not a new package.
- Dropzone: likely an extension of **Filepicker**, not a new package.
- Button groups: likely an addition to the **Button** package.

## Completion checklist

A component can be marked complete above only when all applicable items below are complete:

- [ ] Public API and accessibility behaviour are written down before implementation.
- [ ] The component is an anonymous Blade component (`@props([...])` in the view), not a `Illuminate\View\Component` subclass. See "Component architecture" below.
- [ ] Leaf package, service provider, Composer metadata, config defaults, assets, and Blade views are complete.
- [ ] Every `config('bladewind.*')` key the component's view reads is also present in `packages/meta/config/bladewind.php`, with a matching default. See "Config surface" below.
- [ ] The relevant group metapackage and root package dependencies/autoloading are updated.
- [ ] Light mode, dark mode, responsive layout, RTL, disabled/loading/error states, and reduced motion are handled where relevant.
- [ ] Keyboard interaction, focus management, semantic markup, ARIA labelling, and screen-reader announcements are verified.
- [ ] Unit/render tests and focused browser tests cover the component's important states and interactions.
- [ ] Package README, website documentation, examples, prop tables, and upgrade notes are complete.
- [ ] A corresponding component page exists in the documentation repository and is wired into navigation, search, and component registries.
- [ ] Built assets are current and the full validation workflow passes.

## Notes and decisions

Record rejected ideas, package-boundary decisions, breaking API choices, and links to implementation issues here so they are not rediscovered later.

### Component architecture

- Every component is an anonymous Blade component: props declared with `@props([...])` at the top of the view, defaults resolved (including `config('bladewind.*')` lookups) in `@props` itself or in a following `@php` block. Do not create a `src/Components/*.php` class extending `Illuminate\View\Component`, and do not call `Blade::component()` from the service provider — the provider only needs `mergeConfigFrom`, `loadViewsFrom`, and the `bladewind-components` publish call, matching `packages/table/src/BladewindTableServiceProvider.php` and `packages/drawer/src/BladewindDrawerServiceProvider.php`.
- This is deliberate, not an oversight: it keeps every component's public API readable in one file, avoids a PHP-class prop list drifting from the Blade view that actually renders, and sidesteps real footguns that only affect class-based components — for example a prop literally named `data` silently loses to the base `Component::data()` method, since anonymous components never go through that class at all.
- A root component with named child components (`sidebar` + `sidebar.group` + `sidebar.item`, `command-palette` + `.group` + `.item`) still stays anonymous throughout: the root view lives at `components/{name}/index.blade.php` and children at `components/{name}/{child}.blade.php`; Blade resolves `x-bladewind::{name}` and `x-bladewind::{name}.{child}` from that directory without any explicit registration.
- `Sidebar` predates this rule and is still class-based; it is a known exception, not a template to copy from for new work.

### Config surface

- Every `config('bladewind.{component}.*', $default)` call a component's own package config (`packages/{component}/config/bladewind.php`) defines must be mirrored into the aggregate `packages/meta/config/bladewind.php`, with the same key and the same default — that file is a consumer's one-stop place to discover and override every setting without reading package source.
- This is enforced by `tests/Core/ConfigSurfaceTest.php`, which greps every `resources/views/components/**/*.blade.php` for `config('bladewind.*')` reads and fails if a key it finds is missing from the aggregate file, or if the two defaults disagree. It only scans Blade files — a `config()` call left in a PHP class (see "Component architecture" above for why there shouldn't be one) will not be caught, so do not rely on the test alone; add the aggregate entry as part of building the component, not after the test fails.

### Authoring component documentation

- Follow the established component-page structure in `bladewindui.com/resources/views/docs/tab.blade.php` and `bladewindui.com/resources/views/docs/dropmenu.blade.php`: introduce the component, then show the basic example without adding a `Basic Usage` heading.
- Do not add an installation section to an individual component page. Keep standalone and group-package commands in `bladewindui.com/resources/views/docs/install.blade.php`, and link the component into the relevant tables there.
- Add headings only for distinct variants, behaviour, accessibility guidance, and complete public attribute tables. Keep the matching side navigation in the same order.
- Document shared contracts such as `icon`, `icon-type`, and `icon-dir` consistently with the existing Icon documentation and verify every example against the package source.

### Integrating completed component work

- Develop each component on a dedicated branch in both the package and documentation repositories.
- After validation passes and the user explicitly approves integration, commit only the component-scoped changes, merge them into `development`, and delete the component branch in both repositories.
- Preserve unrelated worktree changes during integration. Do not include them in the component commit merely to make the branches clean.
