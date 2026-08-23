<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ModalTest extends TestCase
{
    use RendersComponents;

    private function modal(): string
    {
        return $this->withClass('bw-modal');
    }

    #[Test]
    public function it_renders_hidden_with_the_documented_default_classes(): void
    {
        $html = $this->render('<x-bladewind::modal name="confirm">body</x-bladewind::modal>');

        $this->assertHasClasses($html, $this->modal(), [
            'bw-modal',
            'bw-confirm-modal',
            'hidden',
            'fixed',
            'inset-0',
            'z-40',
            'backdrop-blur-md',
        ]);
        $this->assertAttribute($html, $this->modal(), 'data-name', 'confirm');
        $this->assertStringContainsString('body', $html);
    }

    #[Test]
    public function backdrop_can_close_is_exposed_as_a_data_attribute(): void
    {
        $open = $this->render('<x-bladewind::modal name="c">b</x-bladewind::modal>');
        $locked = $this->render('<x-bladewind::modal name="c" backdrop_can_close="false">b</x-bladewind::modal>');

        $this->assertAttribute($open, $this->modal(), 'data-backdrop-can-close', '1');
        $this->assertAttribute($locked, $this->modal(), 'data-backdrop-can-close', '');
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('sizeProvider')]
    public function size_maps_to_a_width_class(string $size, string $expected): void
    {
        $html = $this->render('<x-bladewind::modal name="c" size="'.$size.'">b</x-bladewind::modal>');

        $this->assertHasClasses($html, $this->withClass('bw-c'), [$expected]);
    }

    public static function sizeProvider(): array
    {
        return [
            'tiny' => ['tiny', 'sm:w-72'],
            'small' => ['small', 'sm:w-96'],
            'medium' => ['medium', 'sm:w-[32rem]'],
            'big' => ['big', 'sm:w-[48rem]'],
            'large' => ['large', 'sm:w-[60rem]'],
            'xl' => ['xl', 'sm:w-[86rem]'],
            'omg' => ['omg', 'w-full'],
            'unknown falls back to medium' => ['nonsense', 'sm:w-[32rem]'],
        ];
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('blurProvider')]
    public function blur_size_maps_to_a_backdrop_filter(string $blurSize, string $expected): void
    {
        $html = $this->render('<x-bladewind::modal name="c" blur_size="'.$blurSize.'">b</x-bladewind::modal>');

        $this->assertHasClasses($html, $this->modal(), [$expected]);
    }

    public static function blurProvider(): array
    {
        return [
            'none' => ['none', 'backdrop-blur-none'],
            'small' => ['small', 'backdrop-blur-sm'],
            'large' => ['large', 'backdrop-blur-lg'],
            'xl' => ['xl', 'backdrop-blur-xl'],
            'omg' => ['omg', 'backdrop-blur-3xl'],
            'unknown falls back to medium' => ['nonsense', 'backdrop-blur-md'],
        ];
    }

    #[Test]
    public function blur_backdrop_false_overrides_the_blur_size(): void
    {
        $html = $this->render('<x-bladewind::modal name="c" blur_backdrop="false" blur_size="xl">b</x-bladewind::modal>');

        $this->assertHasClasses($html, $this->modal(), ['backdrop-blur-none']);
    }

    #[Test]
    public function a_title_renders_in_the_modal_title_element(): void
    {
        $html = $this->render('<x-bladewind::modal name="c" title="Are you sure?">b</x-bladewind::modal>');

        $this->assertStringContainsString('Are you sure?', $html);
        $this->assertElementCount($html, $this->withClass('modal-title'), 1);
    }

    #[Test]
    public function the_default_buttons_close_the_modal_by_name(): void
    {
        $html = $this->render('<x-bladewind::modal name="confirm">b</x-bladewind::modal>');

        $this->assertAttribute($html, $this->withClass('okay', 'button'), 'data-bw-modal-close', 'confirm');
    }

    /**
     * #608 routed the default close through a data attribute, so a consumer action
     * now goes through the attribute bag and is HTML-escaped. Asserted on the
     * parsed attribute rather than the raw markup, since &#039; parses back to '.
     */
    #[Test]
    public function a_custom_ok_action_runs_before_the_close(): void
    {
        $html = $this->render('<x-bladewind::modal name="c" ok_button_action="saveUser()">b</x-bladewind::modal>');

        $this->assertAttribute($html, $this->withClass('okay', 'button'), 'onclick', "saveUser();hideModal('c')");
    }

    /**
     * The library's own close is delegated off a data attribute, because a strict
     * CSP blocks inline handlers and a modal you cannot dismiss is the worst
     * version of that.
     */
    #[Test]
    public function the_default_close_uses_a_delegated_handler(): void
    {
        $html = $this->render('<x-bladewind::modal name="c">b</x-bladewind::modal>');

        $this->assertAttribute($html, $this->withClass('okay', 'button'), 'data-bw-modal-close', 'c');
        $this->assertAttribute($html, $this->withClass('okay', 'button'), 'onclick', null);
        $this->assertAttribute($html, $this->withClass('cancel', 'button'), 'data-bw-modal-close', 'c');
    }

    #[Test]
    public function close_after_action_false_leaves_the_modal_open(): void
    {
        $html = $this->render(
            '<x-bladewind::modal name="c" ok_button_action="saveUser()" close_after_action="false">b</x-bladewind::modal>'
        );

        $this->assertAttribute($html, $this->withClass('okay', 'button'), 'onclick', 'saveUser()');
    }

    #[Test]
    public function show_action_buttons_false_removes_the_footer_buttons(): void
    {
        $withButtons = $this->render('<x-bladewind::modal name="c">b</x-bladewind::modal>');
        $without = $this->render('<x-bladewind::modal name="c" show_action_buttons="false">b</x-bladewind::modal>');

        $this->assertGreaterThan(0, substr_count($withButtons, 'bw-button'));
        $this->assertStringNotContainsString('bw-button', $without);
    }

    #[Test]
    public function an_empty_button_label_hides_that_button(): void
    {
        $html = $this->render('<x-bladewind::modal name="c" cancel_button_label="">b</x-bladewind::modal>');

        $this->assertElementCount($html, $this->withClass('hidden', 'button'), 1);
    }

    #[Test]
    public function a_type_renders_the_matching_status_icon(): void
    {
        foreach (['warning' => 'yellow', 'error' => 'red', 'success' => 'green', 'info' => 'blue'] as $type => $colour) {
            $html = $this->render('<x-bladewind::modal name="c" type="'.$type.'">b</x-bladewind::modal>');

            // the wrapper div and the svg both carry modal-icon
            $this->assertElementCount($html, $this->withClass('modal-icon', 'div'), 1);
            $this->assertElementCount($html, $this->withClass('modal-icon', 'svg'), 1);
            $this->assertStringContainsString('bg-'.$colour.'-100', $html);
        }
    }

    #[Test]
    public function no_type_renders_no_icon_column(): void
    {
        $html = $this->render('<x-bladewind::modal name="c">b</x-bladewind::modal>');

        $this->assertNoElement($html, $this->withClass('modal-icon', 'div'));
    }

    #[Test]
    public function show_close_icon_renders_the_dismiss_control(): void
    {
        $html = $this->render('<x-bladewind::modal name="c" show_close_icon="true">b</x-bladewind::modal>');

        $this->assertElementCount($html, $this->withClass('modal-close-icon', 'svg'), 1);
    }

    #[Test]
    public function body_css_is_appended_to_the_modal_body(): void
    {
        $html = $this->render('<x-bladewind::modal name="c" body_css="!p-0">b</x-bladewind::modal>');

        $this->assertHasClasses($html, $this->withClass('modal-body'), ['!p-0']);
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config([
            'bladewind.modal.size' => 'big',
            'bladewind.modal.blur_size' => 'xl',
            'bladewind.modal.show_close_icon' => true,
        ]);

        $html = $this->render('<x-bladewind::modal name="c">b</x-bladewind::modal>');

        $this->assertHasClasses($html, $this->withClass('bw-c'), ['sm:w-[48rem]']);
        $this->assertHasClasses($html, $this->modal(), ['backdrop-blur-xl']);
        $this->assertElementCount($html, $this->withClass('modal-close-icon', 'svg'), 1);
    }
}
