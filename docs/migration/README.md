# Moving from `mkocansey/bladewind` to `bladewindui/bladewindui`

`old-package-composer.json` is the entire contents of the old repository's
`composer.json` after the move. Committing it to `mkocansey/bladewind` and tagging
`4.4.0` turns that package into a metapackage that forwards to the new one.

The effect for an application that still requires `mkocansey/bladewind`:

    composer update

pulls the metapackage, which pulls `bladewindui/bladewindui`, which installs the real
code. Nothing in the application changes — the PHP namespace, the `x-bladewind::` tags,
`config/bladewind.php` and `public/vendor/bladewind` are all untouched by the move.

They change the name in their own `composer.json` when they feel like it:

```diff
-        "mkocansey/bladewind": "^4.3"
+        "bladewindui/bladewindui": "^4.4"
```

## The one real breakage

A hardcoded vendor path. Tailwind v4 applications commonly scan the package for the
utility classes used inside BladeWind's own templates:

```diff
-@source '../../vendor/mkocansey/bladewind/packages';
+@source '../../vendor/bladewindui/bladewindui/packages';
```

Miss this and the build still succeeds — it just stops generating those utilities, and
styles quietly disappear. Lead the release notes with it.

Anything else reaching into `vendor/mkocansey/bladewind` has the same problem: deploy
scripts, asset pipelines, IDE helper config, static analysis paths.

## Order of operations

See "Moving the package to `bladewindui/bladewindui`" in `RELEASING.md`. The short
version: the new repo and its Packagist registration must exist **before** the renamed
`composer.json` reaches `main`, or the old package's webhook fails on a name mismatch.
