<?php

namespace Mkocansey\Bladewind\Tests\Concerns;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Assert;

/**
 * Render a component and make assertions against the resulting DOM.
 *
 * Class assertions here compare individual tokens rather than whole class
 * strings. Items 1-3 in improvements.md reorder and re-weight class strings,
 * and a substring assertion on class="" would fail on a harmless reorder while
 * happily missing a token that genuinely disappeared.
 */
trait RendersComponents
{
    /**
     * Note on slots: keep whitespace after a closing slot tag. Blade compiles
     * </x-slot> to @endslot, so "</x-slot>body" becomes "@endslotbody", which the
     * directive parser reads as one unknown directive — the slot never closes and
     * its content silently lands in $slot instead.
     */
    protected function render(string $blade, array $data = []): string
    {
        return Blade::render($blade, $data);
    }

    /**
     * Assert every given class token is present on the first node matching $xpath.
     */
    protected function assertHasClasses(string $html, string $xpath, array $classes): void
    {
        $actual = $this->classesOf($html, $xpath);

        foreach ($classes as $class) {
            Assert::assertContains(
                $class,
                $actual,
                "Expected class [{$class}] on [{$xpath}]. Found: ".implode(' ', $actual)
            );
        }
    }

    /**
     * Assert none of the given class tokens are present on the first node matching $xpath.
     */
    protected function assertMissingClasses(string $html, string $xpath, array $classes): void
    {
        $actual = $this->classesOf($html, $xpath);

        foreach ($classes as $class) {
            Assert::assertNotContains(
                $class,
                $actual,
                "Did not expect class [{$class}] on [{$xpath}]. Found: ".implode(' ', $actual)
            );
        }
    }

    protected function assertAttribute(string $html, string $xpath, string $attribute, ?string $expected): void
    {
        $node = $this->firstNode($html, $xpath);

        if ($expected === null) {
            Assert::assertFalse(
                $node->hasAttribute($attribute),
                "Did not expect attribute [{$attribute}] on [{$xpath}]."
            );

            return;
        }

        Assert::assertSame(
            $expected,
            $node->getAttribute($attribute),
            "Attribute [{$attribute}] on [{$xpath}] did not match."
        );
    }

    protected function assertAttributeContains(string $html, string $xpath, string $attribute, string $needle): void
    {
        $node = $this->firstNode($html, $xpath);

        Assert::assertStringContainsString(
            $needle,
            $node->getAttribute($attribute),
            "Attribute [{$attribute}] on [{$xpath}] did not contain [{$needle}]."
        );
    }

    protected function assertElementCount(string $html, string $xpath, int $expected): void
    {
        Assert::assertCount($expected, iterator_to_array($this->query($html, $xpath)));
    }

    protected function assertNoElement(string $html, string $xpath): void
    {
        $this->assertElementCount($html, $xpath, 0);
    }

    /**
     * XPath helper: nodes of $tag carrying $class as a whole class token.
     */
    protected function withClass(string $class, string $tag = '*'): string
    {
        return "//{$tag}[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')]";
    }

    /** @return list<string> */
    protected function classesOf(string $html, string $xpath): array
    {
        return preg_split(
            '/\s+/',
            trim($this->firstNode($html, $xpath)->getAttribute('class')),
            -1,
            PREG_SPLIT_NO_EMPTY
        );
    }

    protected function firstNode(string $html, string $xpath): DOMElement
    {
        $nodes = $this->query($html, $xpath);

        Assert::assertGreaterThan(
            0,
            $nodes->length,
            "No node matched [{$xpath}] in rendered output:\n".$this->preview($html)
        );

        return $nodes->item(0);
    }

    protected function query(string $html, string $xpath): \DOMNodeList
    {
        $document = new DOMDocument();

        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><body>'.$html.'</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return (new DOMXPath($document))->query($xpath);
    }

    private function preview(string $html): string
    {
        $html = trim(preg_replace('/\n\s*\n/', "\n", $html));

        return strlen($html) > 2000 ? substr($html, 0, 2000).'…' : $html;
    }
}
