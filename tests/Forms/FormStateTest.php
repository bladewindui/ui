<?php

namespace Mkocansey\Bladewind\Tests\Forms;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Mkocansey\Bladewind\Tests\Concerns\RendersComponents;
use Mkocansey\Bladewind\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * #594 — form components resolving old() and $errors themselves.
 *
 * Both behaviours are opt-in. Turning them on by default would change what
 * existing markup renders in the worst possible way: an app already printing its
 * own validation messages would print every one of them twice.
 */
class FormStateTest extends TestCase
{
    use RendersComponents;

    private function withErrors(array $errors, string $bag = 'default'): void
    {
        $viewErrors = new ViewErrorBag;
        $viewErrors->put($bag, new MessageBag($errors));

        view()->share('errors', $viewErrors);
    }

    /**
     * old() reads flashed input off the request's session, so the session has to be
     * attached to the request the way the session middleware would in a real cycle.
     */
    private function withOldInput(array $old): void
    {
        $session = $this->app['session.store'];
        $session->put('_old_input', $old);

        $this->app['request']->setLaravelSession($session);
    }

    #[Test]
    public function nothing_changes_by_default(): void
    {
        $this->withOldInput(['email' => 'old@example.com']);
        $this->withErrors(['email' => 'The email is invalid.']);

        $html = $this->render('<x-bladewind::input name="email" selected_value="set@example.com" />');

        $this->assertAttribute($html, '//input', 'value', 'set@example.com');
        $this->assertMissingClasses($html, '//input', ['has-error']);
        $this->assertStringNotContainsString('The email is invalid.', $html);
    }

    #[Test]
    public function fill_from_old_repopulates_the_field(): void
    {
        $this->withOldInput(['email' => 'old@example.com']);

        $html = $this->render('<x-bladewind::input name="email" fill_from_old="true" />');

        $this->assertAttribute($html, '//input', 'value', 'old@example.com');
    }

    #[Test]
    public function old_input_takes_precedence_over_an_explicit_value(): void
    {
        $this->withOldInput(['email' => 'old@example.com']);

        $html = $this->render(
            '<x-bladewind::input name="email" selected_value="set@example.com" fill_from_old="true" />'
        );

        $this->assertAttribute($html, '//input', 'value', 'old@example.com');
    }

    #[Test]
    public function the_explicit_value_survives_when_there_is_no_old_input(): void
    {
        $html = $this->render(
            '<x-bladewind::input name="email" selected_value="set@example.com" fill_from_old="true" />'
        );

        $this->assertAttribute($html, '//input', 'value', 'set@example.com');
    }

    #[Test]
    public function show_validation_error_adds_the_error_state_and_the_message(): void
    {
        $this->withErrors(['email' => 'The email is invalid.']);

        $html = $this->render('<x-bladewind::input name="email" show_validation_error="true" />');

        $this->assertHasClasses($html, '//input', ['has-error', 'bw-input']);
        $this->assertStringContainsString('The email is invalid.', $html);
        $this->assertElementCount($html, $this->withClass('email-validation-error'), 1);
    }

    #[Test]
    public function a_field_without_an_error_gets_neither(): void
    {
        $this->withErrors(['other' => 'Something else.']);

        $html = $this->render('<x-bladewind::input name="email" show_validation_error="true" />');

        $this->assertMissingClasses($html, '//input', ['has-error']);
        $this->assertNoElement($html, $this->withClass('email-validation-error'));
    }

    #[Test]
    public function the_validation_message_is_separate_from_the_javascript_error_message(): void
    {
        $this->withErrors(['email' => 'The email is invalid.']);

        $html = $this->render(
            '<x-bladewind::input name="email" error_message="Client side" show_validation_error="true" />'
        );

        $this->assertHasClasses($html, $this->withClass('email-inline-error'), ['hidden']);
        $this->assertMissingClasses($html, $this->withClass('email-validation-error'), ['hidden']);
        $this->assertStringContainsString('Client side', $html);
        $this->assertStringContainsString('The email is invalid.', $html);
    }

