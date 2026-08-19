<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * #607: Blade's tag compiler camel-cases an attribute name into the component's
 * data, so has_shadow="false" correctly sets $hasShadow — but @props only strips
 * the camelCase and kebab-case spellings from the attribute bag, so the snake_case
 * key survived and rendered onto the root element as a literal attribute.
 *
 * Every component that renders the attribute bag is covered here. If you add a
 * component that spreads $attributes, add it to the provider — and remember the
 * ->exceptPropAliases(get_defined_vars()) call, which is what makes it pass.
 */
class PropAliasLeakTest extends TestCase
{
    use RendersComponents;

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function componentProvider(): array
    {
        return [
            'button' => ['<x-bladewind::button can_submit="true">Go</x-bladewind::button>', 'can_submit'],
            'bell' => ['<x-bladewind::bell has_notifications="true" />', 'has_notifications'],
            'card' => ['<x-bladewind::card has_shadow="false">c</x-bladewind::card>', 'has_shadow'],
            'centered-content' => ['<x-bladewind::centered-content add_clearing="false">c</x-bladewind::centered-content>', 'add_clearing'],
            'input' => ['<x-bladewind::input name="e" add_clearing="false" />', 'add_clearing'],
            'textarea' => ['<x-bladewind::textarea name="t" add_clearing="false" />', 'add_clearing'],
            'statistic' => ['<x-bladewind::statistic title="t" value="1" has_shadow="false" />', 'has_shadow'],
            'sortable' => ['<x-bladewind::sortable name="s" has_handle="true"></x-bladewind::sortable>', 'has_handle'],
        ];
    }

    #[Test]
    #[DataProvider('componentProvider')]
    public function a_snake_case_prop_is_not_rendered_as_an_attribute(string $blade, string $prop): void
    {
        $html = $this->render($blade);

        $this->assertStringNotContainsString(
            $prop.'="',
            $html,
            "The snake_case prop [{$prop}] leaked into the rendered HTML as a literal attribute. "
            .'The component needs ->exceptPropAliases(get_defined_vars()) where it renders $attributes.'
        );
    }

    /**
     * The kebab spelling never leaked, and must keep working.
     */
    #[Test]
    public function the_kebab_case_spelling_still_applies_and_does_not_leak(): void
    {
        $html = $this->render('<x-bladewind::card has-shadow="false">c</x-bladewind::card>');

        $this->assertStringNotContainsString('has-shadow=', $html);
        $this->assertMissingClasses($html, $this->withClass('bw-card'), ['shadowed']);
    }

    /**
     * The snake spelling must still *apply*, not merely stop leaking.
     */
    #[Test]
    public function the_snake_case_spelling_still_applies(): void
    {
        $html = $this->render('<x-bladewind::card has_shadow="false">c</x-bladewind::card>');

        $this->assertMissingClasses($html, $this->withClass('bw-card'), ['shadowed']);
    }

    /**
     * Every snake_case attribute goes, not only aliases of declared props.
     *
     * Blade camel-cases every attribute into the component's data, so there is no
     * runtime signal separating "snake alias of a prop" from "arbitrary underscored
     * attribute" — both produce a variable in scope. Underscored attribute names are
     * not valid HTML anyway, and data-* and kebab-case both survive, so this is the
     * documented behaviour rather than a gap.
     */
    #[Test]
    public function an_arbitrary_underscored_attribute_is_also_dropped(): void
    {
        $html = $this->render('<x-bladewind::card my_custom_attr="keep">c</x-bladewind::card>');

        $this->assertStringNotContainsString('my_custom_attr', $html);
    }

    #[Test]
    public function the_kebab_and_data_spellings_are_the_supported_way_to_pass_custom_attributes(): void
    {
        $html = $this->render(
            '<x-bladewind::card data-role="panel" my-custom-attr="keep">c</x-bladewind::card>'
        );

        $this->assertAttribute($html, $this->withClass('bw-card'), 'data-role', 'panel');
        $this->assertAttribute($html, $this->withClass('bw-card'), 'my-custom-attr', 'keep');
    }

    #[Test]
    public function ordinary_attributes_are_untouched(): void
    {
        $html = $this->render('<x-bladewind::card id="c1" data-role="panel" class="mt-8">c</x-bladewind::card>');

        $this->assertAttribute($html, $this->withClass('bw-card'), 'id', 'c1');
        $this->assertAttribute($html, $this->withClass('bw-card'), 'data-role', 'panel');
        $this->assertHasClasses($html, $this->withClass('bw-card'), ['mt-8', 'bw-card']);
    }
}
