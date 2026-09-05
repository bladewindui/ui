<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CreditCardTest extends TestCase
{
    use RendersComponents;

    private const ROOT = '//div[@data-bw-credit-card]';
    private const FRONT = '//div[@data-front]';
    private const BACK = '//div[@data-back]';
    private const NUMBER = '//input[@data-number]';
    private const NAME = '//input[@data-name]';
    private const CVC = '//input[@data-cvc]';
    private const BRAND_LABEL = '//*[@data-brand-label]';
    private const FLIP_BUTTON = '//button[@data-flip-button]';
    private const ERROR = '//p[@data-error]';

    #[Test]
    public function it_renders_front_and_back_faces_by_default(): void
    {
        $html = $this->render('<x-bladewind::credit-card></x-bladewind::credit-card>');

        $this->assertElementCount($html, self::ROOT, 1);
        $this->assertElementCount($html, self::FRONT, 1);
        $this->assertElementCount($html, self::BACK, 1);
        $this->assertElementCount($html, self::NUMBER, 1);
        $this->assertElementCount($html, self::CVC, 1);
    }

    #[Test]
    public function cardholder_name_prefills_the_name_field(): void
    {
        $html = $this->render('<x-bladewind::credit-card cardholder_name="Emma Reid"></x-bladewind::credit-card>');

        $this->assertAttribute($html, self::NAME, 'value', 'Emma Reid');
    }

    #[Test]
    public function a_visa_number_is_grouped_in_fours_and_detected(): void
    {
        $html = $this->render('<x-bladewind::credit-card number="4242424242424242"></x-bladewind::credit-card>');

        $this->assertAttribute($html, self::NUMBER, 'value', '4242 4242 4242 4242');
        $this->assertStringContainsString('VISA', $html);
    }

    #[Test]
    public function an_amex_number_is_grouped_four_six_five(): void
    {
        $html = $this->render('<x-bladewind::credit-card number="378282246310005"></x-bladewind::credit-card>');

        $this->assertAttribute($html, self::NUMBER, 'value', '3782 822463 10005');
        $this->assertStringContainsString('AMEX', $html);
    }

    #[Test]
    public function an_explicit_brand_overrides_detection(): void
    {
        $html = $this->render('<x-bladewind::credit-card brand="mastercard" number="4242424242424242"></x-bladewind::credit-card>');

        $this->assertStringContainsString('Mastercard', $html);
        $this->assertStringNotContainsString('VISA', $html);
    }

    #[Test]
    public function a_masked_number_is_preserved_verbatim(): void
    {
        $html = $this->render('<x-bladewind::credit-card number="•••• •••• •••• 4242" brand="visa"></x-bladewind::credit-card>');

        $this->assertAttribute($html, self::NUMBER, 'value', '•••• •••• •••• 4242');
    }

    #[Test]
    public function flipped_true_rotates_the_inner_face(): void
    {
        $html = $this->render('<x-bladewind::credit-card flipped="true"></x-bladewind::credit-card>');

        $this->assertStringContainsString('rotateY(180deg)', $html);
    }

    #[Test]
    public function gradient_variant_uses_the_given_colour(): void
    {
        $html = $this->render('<x-bladewind::credit-card color="cyan"></x-bladewind::credit-card>');

        $this->assertHasClasses($html, self::FRONT, ['from-cyan-500']);
    }

    #[Test]
    public function outline_variant_has_no_gradient_classes(): void
    {
        $html = $this->render('<x-bladewind::credit-card variant="outline"></x-bladewind::credit-card>');

        $this->assertMissingClasses($html, self::FRONT, ['from-primary-500']);
        $this->assertHasClasses($html, self::FRONT, ['border-2']);
    }

    #[Test]
    public function inline_variant_has_no_flip_button_or_name_field(): void
    {
        $html = $this->render('<x-bladewind::credit-card variant="inline"></x-bladewind::credit-card>');

        $this->assertNoElement($html, self::FLIP_BUTTON);
        $this->assertNoElement($html, self::NAME);
        $this->assertElementCount($html, self::NUMBER, 1);
    }

    #[Test]
    public function disabled_hides_the_flip_buttons_and_disables_fields(): void
    {
        $html = $this->render('<x-bladewind::credit-card disabled="true"></x-bladewind::credit-card>');

        $this->assertNoElement($html, self::FLIP_BUTTON);
        $this->assertAttribute($html, self::NUMBER, 'disabled', 'disabled');
    }

    #[Test]
    public function show_error_inline_renders_a_hidden_error_message(): void
    {
        $html = $this->render('<x-bladewind::credit-card show_error_inline="true" error_message="Complete the card details"></x-bladewind::credit-card>');

        $this->assertElementCount($html, self::ERROR, 1);
        $this->assertStringContainsString('Complete the card details', $html);
    }

    #[Test]
    public function additional_classes_are_applied(): void
    {
        $html = $this->render('<x-bladewind::credit-card class="my-card"></x-bladewind::credit-card>');

        $this->assertHasClasses($html, self::ROOT, ['my-card']);
    }
}