    #[Test]
    public function config_turns_both_on_globally(): void
    {
        config([
            'bladewind.forms.fill_from_old' => true,
            'bladewind.forms.show_validation_error' => true,
        ]);
        $this->withOldInput(['email' => 'old@example.com']);
        $this->withErrors(['email' => 'The email is invalid.']);

        $html = $this->render('<x-bladewind::input name="email" />');

        $this->assertAttribute($html, '//input', 'value', 'old@example.com');
        $this->assertHasClasses($html, '//input', ['has-error']);
    }

    #[Test]
    public function a_per_field_prop_overrides_the_config(): void
    {
        config(['bladewind.forms.show_validation_error' => true]);
        $this->withErrors(['email' => 'The email is invalid.']);

        $html = $this->render('<x-bladewind::input name="email" show_validation_error="false" />');

        $this->assertMissingClasses($html, '//input', ['has-error']);
    }

    #[Test]
    public function a_named_error_bag_is_honoured(): void
    {
        $this->withErrors(['email' => 'From the login bag.'], 'login');

        $html = $this->render(
            '<x-bladewind::input name="email" show_validation_error="true" error_bag="login" />'
        );

        $this->assertStringContainsString('From the login bag.', $html);
    }

    #[Test]
    public function array_field_names_resolve_through_dot_notation(): void
    {
        $this->withOldInput(['items' => [['qty' => '7']]]);
        $this->withErrors(['items.0.qty' => 'Too many.']);

        $html = $this->render(
            '<x-bladewind::input name="items[0][qty]" fill_from_old="true" show_validation_error="true" />'
        );

        $this->assertAttribute($html, '//input', 'value', '7');
        $this->assertStringContainsString('Too many.', $html);
    }

    #[Test]
    public function rendering_outside_a_request_does_not_explode(): void
    {
        $html = $this->render(
            '<x-bladewind::input name="email" fill_from_old="true" show_validation_error="true" />'
        );

        $this->assertElementCount($html, '//input', 1);
    }

    #[Test]
    public function textarea_honours_both(): void
    {
        $this->withOldInput(['bio' => 'old bio']);
        $this->withErrors(['bio' => 'Bio is too short.']);

        $html = $this->render(
            '<x-bladewind::textarea name="bio" fill_from_old="true" show_validation_error="true" />'
        );

        $this->assertStringContainsString('old bio', $html);
        $this->assertHasClasses($html, '//textarea', ['has-error']);
        $this->assertStringContainsString('Bio is too short.', $html);
    }

    #[Test]
    public function select_honours_both(): void
    {
        $this->withOldInput(['country' => 'KE']);
        $this->withErrors(['country' => 'Pick a country.']);

        $html = $this->render(
            '<x-bladewind::select name="country" :data="$data" fill_from_old="true" show_validation_error="true" />',
            ['data' => [['label' => 'Ghana', 'value' => 'GH'], ['label' => 'Kenya', 'value' => 'KE']]]
        );

        // the trigger carries the error state, since that is the bordered element
        $this->assertHasClasses($html, $this->withClass('clickable'), ['has-error', 'enabled']);
        $this->assertStringContainsString('Pick a country.', $html);
        $this->assertAttribute($html, '//div[@data-value="KE"]', 'data-selected', 'true');
    }

    #[Test]
    public function a_multiple_select_restores_an_array_of_old_values(): void
    {
        $this->withOldInput(['country' => ['GH', 'KE']]);

        $html = $this->render(
            '<x-bladewind::select name="country" :data="$data" multiple="true" fill_from_old="true" />',
            ['data' => [['label' => 'Ghana', 'value' => 'GH'], ['label' => 'Kenya', 'value' => 'KE']]]
        );

        $this->assertAttribute($html, '//div[@data-value="GH"]', 'data-selected', 'true');
        $this->assertAttribute($html, '//div[@data-value="KE"]', 'data-selected', 'true');
    }

    #[Test]
    public function checkbox_restores_its_checked_state_from_old_input(): void
    {
        $this->withOldInput(['terms' => 'yes']);

        $html = $this->render('<x-bladewind::checkbox name="terms" value="yes" fill_from_old="true" />');

        $this->assertAttribute($html, '//input', 'checked', 'checked');
    }

