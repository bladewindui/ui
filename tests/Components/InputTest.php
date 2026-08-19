<?php

namespace Mkocansey\Bladewind\Tests\Components;

use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class InputTest extends TestCase
{
    use RendersComponents;

    private const INPUT = '//input';

    #[Test]
    public function it_renders_a_text_input_with_the_expected_base_classes(): void
    {
        $html = $this->render('<x-bladewind::input name="email" />');

        $this->assertAttribute($html, self::INPUT, 'type', 'text');
        $this->assertAttribute($html, self::INPUT, 'name', 'email');
        $this->assertAttribute($html, self::INPUT, 'id', 'email');
        $this->assertHasClasses($html, self::INPUT, ['bw-input', 'peer', 'email', 'medium']);
    }

    #[Test]
    public function the_wrapper_carries_a_name_scoped_hook_and_clearing(): void
    {
        $html = $this->render('<x-bladewind::input name="email" />');

        $this->assertHasClasses($html, $this->withClass('dv-email'), ['relative', 'w-full', 'mb-4']);
    }

    #[Test]
    public function add_clearing_false_removes_the_wrapper_margin(): void
    {
        $html = $this->render('<x-bladewind::input name="email" add_clearing="false" />');

        $this->assertMissingClasses($html, $this->withClass('dv-email'), ['mb-4']);
    }

    #[Test]
    public function names_are_normalised(): void
    {
        $html = $this->render('<x-bladewind::input name="user email" />');

        // parseBladewindName() collapses spaces and dashes to underscores
        $this->assertAttribute($html, self::INPUT, 'name', 'user_email');
    }

    #[Test]
    public function required_adds_both_the_class_and_the_placeholder_marker(): void
    {
        $html = $this->render('<x-bladewind::input name="email" placeholder="Email" required="true" />');

        $this->assertHasClasses($html, self::INPUT, ['required']);
        $this->assertAttribute($html, self::INPUT, 'placeholder', 'Email *');
    }

    #[Test]
    public function a_label_takes_over_the_placeholder_and_hides_it(): void
    {
        $html = $this->render('<x-bladewind::input name="email" label="Email" placeholder="you@example.com" />');

        $this->assertAttribute($html, self::INPUT, 'placeholder', 'Email');
        $this->assertHasClasses($html, self::INPUT, ['placeholder-transparent']);
        $this->assertElementCount($html, '//label[@for="email"]', 1);
    }

    #[Test]
    public function show_placeholder_always_keeps_the_placeholder_visible(): void
    {
        $html = $this->render(
            '<x-bladewind::input name="email" label="Email" placeholder="you@example.com" show_placeholder_always="true" />'
        );

        $this->assertAttribute($html, self::INPUT, 'placeholder', 'you@example.com');
        $this->assertMissingClasses($html, self::INPUT, ['placeholder-transparent']);
    }

    #[Test]
    public function numeric_switches_the_type_and_attaches_the_input_guard(): void
    {
        $html = $this->render('<x-bladewind::input name="qty" numeric="true" />');

        $this->assertAttribute($html, self::INPUT, 'type', 'number');
        $this->assertAttributeContains($html, self::INPUT, 'onbeforeinput', 'allowExtraCharsForNumbers');
    }

    #[Test]
    public function min_and_max_attach_the_range_check(): void
    {
        $html = $this->render('<x-bladewind::input name="qty" numeric="true" min="1" max="9" />');

        $this->assertAttributeContains($html, self::INPUT, 'oninput', "checkMinMax('1', '9', 'qty'");
    }

    #[Test]
    public function selected_value_populates_the_value_attribute(): void
    {
        $html = $this->render('<x-bladewind::input name="email" selected_value="a@b.com" />');

        $this->assertAttribute($html, self::INPUT, 'value', 'a@b.com');
    }

    #[Test]
    public function an_error_message_attaches_data_attributes_and_an_inline_holder(): void
    {
        $html = $this->render('<x-bladewind::input name="email" error_message="Required field" />');

        $this->assertAttribute($html, self::INPUT, 'data-error-message', 'Required field');
        $this->assertAttribute($html, self::INPUT, 'data-error-heading', 'Error');
        $this->assertHasClasses($html, $this->withClass('email-inline-error'), ['hidden', 'text-red-500']);
    }

    #[Test]
    public function a_mask_is_passed_through_as_a_data_attribute_and_forces_a_text_field(): void
    {
        $html = $this->render('<x-bladewind::input name="phone" numeric="true" mask="(999) 999-9999" />');

        $this->assertAttribute($html, self::INPUT, 'data-mask', '(999) 999-9999');
        $this->assertAttribute($html, self::INPUT, 'type', 'text');
    }

    #[Test]
    public function money_mode_emits_the_full_set_of_mask_attributes(): void
    {
        $html = $this->render('<x-bladewind::input name="amount" money="true" />');

        $this->assertAttribute($html, self::INPUT, 'data-mask-money', 'true');
        $this->assertAttribute($html, self::INPUT, 'data-mask-decimal', '.');
        $this->assertAttribute($html, self::INPUT, 'data-mask-thousands', ',');
        $this->assertAttribute($html, self::INPUT, 'data-mask-precision', '2');
    }

    #[Test]
    public function a_viewable_password_gets_both_eye_icons_and_a_toggle(): void
    {
        $html = $this->render('<x-bladewind::input name="pwd" type="password" viewable="true" />');

        $this->assertAttribute($html, self::INPUT, 'type', 'password');
        $this->assertElementCount($html, $this->withClass('suffix'), 1);
        $this->assertAttributeContains($html, '//a[1]', 'onclick', "togglePassword('pwd', 'show')");
        $this->assertElementCount($html, $this->withClass('hide-pwd', 'svg'), 1);
    }

    #[Test]
    public function clearable_renders_a_hidden_reset_icon(): void
    {
        $html = $this->render('<x-bladewind::input name="email" clearable="true" />');

        $this->assertElementCount($html, $this->withClass('suffix'), 1);
        $this->assertStringContainsString('makeClearable', $html);
    }

    #[Test]
    public function a_prefix_renders_in_its_own_named_container(): void
    {
        $html = $this->render('<x-bladewind::input name="amount" prefix="GHS" />');

        $this->assertHasClasses($html, $this->withClass('amount-prefix'), ['prefix', 'absolute', 'left-0']);
        $this->assertStringContainsString('GHS', $html);
    }

    #[Test]
    public function a_non_transparent_prefix_gains_a_filled_background(): void
    {
        $html = $this->render('<x-bladewind::input name="amount" prefix="GHS" transparent_prefix="false" />');

        $this->assertHasClasses($html, $this->withClass('amount-prefix'), ['bg-slate-100', 'border-2']);
    }

    #[Test]
    public function prefix_is_icon_renders_an_svg_instead_of_text(): void
    {
        $html = $this->render('<x-bladewind::input name="q" prefix="magnifying-glass" prefix_is_icon="true" />');

        $this->assertElementCount($html, $this->withClass('q-prefix').'/svg', 1);
    }

    #[Test]
    public function a_false_string_readonly_is_stripped_rather_than_honoured(): void
    {
        $html = $this->render('<x-bladewind::input name="email" readonly="false" />');

        $this->assertAttribute($html, self::INPUT, 'readonly', null);
    }

    #[Test]
    public function readonly_is_kept_when_truly_set(): void
    {
        $html = $this->render('<x-bladewind::input name="email" readonly="readonly" />');

        $this->assertAttribute($html, self::INPUT, 'readonly', 'readonly');
    }

    #[Test]
    public function a_generated_name_is_used_when_none_is_given(): void
    {
        $html = $this->render('<x-bladewind::input />');

        $this->assertMatchesRegularExpression('/name="input_[a-z0-9]+"/', $html);
    }

    #[Test]
    public function config_supplies_the_defaults(): void
    {
        config([
            'bladewind.input.size' => 'big',
            'bladewind.input.add_clearing' => false,
        ]);

        $html = $this->render('<x-bladewind::input name="email" />');

        $this->assertHasClasses($html, self::INPUT, ['big']);
        $this->assertMissingClasses($html, $this->withClass('dv-email'), ['mb-4']);
    }
}
