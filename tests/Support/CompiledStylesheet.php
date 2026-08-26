<?php

namespace Mkocansey\Bladewind\Tests\Support;

use RuntimeException;

/**
 * A very small reader for the compiled bundle.
 *
 * Tier B of improvements.md item 10 asserts on the *output* of `npm run build`
 * rather than on Blade templates, because the Tailwind v4 co-existence failure
 * lives in the cascade: no assertion on rendered markup can see an input whose
 * border resolves to nothing once the host app's own build lands.
 *
 * This is deliberately not a CSS parser. It splits declaration blocks and reads
 * @layer blocks by brace matching, which is enough for the invariants below and
 * has no dependency to keep current.
 */
class CompiledStylesheet
{
    public const PATH = __DIR__.'/../../packages/core/public/css/bladewind-ui.min.css';

    private string $css;

    public function __construct(?string $css = null)
    {
        $this->css = $css ?? self::read();
    }

    public static function read(): string
    {
        $path = realpath(self::PATH);

        if ($path === false) {
            throw new RuntimeException(
                'Compiled bundle not found at '.self::PATH.'. Run `npm run build` first.'
            );
        }

        return file_get_contents($path);
    }

    public function raw(): string
    {
        return $this->css;
    }

    /**
     * The contents of a top-level @layer block, by brace matching.
     */
    public function layer(string $name): string
    {
        $open = '@layer '.$name.'{';
        $start = strpos($this->css, $open);

        if ($start === false) {
            return '';
        }

        $i = $start + strlen($open);
        $depth = 1;

        while ($depth > 0 && $i < strlen($this->css)) {
            if ($this->css[$i] === '{') {
                $depth++;
            } elseif ($this->css[$i] === '}') {
                $depth--;
            }
            $i++;
        }

        return substr($this->css, $start + strlen($open), $i - $start - strlen($open) - 1);
    }

    /**
     * Every declaration block in the given scope, as selector + declarations.
     *
     * @return list<array{selector: string, declarations: string}>
     */
    public function rules(?string $scope = null): array
    {
        preg_match_all('/([^{}@][^{}]*)\{([^{}]*)\}/', $scope ?? $this->css, $matches, PREG_SET_ORDER);

        return array_map(fn (array $m): array => [
            'selector' => trim(preg_replace('/\s+/', ' ', $m[1])),
            'declarations' => $m[2],
        ], $matches);
    }

    /**
     * Rules whose selector mentions a BladeWind component class.
     *
     * @return list<array{selector: string, declarations: string}>
     */
    public function componentRules(): array
    {
        return array_values(array_filter(
            $this->rules(),
            fn (array $rule): bool => str_contains($rule['selector'], '.bw-')
        ));
    }

    /**
     * The first rule whose selector is exactly $selector.
     */
    public function rule(string $selector): ?string
    {
        foreach ($this->rules() as $rule) {
            if ($rule['selector'] === $selector) {
                return $rule['declarations'];
            }
        }

        return null;
    }

    /**
     * All declarations across every rule whose selector contains $needle.
     */
    public function declarationsMatching(string $needle): string
    {
        $out = '';

        foreach ($this->rules() as $rule) {
            if (str_contains($rule['selector'], $needle)) {
                $out .= $rule['declarations'].';';
            }
        }

        return $out;
    }

    /**
     * Custom properties this stylesheet defines anywhere.
     *
     * @return list<string>
     */
    public function definedCustomProperties(): array
    {
        preg_match_all('/(--[a-zA-Z0-9-]+)\s*:/', $this->css, $m);

        return array_values(array_unique($m[1]));
    }

    /**
     * Custom properties registered with @property, mapped to their block body.
     *
     * @return array<string, string>
     */
    public function registeredCustomProperties(): array
    {
        preg_match_all('/@property\s+(--[a-zA-Z0-9-]+)\s*\{([^{}]*)\}/', $this->css, $m, PREG_SET_ORDER);

        $out = [];
        foreach ($m as $match) {
            $out[$match[1]] = $match[2];
        }

        return $out;
    }

    /**
     * Custom properties referenced by .bw-* rules through a var() with no
     * fallback — the exact shape that resolves to nothing when the host app's
     * theme does not define the token.
     *
     * @return array<string, int> property => reference count
     */
    public function unfallbackedTokensInComponentRules(): array
    {
        $counts = [];

        foreach ($this->componentRules() as $rule) {
            preg_match_all('/var\(\s*(--[a-zA-Z0-9-]+)\s*(,)?/', $rule['declarations'], $m, PREG_SET_ORDER);

            foreach ($m as $match) {
                if (isset($match[2])) {
                    continue; // has a fallback
                }

                $counts[$match[1]] = ($counts[$match[1]] ?? 0) + 1;
            }
        }

        ksort($counts);

        return $counts;
    }
}
