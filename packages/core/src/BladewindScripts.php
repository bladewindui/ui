<?php

namespace Mkocansey\Bladewind\Core;

/**
 * Resolves which BladewindUI scripts a page needs.
 *
 * Consumers were hand-picking <script src="…"> tags per layout and keeping them
 * in step with whichever components that layout happened to use — see #595. The
 * @bladewindScripts directive takes component names instead:
 *
 *     @bladewindScripts
 *     @bladewindScripts('select', 'dropmenu')
 *
 * helpers.js always comes first, because everything else assumes it.
 */
class BladewindScripts
{
    /**
     * Component name => the scripts it needs, in load order.
     *
     * Heavy dependencies — chart.js, FilePond, cropper — are deliberately absent.
     * Those components emit their own <script src> when they render, so a page
     * that does not use them never fetches them.
     */
    public const MAP = [
        'select' => ['select.js'],
        'dropmenu' => ['dropmenu.js'],
        'datepicker' => ['datepicker.js'],
        'table' => ['table.js'],
        'notification' => ['notification.js'],
        'mask' => ['mask.js'],
        'animations' => ['animations.js'],
        'sortable' => ['sortable.min.js'],
    ];

    /**
     * @param  list<string>  $components
     * @return list<string> script filenames, deduplicated, helpers first
     */
    public static function resolve(array $components = []): array
    {
        $scripts = ['helpers.js'];

        foreach ($components as $component) {
            $key = strtolower(trim((string) $component));

            foreach (self::MAP[$key] ?? [] as $script) {
                $scripts[] = $script;
            }
        }

        return array_values(array_unique($scripts));
    }

    /**
     * @param  list<string>  $components
     */
    public static function tags(array $components = [], ?string $nonce = null): string
    {
        $nonce ??= config('bladewind.script.nonce');
        $attribute = $nonce ? ' nonce="'.e($nonce).'"' : '';

        return implode("\n", array_map(
            fn (string $script): string => sprintf(
                '<script src="%s"%s></script>',
                e(asset('vendor/bladewind/js/'.$script)),
                $attribute
            ),
            self::resolve($components)
        ));
    }

    /** @return list<string> every component name the directive understands */
    public static function known(): array
    {
        return array_keys(self::MAP);
    }
}
