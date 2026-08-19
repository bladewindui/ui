<?php

namespace Mkocansey\Bladewind\Tests\Core;

use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * #599 — the published config is the place to set house style once, so every key
 * a component actually reads has to be discoverable there.
 *
 * When this first ran it found 21 keys that components read but the published
 * config never documented, across sortable, tag, statistic, avatar, datepicker,
 * input and selectable_card. A consumer had no way to find them short of reading
 * the blade source.
 */
class ConfigSurfaceTest extends TestCase
{
    /**
     * @return list<string> every bladewind.* key read by a component template
     */
    private function keysReadByComponents(): array
    {
        $root = __DIR__.'/../..';
        $files = array_merge(
            glob($root.'/packages/*/resources/views/components/*.blade.php'),
            glob($root.'/packages/*/resources/views/components/**/*.blade.php'),
        );

        $keys = [];

        foreach ($files as $file) {
            preg_match_all(
                '/config\(\s*[\'"]bladewind\.([a-z0-9_.]+)[\'"]/i',
                file_get_contents($file),
                $matches
            );

            foreach ($matches[1] as $key) {
                $keys[$key] = true;
            }
        }

        ksort($keys);

        return array_keys($keys);
    }

    private function publishedConfig(): array
    {
        return require __DIR__.'/../../packages/meta/config/bladewind.php';
    }

    private function has(array $config, string $key): bool
    {
        foreach (explode('.', $key) as $segment) {
            if (! is_array($config) || ! array_key_exists($segment, $config)) {
                return false;
            }

            $config = $config[$segment];
        }

        return true;
    }

    #[Test]
    public function every_config_key_a_component_reads_is_in_the_published_config(): void
    {
        $config = $this->publishedConfig();

        $missing = array_values(array_filter(
            $this->keysReadByComponents(),
            fn (string $key): bool => ! $this->has($config, $key)
        ));

        $this->assertSame(
            [],
            $missing,
            "These keys are read by a component but absent from packages/meta/config/bladewind.php,\n"
            ."so a consumer cannot discover them without reading the blade source:\n  "
            .implode("\n  ", $missing)
        );
    }

    /**
     * The runtime default and the published default have to agree, or setting the
     * documented value is a no-op and changing it does something unexpected.
     */
    #[Test]
    public function the_published_config_is_loaded_and_merged_at_runtime(): void
    {
        $this->assertIsArray(config('bladewind.card'));
        $this->assertIsArray(config('bladewind.forms'));
        $this->assertSame(config('bladewind.card.has_shadow'), true);
    }

    #[Test]
    public function the_component_packages_and_the_published_config_do_not_disagree(): void
    {
        $published = $this->publishedConfig();
        $conflicts = [];

        foreach (glob(__DIR__.'/../../packages/*/config/bladewind.php') as $file) {
            if (str_contains($file, '/meta/')) {
                continue;
            }

            foreach (require $file as $group => $values) {
                if (! is_array($values) || ! isset($published[$group]) || ! is_array($published[$group])) {
                    continue;
                }

                foreach ($values as $key => $value) {
                    if (is_array($value) || ! array_key_exists($key, $published[$group])) {
                        continue;
                    }

                    if ($published[$group][$key] !== $value) {
                        $conflicts[] = sprintf(
                            '%s.%s — package says %s, published config says %s',
                            $group,
                            $key,
                            var_export($value, true),
                            var_export($published[$group][$key], true)
                        );
                    }
                }
            }
        }

        $this->assertSame([], $conflicts, implode("\n  ", $conflicts));
    }
}
