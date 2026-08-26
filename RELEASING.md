# Releasing BladewindUI

All work happens in **this monorepo**, hosted at **`bladewindui/bladewindui`**.
The individual package repos (`mkocansey/bladewind-table` etc.) are **read-only mirrors** — never push to them directly.

> ⚠️ **Never target a split repo that resolves to this monorepo's own remote.**
> A matrix entry whose `repository_organization` + `split_repository` add up to the
> monorepo itself makes the split action force-push filtered subtree history into its
> own parent, overwriting `main`. That happened on 2026-06-08, when an entry targeted
> `bladewind` while the monorepo lived at `mkocansey/bladewind` — `main` was wiped down
> to 3 files and restored from a contributor's local clone.
>
> **The forbidden name moved with the repo.** It is now `bladewindui` + `bladewindui`.
> The splits still push to the `mkocansey` org, so nothing can collide today; if they
> are ever moved, that pairing recreates the incident. See the note above the
> (deliberately absent) `packages/meta` entry in `split-packages.yml`.

---

## Root `composer.json` — why it is a `library` with `replace`

The monorepo root is named `bladewindui/bladewindui` and declares `type: library` so
that downstream projects can depend on it directly via a Composer **path repository**
during local development:

```json
"repositories": {
    "bladewindui/bladewindui": {
        "type": "path",
        "url": "/path/to/bladewindui"
    }
}
```

The `replace` block tells Composer that installing the root package also satisfies every
sub-package requirement (e.g. `mkocansey/bladewind-button ^2.0`), so no network calls
are made for the individual split repos during local dev.

The `extra.laravel.providers` list registers all component service providers so Laravel
auto-discovers them from a single path-repo install.

**On Packagist**, `bladewindui/bladewindui` is sourced **directly from this monorepo**.
The root `composer.json` *is* the published full-install package: its `replace` block
declares every granular sub-package, so installing it transparently satisfies
`mkocansey/bladewind-button`, `mkocansey/bladewind-table`, etc. without Composer ever
touching the split repos.

That `replace` block also declares the package's **former name**,
`mkocansey/bladewind`, at `self.version`. Anything that still depends on the old name —
a consuming app, or a third-party package — is satisfied by installing this one.

`packages/meta` is **intentionally not split** into its own repo — doing so would
require a split target literally named `bladewind`, which collides with this monorepo's
own remote (see the warning above, and the explanatory note in `split-packages.yml`
where that matrix entry would otherwise go).

---

## Moving the package to `bladewindui/bladewindui`

The root `composer.json`, `split-packages.yml` and this file are already prepared for
the move. **This branch must not reach `main` until the new repo and Packagist package
exist** — the moment `main` says `bladewindui/bladewindui`, Packagist's webhook for
`mkocansey/bladewind` sees a name mismatch and refuses the update.

### What deliberately does not change

Only the Composer package name and the GitHub location move. These stay exactly as they
are, and that is what keeps consuming apps working untouched:

| identifier | value | set by |
|---|---|---|
| PHP namespace | `Mkocansey\Bladewind\…` | `autoload.psr-4` |
| Blade namespace | `bladewind` → `x-bladewind::card` | `loadViewsFrom(..., 'bladewind')` |
| config key | `bladewind` | `mergeConfigFrom(..., 'bladewind')` |
| published assets | `public/vendor/bladewind` | `BladewindCoreServiceProvider` |
| lang path | `lang/vendor/bladewind` | `BladewindCoreServiceProvider` |

Renaming the Blade namespace would rewrite every component tag in every consuming app —
one audited application alone has ~4,600 of them — for no benefit. Don't.

### Steps

1. **Create `bladewindui/bladewindui`** on GitHub. Do *not* transfer
   `mkocansey/bladewind`: the old repo has to keep existing to serve the compatibility
   shim in step 4, and creating a repo at a transferred name disables GitHub's redirect
   anyway. The trade is that stars, watchers and issues stay on the old repo.
2. **Push this monorepo's full history** to the new remote, then merge this branch.
3. **Register `bladewindui/bladewindui` on Packagist** with the GitHub webhook, and
   release `4.4.0` from it using the flow below. Keep the version line continuous —
   starting again at 1.0.0 would break `monorepo-builder`'s single-version invariant.
4. **Publish the shim.** On `mkocansey/bladewind`, reduce `composer.json` to the
   metapackage in `docs/migration/old-package-composer.json` and tag `4.4.0`. An
   existing app then picks up the real package on its next `composer update` with no
   changes to its own code — same namespace, same tags, same config, same assets.
5. **Mark `mkocansey/bladewind` abandoned** on Packagist, replacement
   `bladewindui/bladewindui`. The notice is advisory; the shim keeps it working.

### Tell consumers about the vendor path

The one thing that genuinely breaks is a hardcoded `vendor/mkocansey/bladewind` path.
Tailwind v4 apps commonly have:

```css
@source '../../vendor/mkocansey/bladewind/packages';
```

