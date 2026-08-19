<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * #593 — pagination against a LengthAwarePaginator.
 *
 * The DOM show/hide mode is untouched and still the default; handing the
 * component a paginator switches it over.
 */
class PaginationTest extends TestCase
{
    use RendersComponents;

    private function paginator(int $total = 100, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            range(1, min($perPage, $total)),
            $total,
            $perPage,
            $page,
            ['path' => 'http://localhost/orders']
        );
    }

    private function renderPaginator(LengthAwarePaginator|Paginator $p, string $attrs = ''): string
    {
        return $this->render('<x-bladewind::pagination :paginator="$p" '.$attrs.' />', ['p' => $p]);
    }

    #[Test]
    public function it_renders_server_side_links_for_a_paginator(): void
    {
        $html = $this->renderPaginator($this->paginator(page: 3));

        $this->assertElementCount($html, $this->withClass('bw-pagination-server'), 1);
        // page 2 is linked twice on page 3 — once as the previous arrow, once as
        // its own number — so scope these to the numbered links
        $this->assertElementCount($html, $this->withClass('page', 'a').'[contains(@href,"page=2")]', 1);
        $this->assertElementCount($html, $this->withClass('page', 'a').'[contains(@href,"page=4")]', 1);
        $this->assertAttribute($html, $this->withClass('prev-btn'), 'rel', 'prev');
    }

    #[Test]
    public function it_shows_where_the_reader_is(): void
    {
        $html = $this->renderPaginator($this->paginator(total: 95, perPage: 10, page: 3));

        $summary = $this->firstNode($html, $this->withClass('pagination-summary'))->textContent;

        $this->assertStringContainsString('21', $summary);
        $this->assertStringContainsString('30', $summary);
        $this->assertStringContainsString('95', $summary);
    }

    /**
     * First and last always reachable, ellipsis for the gap — the thing prev/next
     * alone cannot give you.
     */
    #[Test]
    public function first_and_last_pages_stay_reachable_with_an_ellipsis_between(): void
    {
        $html = $this->renderPaginator($this->paginator(total: 1000, perPage: 10, page: 50));

        $pages = array_map(
            fn ($n) => trim($n->textContent),
            iterator_to_array($this->query($html, $this->withClass('page')))
        );

        $this->assertSame(['1', '49', '50', '51', '100'], $pages);
        $this->assertElementCount($html, $this->withClass('dots'), 2);
    }

    #[Test]
    public function there_is_no_ellipsis_when_the_window_reaches_the_edges(): void
    {
        $html = $this->renderPaginator($this->paginator(total: 30, perPage: 10, page: 2));

        $pages = array_map(
            fn ($n) => trim($n->textContent),
            iterator_to_array($this->query($html, $this->withClass('page')))
        );

        $this->assertSame(['1', '2', '3'], $pages);
        $this->assertNoElement($html, $this->withClass('dots'));
    }

    #[Test]
    public function on_each_side_widens_the_window(): void
    {
        $html = $this->renderPaginator($this->paginator(total: 1000, perPage: 10, page: 50), 'on_each_side="2"');

        $pages = array_map(
            fn ($n) => trim($n->textContent),
            iterator_to_array($this->query($html, $this->withClass('page')))
        );

        $this->assertSame(['1', '48', '49', '50', '51', '52', '100'], $pages);
    }

    #[Test]
    public function the_current_page_is_marked_and_not_a_link(): void
    {
        $html = $this->renderPaginator($this->paginator(page: 3));

        $this->assertElementCount($html, $this->withClass('current', 'span'), 1);
        $this->assertAttribute($html, $this->withClass('current', 'span'), 'aria-current', 'page');
    }

    #[Test]
    public function previous_is_disabled_on_the_first_page(): void
    {
        $html = $this->renderPaginator($this->paginator(page: 1));

        $this->assertAttribute($html, $this->withClass('prev-btn'), 'aria-disabled', 'true');
        $this->assertNoElement($html, '//a'.'[contains(@class,"prev-btn")]');
    }

    #[Test]
    public function next_is_disabled_on_the_last_page(): void
    {
        $html = $this->renderPaginator($this->paginator(total: 30, perPage: 10, page: 3));

        $this->assertAttribute($html, $this->withClass('next-btn'), 'aria-disabled', 'true');
    }

    #[Test]
    public function a_per_page_selector_is_rendered_when_options_are_given(): void
    {
        $html = $this->renderPaginator($this->paginator(perPage: 30), ':per_page_options="[15, 30, 50]"');

        $this->assertElementCount($html, $this->withClass('per-page'), 1);
        $this->assertElementCount($html, '//option', 3);
        $this->assertAttribute($html, '//option[@value="30"]', 'selected', 'selected');
    }

    #[Test]
    public function the_per_page_selector_resets_to_the_first_page(): void
    {
        $html = $this->renderPaginator($this->paginator(page: 4), ':per_page_options="[15, 30]"');

        $url = $this->firstNode($html, '//option[@value="30"]')->getAttribute('data-url');

        $this->assertStringContainsString('per_page=30', $url);
        $this->assertStringContainsString('page=1', $url);
    }

    #[Test]
    public function no_per_page_selector_without_options(): void
    {
        $html = $this->renderPaginator($this->paginator());

        $this->assertNoElement($html, $this->withClass('per-page'));
    }

    #[Test]
    public function a_simple_paginator_gets_prev_and_next_without_numbers(): void
    {
        $simple = new Paginator(range(1, 10), 10, 2, ['path' => 'http://localhost/orders']);

        $html = $this->render('<x-bladewind::pagination :paginator="$p" />', ['p' => $simple]);

        $this->assertElementCount($html, $this->withClass('bw-pagination-server'), 1);
        $this->assertNoElement($html, $this->withClass('page'));
        $this->assertNoElement($html, $this->withClass('pagination-summary'));
        $this->assertElementCount($html, $this->withClass('prev-btn'), 1);
    }

    /**
     * The DOM mode is the default and must be untouched by any of this.
     */
    #[Test]
    public function the_client_mode_still_renders_when_no_paginator_is_given(): void
    {
        $html = $this->render('<x-bladewind::pagination total_records="100" table="orders" />');

        $this->assertNoElement($html, $this->withClass('bw-pagination-server'));
        $this->assertElementCount($html, $this->withClass('bw-pagination-orders'), 1);
        $this->assertStringContainsString('goToPage', $html);
    }

    #[Test]
    public function config_supplies_the_server_mode_defaults(): void
    {
        config([
            'bladewind.pagination.per_page_options' => [25, 50],
            'bladewind.pagination.per_page_name' => 'size',
        ]);

        $html = $this->renderPaginator($this->paginator());

        $this->assertElementCount($html, '//option', 2);
        $this->assertStringContainsString('size=25', $this->firstNode($html, '//option[@value="25"]')->getAttribute('data-url'));
    }
}