    #[Test]
    public function a_checkbox_group_restores_only_the_boxes_that_were_ticked(): void
    {
        $this->withOldInput(['perms' => ['read', 'write']]);

        $read = $this->render('<x-bladewind::checkbox name="perms" value="read" fill_from_old="true" />');
        $admin = $this->render('<x-bladewind::checkbox name="perms" value="admin" fill_from_old="true" />');

        $this->assertAttribute($read, '//input', 'checked', 'checked');
        $this->assertAttribute($admin, '//input', 'checked', null);
    }

    /**
     * A form that has never been submitted has no flashed input at all. That must
     * not read as "submitted with nothing ticked" and silently clear a box the
     * consumer set with checked="true".
     */
    #[Test]
    public function no_old_input_leaves_an_explicitly_checked_box_alone(): void
    {
        $html = $this->render('<x-bladewind::checkbox name="terms" value="yes" checked="true" fill_from_old="true" />');

        $this->assertAttribute($html, '//input', 'checked', 'checked');
    }

    #[Test]
    public function an_old_submission_without_the_box_unchecks_it(): void
    {
        $this->withOldInput(['other' => '1']);

        $html = $this->render('<x-bladewind::checkbox name="terms" value="yes" checked="true" fill_from_old="true" />');

        $this->assertAttribute($html, '//input', 'checked', null);
    }

    #[Test]
    public function checkbox_renders_its_validation_error(): void
    {
        $this->withErrors(['terms' => 'You must accept.']);

        $html = $this->render('<x-bladewind::checkbox name="terms" value="yes" show_validation_error="true" />');

        $this->assertStringContainsString('You must accept.', $html);
    }

    #[Test]
    public function radio_passes_both_through_to_the_underlying_checkbox(): void
    {
        $this->withOldInput(['plan' => 'pro']);
        $this->withErrors(['plan' => 'Choose a plan.']);

        $html = $this->render(
            '<x-bladewind::radio name="plan" value="pro" fill_from_old="true" show_validation_error="true" />'
        );

        $this->assertAttribute($html, '//input', 'checked', 'checked');
        $this->assertStringContainsString('Choose a plan.', $html);
    }

    #[Test]
    public function datepicker_passes_both_through_to_the_underlying_input(): void
    {
        $this->withOldInput(['starts_on' => '2026-08-19']);
        $this->withErrors(['starts_on' => 'Pick a date.']);

        $html = $this->render(
            '<x-bladewind::datepicker name="starts_on" fill_from_old="true" show_validation_error="true" />'
        );

        $this->assertAttribute($html, '//input[@name="starts_on"]', 'value', '2026-08-19');
        $this->assertStringContainsString('Pick a date.', $html);
    }

    /**
     * A file input cannot be repopulated, so filepicker takes the error half only.
     */
    #[Test]
    public function filepicker_renders_its_validation_error(): void
    {
        $this->withErrors(['avatar' => 'The avatar must be an image.']);

        $html = $this->render('<x-bladewind::filepicker name="avatar" show_validation_error="true" />');

        $this->assertStringContainsString('The avatar must be an image.', $html);
    }

    #[Test]
    public function every_form_component_is_inert_by_default(): void
    {
        $this->withOldInput(['f' => 'old', 'country' => 'KE', 'terms' => 'yes']);
        $this->withErrors([
            'f' => 'err', 'country' => 'err', 'terms' => 'err', 'avatar' => 'err',
        ]);

        $renders = [
            $this->render('<x-bladewind::input name="f" />'),
            $this->render('<x-bladewind::textarea name="f" />'),
            $this->render('<x-bladewind::checkbox name="terms" value="yes" />'),
            $this->render('<x-bladewind::radio name="terms" value="yes" />'),
            $this->render('<x-bladewind::datepicker name="f" />'),
            $this->render('<x-bladewind::filepicker name="avatar" />'),
            $this->render(
                '<x-bladewind::select name="country" :data="$data" />',
                ['data' => [['label' => 'Kenya', 'value' => 'KE']]]
            ),
        ];

        foreach ($renders as $i => $html) {
            $this->assertStringNotContainsString('err', $html, "component #{$i} rendered an error by default");
            $this->assertStringNotContainsString('validation-error', $html, "component #{$i} rendered an error holder");
            $this->assertStringNotContainsString('has-error', $html, "component #{$i} took the error state");
        }
    }
}
