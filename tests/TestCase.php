<?php

namespace Mkocansey\Bladewind\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RuntimeException;

abstract class TestCase extends Orchestra
{
    /**
     * Every component provider declared in the root composer.json.
     *
     * Reading the list from composer.json rather than hard-coding it here means a
     * component added per RELEASING.md is under test the moment its provider is
     * registered — and a provider that is dropped or misspelled fails here too,
     * not only in bin/validate-providers.php.
     */
    protected function getPackageProviders($app): array
    {
        $composer = json_decode(
            file_get_contents(__DIR__.'/../composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $providers = $composer['extra']['laravel']['providers'] ?? [];

        if ($providers === []) {
            throw new RuntimeException('No providers declared in composer.json extra.laravel.providers');
        }

        return $providers;
    }

    /**
     * Point public_path() at the core package's published assets.
     *
     * The icon component reads SVGs off disk with public_path(), so without this
     * every icon renders empty and icon-bearing components silently lose content.
     * tests/fixtures/public/vendor/bladewind is a symlink to packages/core/public
     * rather than a copy — RELEASING.md is explicit that duplicated asset
     * directories drift, and select.js already proved it.
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app->usePublicPath(realpath(__DIR__.'/fixtures/public'));
    }
}
