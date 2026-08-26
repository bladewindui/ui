<?php

namespace Mkocansey\Bladewind\Tests\Core;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * The move to bladewindui/bladewindui renames the Composer package and nothing
 * else. These pin the identifiers that consuming applications actually depend
 * on, so a later tidy-up cannot quietly break every install.
 *
 * The Blade namespace is the one that matters most: renaming it would rewrite
 * every component tag in every consuming app. One audited application alone has
 * roughly 4,600 of them.
 */
class PackageIdentityTest extends TestCase
{
    use RendersComponents;

    private function rootComposer(): array
    {
        return json_decode(file_get_contents(__DIR__.'/../../composer.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    #[Test]
    public function the_package_is_named_for_the_new_org(): void
    {
        $this->assertSame('bladewindui/bladewindui', $this->rootComposer()['name']);
    }

    /**
     * Anything still depending on the old name — an application, or a third-party
     * package — has to be satisfied by installing this one.
     */
    #[Test]
    public function the_former_package_name_is_replaced(): void
    {
        $replace = $this->rootComposer()['replace'];

        // monorepo-builder rewrites every "self.version" placeholder to the literal
        // released version at release time, so this only pins that the former name
        // tracks the same marker as everything else in the block - not a literal
        // string, which flips between "self.version" and a version number depending
        // on whether a release has run since the branch was last touched.
        $this->assertArrayHasKey('mkocansey/bladewind', $replace);
        $this->assertSame(reset($replace), $replace['mkocansey/bladewind']);
    }

    #[Test]
    public function every_granular_package_is_still_replaced(): void
    {
        $replace = $this->rootComposer()['replace'];
        $packages = glob(__DIR__.'/../../packages/*', GLOB_ONLYDIR);

        // packages/meta is deliberately not split and so is not replaced
        $expected = count($packages) - 1;

        $this->assertGreaterThanOrEqual($expected, count($replace) - 1);
    }

    /**
     * x-bladewind::card resolves through the 'bladewind' view namespace. This is
     * the single most expensive thing to change and must not move with the org.
     */
    #[Test]
    public function the_blade_namespace_is_unchanged(): void
    {
        $html = $this->render('<x-bladewind::card>identity</x-bladewind::card>');

        $this->assertStringContainsString('identity', $html);
        $this->assertHasClasses($html, $this->withClass('bw-card'), ['bw-card']);
    }

    #[Test]
    public function the_config_key_is_unchanged(): void
    {
        $this->assertIsArray(config('bladewind.card'));
        $this->assertIsArray(config('bladewind.forms'));
    }

    #[Test]
    public function the_php_namespace_is_unchanged(): void
    {
        $autoload = $this->rootComposer()['autoload']['psr-4'];

        $this->assertArrayHasKey('Mkocansey\\Bladewind\\Card\\', $autoload);
        $this->assertArrayHasKey('Mkocansey\\Bladewind\\', $autoload);
    }

    /**
     * Consuming apps reference published assets by URL and scan the vendor path
     * for Tailwind sources. Both are keyed on 'bladewind', not on the org.
     */
    #[Test]
    public function the_published_asset_and_lang_paths_are_unchanged(): void
    {
        $provider = file_get_contents(__DIR__.'/../../packages/core/src/BladewindCoreServiceProvider.php');

        $this->assertStringContainsString("public_path('vendor/bladewind')", $provider);
        $this->assertStringContainsString("lang_path('vendor/bladewind')", $provider);
    }

    /**
     * The 2026-06-08 incident was a split entry that resolved to the monorepo's
     * own remote. The forbidden pairing moved with the repo; this checks no
     * matrix entry can currently reach it.
     */
    #[Test]
    public function no_split_target_resolves_to_this_monorepo(): void
    {
        $workflow = file_get_contents(__DIR__.'/../../.github/workflows/split-packages.yml');

        preg_match("/repository_organization: '([^']+)'/", $workflow, $org);
        preg_match_all("/split_repository: '([^']+)'/", $workflow, $repos);

        $this->assertNotEmpty($org, 'split-packages.yml declares no organization');

        $forbidden = $org[1] === 'bladewindui' ? 'bladewindui' : 'bladewind';

        $this->assertNotContains(
            $forbidden,
            $repos[1],
            "A split entry targets {$org[1]}/{$forbidden}, which is this monorepo's own remote. "
            .'Splitting into it force-pushes filtered history over main — this wiped main on 2026-06-08.'
        );
    }
}
