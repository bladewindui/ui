{{-- format-ignore-start --}}
@props([
    'class' => null,
    'title' => '',
    'value' => '',
    'icon_css' => '',
    'disabled' => false,
])
@aware([
    'disabled' => $disabled,
    'compact' => config('bladewind.checkcards.compact', false),
    'color' => config('bladewind.checkcards.color', 'primary'),
    'radius' => config('bladewind.checkcards.radius', 'medium'),
    'name' => null,
    'borderWidth' => config('bladewind.checkcards.border_width', ''),
    'borderColor' => config('bladewind.checkcards.border_color', 'gray'),
    'alignItems' => config('bladewind.checkcards.align_items', 'top'),
    'selectedValue' => '',
    'icon' => null,
    'avatar' => null,
    'avatarSize' => config('bladewind.checkcards.avatar_size', 'medium'),
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $compact = parseBladewindVariable($compact);
    $disabled = parseBladewindVariable($disabled);
    $colour = defaultBladewindColour($color);
    $border_colour = defaultBladewindColour($borderColor);
    $border_width = !in_array($borderWidth, ['', 2,4,8]) ? '' : '-'.$borderWidth;
    $radius = !in_array($radius, ['none', 'small', 'medium', 'full']) ? 'small' : $radius;
    $radii = [
        'none' => 'rounded-none',
        'small' => 'rounded-md',
        'medium' => 'rounded-lg',
        'full' => 'rounded-full'
    ];
@endphp
{{-- format-ignore-end --}}

<div @class([
        'bg-white dark:bg-dark-800/30 bw-selectable-card cursor-pointer focus:outline-none flex relative',
        'aria-disabled:cursor-not-allowed aria-disabled:opacity-50',
        'items-center' => ($alignItems == 'center'),
        'items-start' => ($alignItems != 'center'),
        "border$border_width border-$border_colour-400/50 hover:border-$border_colour-500/80 dark:border-dark-500/80 dark:hover:border-dark-400/70",
        $class => (!empty($class)),
        $radii[$radius],
        $name,
        'py-3 px-4' => ($compact),
        'p-5' => (!$compact)
]) {{ $attributes->exceptPropAliases(get_defined_vars())->merge([ 'class' => ""]) }} data-bw-checkcard="{{$name}}" data-bw-checkcard-colour="{{$border_colour}}"
     role="checkbox"
     tabindex="0"
     aria-checked="{{ in_array((string) $value, array_map('strval', (array) explode(',', (string) $selectedValue)), true) ? 'true' : 'false' }}"
     @if($title !== '') aria-label="{{ strip_tags($title) }}" @endif
     @if($disabled) aria-disabled="true" @endif
     data-value="{{$value}}">
    <div class="flex">
        <span>
            @if(!empty($icon))
                <x:bladewind::icon
                        name="{{$icon}}"
                        class="rounded-full p-2 bg-{{$colour}}-100/70 text-{{$colour}}-600 mr-3 {{ empty($icon_css) ? '!size-14' : 'size-14 '. $icon_css}}"/>
            @elseif(!empty($avatar))
                <x-bladewind::avatar image="{{$avatar}}" bg_color="{{$colour}}" :size="$avatarSize"
                                     class="mr-3.5 {{($alignItems!='center') ? 'mt-2':''}}"/>
            @endif
        </span>
    </div>
    <div class="grow">
        @if(!empty($title))
            <div class="text-base tracking-wide font-medium text-slate-900 dark:text-dark-200">{{$title}}</div>
        @endif
        <div class="text-slate-500 dark:text-dark-400">
            {{$slot}}
        </div>
    </div>
    <div class="absolute top-1/2 right-0 translate-x-1/2 -translate-y-1/2 pointer-events-none checkmark hidden">
        <x-bladewind::icon name="check-circle" type="solid"
                           class="text-{{$border_colour}}-600 stroke-white !size-7"/>
    </div>
</div>

@once
    <x-bladewind::script :nonce="$nonce">
        var selectCheckcard = (name, value, borderColour) => {
        let input = domEl(`input.${name}`);
        let arrInputValue = (input.value !== '') ? input.value.split(',') : [];
        let elString = `div.${name}[data-value="${value}"]`;
        let el = domEl(elString);
        let checkmark = domEl(`${elString} .checkmark`);
        const border_default = `border-${borderColour}-400/50,hover:border-${borderColour}-500/80`;
        const border_active = `border-${borderColour}-500`;
        const maxSelection = parseInt(input.getAttribute('data-max-selection'));
        const errorHeading = input.getAttribute('data-error-heading') ?? '';
        const errorMessage = input.getAttribute('data-error-message');
        const showError = parseInt(input.getAttribute('data-show-error')) === 1;
        const autoSelect = parseInt(input.getAttribute('data-auto-select')) === 1;

        if (arrInputValue.length > 0 && arrInputValue.includes(value)) {
        arrInputValue = arrInputValue.filter(item => item !== value);
        input.value = arrInputValue.join(',');
        hide(checkmark, true);
        el.setAttribute('aria-checked', 'false');
        changeCss(el, border_default, 'add', true);
        changeCss(el, border_active, 'remove', true);
        } else {
        if (arrInputValue.length >= maxSelection) {
        if (autoSelect) { //removed last item selected
        selectCheckcard(name, arrInputValue[arrInputValue.length - 1], borderColour);
        arrInputValue.pop();
        } else {
        if (showError) showNotification(errorHeading, errorMessage, 'error');
        return false;
        }
        }
        arrInputValue.push(value);
        input.value = arrInputValue.join(',');
        unhide(checkmark, true);
        el.setAttribute('aria-checked', 'true');
        changeCss(el, border_default, 'remove', true);
        changeCss(el, border_active, 'add', true);
        }
        }

        /* disable every card in a group, or only the card with the given value.
           selectCheckcard() still works on a disabled card, so a selection can
           be made in code and then locked in */
        var disableCheckcards = (name, value = null, disabled = true) => {
        domEls(`[data-bw-checkcard="${name}"]`).forEach((card) => {
        if (value === null || card.getAttribute('data-value') === String(value)) {
        card.setAttribute('aria-disabled', disabled);
        }
        });
        }
        var enableCheckcards = (name, value = null) => disableCheckcards(name, value, false);

        /* bound by delegation rather than an inline onclick, so a strict CSP does
           not disable selection, and Enter/Space work on a card that is not a
           native control. see #608 */
        bwOn('click', '[data-bw-checkcard]', (card) => {
        if (card.getAttribute('aria-disabled') === 'true') return;
        const name = card.getAttribute('data-bw-checkcard');
        const value = card.getAttribute('data-value');
        selectCheckcard(name, value, card.getAttribute('data-bw-checkcard-colour'));

        /* the group's onclick names a function rather than holding inline code,
           so it can be called without eval under a strict CSP */
        const onclick = domEl(`input.${name}`).getAttribute('data-onclick');
        if (onclick) window[onclick](value, card.getAttribute('aria-checked') === 'true', card);
        });
        bwActivateOnKey('[data-bw-checkcard]');
    </x-bladewind::script>
@endonce

@if($selectedValue !== '')
    @if(in_array($value, explode(',', $selectedValue)))
        <x-bladewind::script :nonce="$nonce">
            selectCheckcard('{{$name}}', '{{$value}}', '{{$border_colour}}');
        </x-bladewind::script>
    @endif
@endif