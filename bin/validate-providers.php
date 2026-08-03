<?php

/**
 * Every class listed in a package's extra.laravel.providers must exist.
 *
 * Laravel's package discovery copies these strings into the package manifest
 * without checking them, so a name that does not resolve is not caught until
 * the application boots — and then it is a fatal "Class not found" on every
 * request. The monorepo itself never notices, because the root composer.json
 * carries its own (correct) list; only the split single-component packages
 * break. See the 32 packages fixed in the commit that added this script.
 *
 * Run: php bin/validate-providers.php
 */

$root = dirname(__DIR__);
$errors = [];
$checked = 0;

$manifests = glob("$root/packages/*/composer.json");
$manifests[] = "$root/composer.json";

foreach ($manifests as $manifest) {
    $name = substr($manifest, strlen($root) + 1);
    $json = json_decode(file_get_contents($manifest), true);

    if ($json === null) {
        $errors[] = "$name: invalid JSON";
        continue;
    }

    $isRoot = $manifest === "$root/composer.json";

    foreach ($json['extra']['laravel']['providers'] ?? [] as $provider) {
        $checked++;
        $class = substr(strrchr($provider, '\\'), 1);

        // the root package replaces every split package, so its providers may
        // live in any packages/*/src directory
        $found = $isRoot
            ? glob("$root/packages/*/src/$class.php")
            : array_filter([dirname($manifest)."/src/$class.php"], 'file_exists');

        if (! $found) {
            $errors[] = "$name: $provider has no matching class file";
            continue;
        }

        $namespace = substr($provider, 0, strrpos($provider, '\\'));
        $source = file_get_contents(reset($found));

        if (! preg_match('/^namespace\s+'.preg_quote($namespace, '/').';/m', $source)) {
            $errors[] = "$name: $provider is declared in a different namespace";
        }
    }
}

if ($errors) {
    echo "Broken service provider declarations:\n\n";
    foreach ($errors as $error) {
        echo "  $error\n";
    }
    echo "\n";
    exit(1);
}

echo "OK: $checked service provider declarations resolve\n";
