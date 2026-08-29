# Moving to `bladewindui/ui`

This is the package's **third** name. `mkocansey/bladewind` moved to
`bladewindui/bladewindui` when the project moved to the `bladewindui` GitHub org;
`bladewindui/bladewindui` now moves to `bladewindui/ui`, dropping the doubled-up name.
Both old names keep working via metapackage shims — nobody has to migrate twice, or
at all, unless they want the shorter name.

`old-package-composer.json` is the entire contents of `mkocansey/bladewind`'s
`composer.json` after the move — a metapackage requiring `bladewindui/ui` directly
(it never shipped pointing at `bladewindui/bladewindui`, so there is no need to hop
through that name). Committing it to `mkocansey/bladewind` and tagging turns that
repo into a metapackage that forwards straight to the current package.

`old-bladewindui-package-composer.json` is the same idea for `bladewindui/bladewindui`,
which **did** ship — release `4.4.0` came from it. Committing it there (again,
requiring `bladewindui/ui` directly) and tagging turns that repo into a metapackage too.

The effect for an application that requires either old name:

    composer update

pulls the metapackage, which pulls `bladewindui/ui`, which installs the real code.
Nothing in the application changes — the PHP namespace, the `x-bladewind::` tags,
`config/bladewind.php` and `public/vendor/bladewind` are all untouched by the move.

They change the name in their own `composer.json` when they feel like it:

```diff
-        "bladewindui/bladewindui": "^4.4"
+        "bladewindui/ui": "^4.4"
```

## The one real breakage

A hardcoded vendor path. Tailwind v4 applications commonly scan the package for the
utility classes used inside BladeWind's own templates:

```diff
-@source '../../vendor/bladewindui/bladewindui/packages';
+@source '../../vendor/bladewindui/ui/packages';
```

Miss this and the build still succeeds — it just stops generating those utilities, and
styles quietly disappear. Lead the release notes with it.

Anything else reaching into `vendor/bladewindui/bladewindui` (or the older
`vendor/mkocansey/bladewind`) has the same problem: deploy scripts, asset pipelines,
IDE helper config, static analysis paths.

## The 48 leaf packages moved too

`mkocansey/bladewind-<name>` → `bladewindui/<name>` happens in the same change. See
"The split mirrors already moved" in `RELEASING.md` for the shim pattern used there —
it's the same idea, one metapackage per old repo.

## Order of operations

See "Moving the package to `bladewindui/ui`" in `RELEASING.md`. The short version: the
new repo and its Packagist registration must exist **before** the renamed
`composer.json` reaches `main`, or the old package's webhook fails on a name mismatch.
