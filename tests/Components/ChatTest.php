<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ChatTest extends TestCase
{
    use RendersComponents;

    private const THREAD = '//div[contains(concat(" ", normalize-space(@class), " "), " bw-chat ")]';
    private const MESSAGE = '//div[contains(@class, "bw-chat-message")]';
    private const BUBBLE = '//div[contains(@class, "bw-chat-bubble")]';
    private const AVATAR_WRAPPER = '//div[@class="shrink-0 w-8"]';
    private const STATUS_ICON = '//*[contains(@class, "size-3")]';

    private function markup(string $itemAttrs = '', string $slot = 'Hey there'): string
    {
        return <<<BLADE
            <x-bladewind::chat>
                <x-bladewind::chat.message {$itemAttrs}>{$slot}</x-bladewind::chat.message>
            </x-bladewind::chat>
        BLADE;
    }

    #[Test]
    public function it_renders_a_thread_with_a_message(): void
    {
        $html = $this->render($this->markup());

        $this->assertElementCount($html, self::THREAD, 1);
        $this->assertElementCount($html, self::MESSAGE, 1);
        $this->assertElementCount($html, self::BUBBLE, 1);
        $this->assertStringContainsString('Hey there', $html);
    }

    #[Test]
    public function incoming_messages_are_not_reversed_and_show_an_avatar(): void
    {
        $html = $this->render($this->markup());

        $this->assertMissingClasses($html, self::MESSAGE, ['flex-row-reverse']);
        $this->assertElementCount($html, self::AVATAR_WRAPPER, 1);
    }

    #[Test]
    public function outgoing_messages_are_reversed_and_have_no_avatar_slot(): void
    {
        $html = $this->render($this->markup('outgoing="true"'));

        $this->assertHasClasses($html, self::MESSAGE, ['flex-row-reverse']);
        $this->assertNoElement($html, self::AVATAR_WRAPPER);
    }

    #[Test]
    public function outgoing_bubble_uses_the_primary_colour(): void
    {
        $html = $this->render($this->markup('outgoing="true"'));

        $this->assertHasClasses($html, self::BUBBLE, ['bg-primary-600']);
    }

    #[Test]
    public function incoming_bubble_uses_a_neutral_colour(): void
    {
        $html = $this->render($this->markup());

        $this->assertHasClasses($html, self::BUBBLE, ['bg-gray-100']);
    }

    #[Test]
    public function sender_name_is_shown_for_an_ungrouped_incoming_message(): void
    {
        $html = $this->render($this->markup('sender="Jane Doe"'));

        $this->assertStringContainsString('Jane Doe', $html);
    }

    #[Test]
    public function grouped_hides_the_avatar_and_sender_name(): void
    {
        $html = $this->render($this->markup('sender="Jane Doe" grouped="true"'));

        $this->assertStringNotContainsString('Jane Doe', $html);
    }

    #[Test]
    public function it_shows_a_timestamp_when_given(): void
    {
        $html = $this->render($this->markup('time="10:32 AM"'));

        $this->assertStringContainsString('10:32 AM', $html);
    }

    #[Test]
    public function delivery_status_only_shows_on_outgoing_messages(): void
    {
        $incoming = $this->render($this->markup('status="read"'));
        $outgoing = $this->render($this->markup('outgoing="true" status="read"'));

        $this->assertElementCount($outgoing, self::STATUS_ICON, 1);
        $this->assertNoElement($incoming, self::STATUS_ICON);
    }

    #[Test]
    public function failed_status_is_shown_in_red(): void
    {
        $html = $this->render($this->markup('outgoing="true" status="failed"'));

        $this->assertHasClasses($html, self::STATUS_ICON, ['text-red-500']);
    }

    #[Test]
    public function it_renders_an_attachments_slot(): void
    {
        $html = $this->render(
            '<x-bladewind::chat><x-bladewind::chat.message>Check this out<x-slot:attachments><a href="#">photo.jpg</a></x-slot:attachments></x-bladewind::chat.message></x-bladewind::chat>'
        );

        $this->assertStringContainsString('photo.jpg', $html);
    }

    #[Test]
    public function chat_height_sets_an_inline_style(): void
    {
        $html = $this->render('<x-bladewind::chat height="400px"></x-bladewind::chat>');

        $this->assertAttribute($html, self::THREAD, 'style', 'height: 400px');
    }
}
