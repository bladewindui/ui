<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class KanbanTest extends TestCase
{
    use RendersComponents;

    private const BOARD = '//div[contains(concat(" ", normalize-space(@class), " "), " bw-kanban ")]';
    private const COLUMN = '//div[@data-column]';
    private const LIST = '//ul[@data-card-list]';
    private const CARD = '//li[@data-card]';
    private const EMPTY = '//p[@data-empty]';
    private const SPINNER = '//*[contains(@class, "bw-spinner")]';

    private function markup(string $columnAttrs = '', string $cards = ''): string
    {
        $cards = $cards ?: '<x-bladewind::kanban.card value="1">Task one</x-bladewind::kanban.card>';

        return <<<BLADE
            <x-bladewind::kanban>
                <x-bladewind::kanban.column title="To do" id="todo" {$columnAttrs}>
                    {$cards}
                </x-bladewind::kanban.column>
            </x-bladewind::kanban>
        BLADE;
    }

    #[Test]
    public function it_renders_a_board_with_a_column_and_a_card(): void
    {
        $html = $this->render($this->markup());

        $this->assertElementCount($html, self::BOARD, 1);
        $this->assertElementCount($html, self::COLUMN, 1);
        $this->assertElementCount($html, self::LIST, 1);
        $this->assertElementCount($html, self::CARD, 1);
        $this->assertStringContainsString('Task one', $html);
        $this->assertStringContainsString('To do', $html);
    }

    #[Test]
    public function the_column_reports_its_id_for_the_move_hook(): void
    {
        $html = $this->render($this->markup());

        $this->assertAttribute($html, self::COLUMN, 'data-column-id', 'todo');
    }

    #[Test]
    public function a_column_without_an_explicit_id_slugs_its_title(): void
    {
        $html = $this->render(
            '<x-bladewind::kanban><x-bladewind::kanban.column title="In Progress"></x-bladewind::kanban.column></x-bladewind::kanban>'
        );

        $this->assertAttribute($html, self::COLUMN, 'data-column-id', 'in-progress');
    }

    #[Test]
    public function a_card_reports_its_value_as_a_data_id(): void
    {
        $html = $this->render($this->markup());

        $this->assertAttribute($html, self::CARD, 'data-id', '1');
    }

    #[Test]
    public function the_empty_state_element_is_present_but_hidden_with_cards(): void
    {
        $html = $this->render($this->markup());

        $this->assertElementCount($html, self::EMPTY, 1);
        $this->assertAttribute($html, self::EMPTY, 'hidden', '');
    }

    #[Test]
    public function empty_text_is_customisable(): void
    {
        $html = $this->render($this->markup('empty-text="Nothing here yet"'));

        $this->assertStringContainsString('Nothing here yet', $html);
    }

    #[Test]
    public function loading_hides_the_card_list_and_shows_a_spinner(): void
    {
        $html = $this->render($this->markup('loading="true"'));

        $this->assertNoElement($html, self::LIST);
        $this->assertElementCount($html, self::SPINNER, 1);
    }

    #[Test]
    public function it_renders_a_column_actions_slot(): void
    {
        $html = $this->render(
            '<x-bladewind::kanban><x-bladewind::kanban.column title="Done" id="done"><x-slot:actions><button>+</button></x-slot:actions></x-bladewind::kanban.column></x-bladewind::kanban>'
        );

        $this->assertStringContainsString('<button>+</button>', $html);
    }

    #[Test]
    public function additional_classes_are_applied_to_the_board(): void
    {
        $html = $this->render('<x-bladewind::kanban class="my-board"></x-bladewind::kanban>');

        $this->assertHasClasses($html, self::BOARD, ['my-board']);
    }
}