After the move that is `vendor/bladewindui/bladewindui/packages`. **The build does not
error** — it silently stops generating the utilities scanned from BladeWind templates,
and styles go missing. This belongs at the top of the release notes.

### The split mirrors are staying put

The 48 `mkocansey/bladewind-*` repos are not moving in this change. They are read-only
mirrors, they keep working, and moving them means 48 new repos plus 48 Packagist
registrations plus 48 shims. `repository_organization` in `split-packages.yml` therefore
stays `mkocansey` — and if you ever do move them, re-read the warning at the top of this
file first.

---

## First-time setup

### 1. Create the split repos on GitHub

Create one empty public repo per package (no README, no licence — keep them completely empty).
There are **48 repos** in total — one per `packages/*` directory plus the full-install meta repo:

```
# Foundation
mkocansey/bladewind-core
mkocansey/bladewind-icon
mkocansey/bladewind-script
mkocansey/bladewind-spinner
mkocansey/bladewind-button
mkocansey/bladewind-alert
mkocansey/bladewind-bell
mkocansey/bladewind-notification
mkocansey/bladewind-modal
mkocansey/bladewind-table

# Forms leaf packages
mkocansey/bladewind-input
mkocansey/bladewind-textarea
mkocansey/bladewind-select
mkocansey/bladewind-checkbox
mkocansey/bladewind-radio
mkocansey/bladewind-toggle
mkocansey/bladewind-datepicker
mkocansey/bladewind-timepicker
mkocansey/bladewind-colorpicker
mkocansey/bladewind-filepicker
mkocansey/bladewind-slider
mkocansey/bladewind-checkcards
mkocansey/bladewind-number
mkocansey/bladewind-code

# Forms aggregate (metapackage)
mkocansey/bladewind-forms

# Content leaf packages
mkocansey/bladewind-card
mkocansey/bladewind-contact-card
mkocansey/bladewind-avatar
mkocansey/bladewind-accordion
mkocansey/bladewind-tag
mkocansey/bladewind-timeline
mkocansey/bladewind-statistic
mkocansey/bladewind-rating
mkocansey/bladewind-horizontal-line-graph
mkocansey/bladewind-empty-state
mkocansey/bladewind-centered-content
mkocansey/bladewind-chart
mkocansey/bladewind-progress
mkocansey/bladewind-listview
mkocansey/bladewind-tooltip
mkocansey/bladewind-popover

# Content aggregate (metapackage)
mkocansey/bladewind-content

# Navigation leaf packages
mkocansey/bladewind-tab
mkocansey/bladewind-dropmenu
mkocansey/bladewind-pagination
mkocansey/bladewind-theme-switcher

# Navigation aggregate (metapackage)
mkocansey/bladewind-navigation

# Full-install meta package
mkocansey/bladewind          ← maps to packages/meta/
```

### 2. Add the GitHub Actions secret

In **this monorepo's** Settings → Secrets and variables → Actions, add:

| Secret name | Value |
|---|---|
| `MONOREPO_SPLIT_TOKEN` | A GitHub personal access token (classic) with `repo` scope, or a fine-grained token with **Contents: Read and write** on all split repos |

### 3. Register each split repo on Packagist

