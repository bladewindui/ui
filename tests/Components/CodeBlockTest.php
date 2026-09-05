<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CodeBlockTest extends TestCase
{
    use RendersComponents;

    private const ROOT = '//div[contains(@class, "bw-code-block")]';
    private const PRE = '//pre';
    private const CODE = '//code[@data-bw-code-block-source]';
    private const COPY = '//button[@data-bw-code-block-copy]';

    private function markup(string $attrs = '', ?string $slot = null): string
    {
        $slot ??= 'echo hello;';

        return <<<BLADE
            <x-bladewind::code-block {$attrs}>{$slot}</x-bladewind::code-block>
        BLADE;
    }

    #[Test]
    public function it_renders_the_code_in_a_pre_and_code_element(): void
    {
        $html = $this->render($this->markup());

        $this->assertElementCount($html, self::ROOT, 1);
        $this->assertElementCount($html, self::PRE, 1);
        $this->assertElementCount($html, self::CODE, 1);
        $this->assertStringContainsString('echo hello;', $html);
    }

    #[Test]
    public function it_dedents_a_slot_indented_to_match_surrounding_markup(): void
    {
        $html = $this->render(
            "<x-bladewind::code-block>\n                function foo() {\n                    return 1;\n                }\n            </x-bladewind::code-block>"
        );

        $this->assertStringContainsString("function foo() {\n    return 1;\n}", $html);
    }

    #[Test]
    public function a_code_attribute_is_used_over_the_slot_and_is_escaped(): void
    {
        $html = $this->render('<x-bladewind::code-block code="<div>hi</div>"></x-bladewind::code-block>');

        $this->assertStringContainsString('&lt;div&gt;hi&lt;/div&gt;', $html);
    }

    #[Test]
    public function default_language_is_markup(): void
    {
        $html = $this->render($this->markup());

        $this->assertHasClasses($html, self::PRE, ['language-markup']);
        $this->assertHasClasses($html, self::CODE, ['language-markup']);
    }

    #[Test]
    public function language_sets_the_language_class_and_label(): void
    {
        $html = $this->render($this->markup('language="php"'));

        $this->assertHasClasses($html, self::PRE, ['language-php']);
        $this->assertStringContainsString('php', $html);
    }

    #[Test]
    public function line_numbers_false_by_default(): void
    {
        $html = $this->render($this->markup());

        $this->assertMissingClasses($html, self::PRE, ['line-numbers']);
    }

    #[Test]
    public function line_numbers_true_adds_the_plugin_class(): void
    {
        $html = $this->render($this->markup('line-numbers="true"'));

        $this->assertHasClasses($html, self::PRE, ['line-numbers']);
    }

    #[Test]
    public function highlight_lines_sets_the_data_line_attribute(): void
    {
        $html = $this->render($this->markup('highlight-lines="2-3"'));

        $this->assertAttribute($html, self::PRE, 'data-line', '2-3');
    }

    #[Test]
    public function wrap_true_adds_wrapping_classes(): void
    {
        $html = $this->render($this->markup('wrap="true"'));

        $this->assertHasClasses($html, self::PRE, ['!whitespace-pre-wrap']);
    }

    #[Test]
    public function it_shows_a_copy_button_by_default(): void
    {
        $html = $this->render($this->markup());

        $this->assertElementCount($html, self::COPY, 1);
    }

    #[Test]
    public function show_copy_false_hides_the_copy_button(): void
    {
        $html = $this->render($this->markup('show-copy="false"'));

        $this->assertNoElement($html, self::COPY);
    }

    #[Test]
    public function it_renders_a_title_when_given(): void
    {
        $html = $this->render($this->markup('title="routes/web.php"'));

        $this->assertStringContainsString('routes/web.php', $html);
    }

    #[Test]
    public function show_language_label_false_hides_the_label(): void
    {
        $html = $this->render($this->markup('show-language-label="false"'));

        $this->assertStringNotContainsString('>markup<', $html);
    }

    #[Test]
    public function additional_classes_are_applied(): void
    {
        $html = $this->render($this->markup('class="my-code-block"'));

        $this->assertHasClasses($html, self::ROOT, ['my-code-block']);
    }
}
