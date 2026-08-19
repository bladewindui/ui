<?php

namespace Mkocansey\Bladewind\Core;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;

class BladewindCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerAttributeBagMacros();

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'bladewind');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/bladewind'),
        ], 'bladewind-lang');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/bladewind'),
        ], 'bladewind-public');
    }

    /**
     * Stop snake_case attribute names being rendered onto component roots.
     *
     * Blade's tag compiler camel-cases an attribute name into the component's data,
     * so has_shadow="false" correctly sets $hasShadow. But ComponentAttributeBag::
     * extractPropNames() — which is what @props uses to decide what to remove from
     * the bag — only ever produces the camelCase and kebab-case spellings. The
     * snake_case key therefore survives in $attributes and renders onto the root
     * element as a literal attribute: <div class="bw-card …" has_shadow="false">.
     *
     * Kebab-case never had the problem. Snake_case does, and snake_case is what the
     * documentation uses throughout, so in practice nearly every consumer has these
     * in their markup. See #607.
     *
     * The rule is: drop a key that is snake_case and whose camelCase spelling is a
     * variable in the calling view's scope. Note that Blade camel-cases *every*
     * attribute into the component's data, not only declared props, so in practice
     * this removes every snake_case attribute passed to a BladeWind component —
     * there is no runtime signal that separates "snake alias of a declared prop"
     * from "arbitrary underscored attribute", because both produce a variable.
     *
     * That is an acceptable trade: attribute names containing underscores are not
     * valid HTML in the first place, and consumers wanting a custom attribute on a
     * component root have data-* and kebab-case, both of which pass through
     * untouched.
     *
     * Usage, at the point the bag is rendered:
     *
     *     {{ $attributes->exceptPropAliases(get_defined_vars())->merge([...]) }}
     *
     * get_defined_vars() has to be passed in because a macro cannot see the calling
     * view's scope.
     */
    private function registerAttributeBagMacros(): void
    {
        if (ComponentAttributeBag::hasMacro('exceptPropAliases')) {
            return;
        }

        ComponentAttributeBag::macro('exceptPropAliases', function (array $scope = []) {
            $aliases = [];

            foreach (array_keys($this->getAttributes()) as $key) {
                if (! is_string($key) || ! str_contains($key, '_')) {
                    continue;
                }

                $camel = Str::camel($key);

                if ($camel !== $key && array_key_exists($camel, $scope)) {
                    $aliases[] = $key;
                }
            }

            return $aliases === [] ? $this : $this->except($aliases);
        });
    }
}