Go to [packagist.org/packages/submit](https://packagist.org/packages/submit) and submit each split repo URL. Enable the GitHub webhook so Packagist auto-updates on new tags.

---

## Day-to-day release flow

```bash
# 1. Make sure you're on main and everything is committed
git checkout main && git pull

# 2. Install monorepo-builder (first time only)
composer install

# 3. Validate all package composer.json files are consistent
vendor/bin/monorepo-builder validate

# 4. Release — this command does everything:
#    a) bumps all inter-package version constraints to the new version
#    b) commits the change
#    c) tags the monorepo commit with the string you pass below, verbatim
#    d) pushes the tag to GitHub
#    → GitHub Actions split-packages.yml fires automatically
#    → each packages/* directory is pushed to its read-only repo
#    → the same tag is applied to each split repo
#    → Packagist picks up the new release via webhook
#
#    INCLUDE THE "v" PREFIX. Every release since v3.0 is tagged vX.Y.Z, and the
#    version string is used as the tag exactly as typed — no prefix is added for
#    you. Passing "4.3.0" tags "4.3.0", which is what happened on 2026-08-10 and
#    is why that one tag in the list has no v. Nothing breaks (the split workflow
#    triggers on '*', and Composer normalises both forms to the same version), but
#    the tag list stops being uniform and the tag no longer matches the release title.
vendor/bin/monorepo-builder release v2.1.0

# 5. Done. Monitor the Actions tab to confirm all 48 splits succeeded.
```

---

## Semantic versioning rules

- **Patch** (`2.0.x`) — bug fixes, no API changes
- **Minor** (`2.x.0`) — new attributes/features, backward compatible
- **Major** (`x.0.0`) — breaking changes (attribute renamed/removed, SP class moved)

All packages always share the same version number. The monorepo-builder enforces this.

---

## Package architecture

Every component is a **standalone leaf package** that users can install individually:

```
composer require mkocansey/bladewind-accordion   # just accordion
composer require mkocansey/bladewind-table       # just table (pulls exact deps)
```

Three **aggregate metapackages** bundle related components for convenience:

```
composer require mkocansey/bladewind-forms       # all form components
composer require mkocansey/bladewind-content     # all content components
composer require mkocansey/bladewind-navigation  # all navigation components
```

The full install meta-package pulls everything:

```
composer require mkocansey/bladewind             # the whole library
```

Aggregate packages are `type: metapackage` — they contain no code, only a `require` list.

---

## Adding a new component

1. Create `packages/<name>/` with:
   - `composer.json` (name: `mkocansey/bladewind-<name>`, type: `library`) — list only the leaf packages it actually depends on in `require` (grep the blade file for `<x-bladewind::*` to find them), and declare its own provider in `extra.laravel.providers` as `"Mkocansey\\Bladewind\\<Name>\\Bladewind<Name>ServiceProvider"` — **keep the `Bladewind` prefix**, this is the entry the split package is installed with
   - `src/Bladewind<Name>ServiceProvider.php` — see the template below
   - any CSS in `resources/assets/css/`, imported from the root `tailwind.css` so it lands in the compiled bundle
   - any JavaScript in `packages/core/public/js/` — **not** in a `public/` directory of its own (see below)
   - `config/bladewind.php` (just this component's config keys)
   - `resources/views/components/` (blade files)

2. Add to root `composer.json` — three places:
   - `autoload.psr-4`: `"Mkocansey\\Bladewind\\<Name>\\": "packages/<name>/src/"`
   - `replace`: `"mkocansey/bladewind-<name>": "self.version"`
   - `extra.laravel.providers`: `"Mkocansey\\Bladewind\\<Name>\\Bladewind<Name>ServiceProvider"`

3. Add a matrix entry to `.github/workflows/split-packages.yml`:
   ```yaml
   - { local_path: 'packages/<name>', split_repository: 'bladewind-<name>' }
   ```

4. If the component belongs to a group (forms/content/navigation), add it to the relevant `packages/<group>/composer.json` `require`

5. Add it to `packages/meta/composer.json` `require` (or it'll be pulled transitively via the group)

6. Add its config keys to `packages/meta/config/bladewind.php`

7. Create the empty GitHub repo `mkocansey/bladewind-<name>`

8. Register it on Packagist with a GitHub webhook

9. Release a new minor version

### Rendering the attribute bag

If your component spreads `$attributes` onto its root element, call
`exceptPropAliases(get_defined_vars())` first:

```blade
<div {{ $attributes->exceptPropAliases(get_defined_vars())->merge(['class' => $classes]) }}>
```

Blade camel-cases an attribute name into the component's data, so `has_shadow="false"`
correctly sets `$hasShadow` — but `@props` only strips the camelCase and kebab-case
spellings from the bag, so the snake_case key survives and renders onto the root as a
literal `has_shadow="false"` attribute. Since the docs use snake_case throughout, that
affects nearly every consumer. The macro is registered by `BladewindCoreServiceProvider`,
which every component package depends on.

`tests/Components/PropAliasLeakTest.php` covers every component that renders the bag.
Add yours to its provider — that is what stops the next component reintroducing this.

### Where assets live

All published assets live in `packages/core/public`, and `BladewindCoreServiceProvider` is the only provider that publishes them (tag: `bladewind-public`). Every component package requires `mkocansey/bladewind-core`, so a user who installs a single component still gets the assets.

Do not give a component package its own `public/` directory. Nothing publishes it, so the file is never served — it just becomes a second copy that drifts. That is exactly what happened to `select.js`: the copy left behind in `packages/select/public` missed the `filter()` fix in 65d3525 for two releases before it was removed.

Component CSS is the one exception to "assets live in core": it stays in `packages/<name>/resources/assets/css/` because it is a *source* file, imported by the root `tailwind.css` and compiled into `packages/core/public/css/bladewind-ui.min.css`. It is never published raw.

### Service provider template for new components

Use this exact pattern:

```php
<?php

namespace Mkocansey\Bladewind\<Name>;

use Illuminate\Support\ServiceProvider;

class Bladewind<Name>ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bladewind.php', 'bladewind');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'bladewind');

        $this->publishes([
            __DIR__.'/../resources/views/components/' => resource_path('views/components/bladewind'),
        ], 'bladewind-components');
    }
}
```

Component providers publish views and nothing else. Assets are core's job.

The class name in the package's own `extra.laravel.providers` must match this class exactly. Nothing in the monorepo exercises that entry — the root `composer.json` has its own list — so a wrong name stays invisible here and fatals on boot for anyone installing the split package. `php bin/validate-providers.php` checks every declaration and runs in CI.
