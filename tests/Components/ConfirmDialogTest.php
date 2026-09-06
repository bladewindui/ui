<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ConfirmDialogTest extends TestCase
{
    use RendersComponents;

    private const MODAL = '//div[contains(@class, "bw-mine-modal")]';
    private const CANCEL = '//button[contains(concat(" ", normalize-space(@class), " "), " bw-mine-cancel ")]';
    private const CONFIRM = '//button[contains(concat(" ", normalize-space(@class), " "), " bw-mine-confirm ")]';

    #[Test]
    public function it_renders_a_modal_with_a_danger_icon_by_default(): void
    {
        $html = $this->render('<x-bladewind::confirm-dialog name="mine" title="Delete user?">This cannot be undone.</x-bladewind::confirm-dialog>');

        $this->assertStringContainsString('Delete user?', $html);
        $this->assertStringContainsString('This cannot be undone.', $html);
        $this->assertElementCount($html, self::MODAL.'//svg[contains(@class, "modal-icon")]', 1);
        $this->assertNoElement($html, self::MODAL.'//div[contains(@class, "modal-footer")]');
    }

    #[Test]
    public function backdrop_cannot_close_it_by_default_unlike_a_plain_modal(): void
    {
        $html = $this->render('<x-bladewind::confirm-dialog name="mine" />');

        $this->assertAttribute($html, self::MODAL, 'data-backdrop-can-close', '');
    }

    #[Test]
    public function no_confirm_action_makes_confirm_a_plain_close(): void
    {
        $html = $this->render('<x-bladewind::confirm-dialog name="mine" />');

        $this->assertAttribute($html, self::CONFIRM, 'onclick', "hideModal('mine')");
    }

    #[Test]
    public function a_confirm_action_is_wrapped_in_the_pending_state_runner(): void
    {
        $html = $this->render('<x-bladewind::confirm-dialog name="mine" onConfirm="deleteUser(1)" />');

        $this->assertAttributeContains($html, self::CONFIRM, 'onclick', "runBwConfirmDialogAction('mine', function(){ return (deleteUser(1)); }, true)");
    }

    #[Test]
    public function close_after_confirm_false_is_threaded_through(): void
    {
        $html = $this->render('<x-bladewind::confirm-dialog name="mine" onConfirm="deleteUser(1)" close-after-confirm="false" />');

        $this->assertAttributeContains($html, self::CONFIRM, 'onclick', 'false)');
    }

    #[Test]
    public function cancel_always_closes_via_the_delegated_modal_close(): void
    {
        $html = $this->render('<x-bladewind::confirm-dialog name="mine" onConfirm="deleteUser(1)" />');

        $this->assertAttribute($html, self::CANCEL, 'data-bw-modal-close', 'mine');
    }

    #[Test]
    public function tone_selects_the_icon_type_and_confirm_colour(): void
    {
        $html = $this->render('<x-bladewind::confirm-dialog name="mine" tone="info" />');

        $this->assertHasClasses($html, self::MODAL.'//svg[contains(@class, "modal-icon")]', ['info']);
        $this->assertHasClasses($html, self::CONFIRM, ['bg-blue-500!']);
    }

    #[Test]
    public function an_invalid_tone_falls_back_to_danger(): void
    {
        $html = $this->render('<x-bladewind::confirm-dialog name="mine" tone="nonsense" />');

        $this->assertHasClasses($html, self::MODAL.'//svg[contains(@class, "modal-icon")]', ['error']);
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config([
            'bladewind.confirm_dialog.tone' => 'warning',
            'bladewind.confirm_dialog.backdrop_can_close' => true,
        ]);

        $html = $this->render('<x-bladewind::confirm-dialog name="mine" />');

        $this->assertHasClasses($html, self::MODAL.'//svg[contains(@class, "modal-icon")]', ['warning']);
        $this->assertAttribute($html, self::MODAL, 'data-backdrop-can-close', '1');
    }
}
