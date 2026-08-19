{{-- format-ignore-start --}}
@props([
    // name of the input field for use in forms
    'name' => defaultBladewindName('input-'),

    // what type of input box are you displaying
    // available options are text, password, email, search, tel
    'type' => 'text', 

    // label to display on the input box
    'label' => '',

    // should the input accept numbers only. Default is false
    'numeric' => false,

    // minimum number a user can enter when numeric=true
    'min' => null,

    // maximum number a user can enter when numeric=true
    'max' => null,

    // is this a required field. Default is false
    'required' => false,

    // adds margin after the input box
    'addClearing' => config('bladewind.input.add_clearing', true),

    // placeholder text
    'placeholder' => '',

    // value to set when in edit mode or if you want to load the input with default text
    'selectedValue' => '',

    // should the placeholder always be visible even if a label is set
    // by default the label overwrites the placeholder
    // useful if you don't want this overwriting
    'showPlaceholderAlways' => config('bladewind.input.show_placeholder_always', false),

    // message to display when validation fails for this field
    // this is just attached to the input field as a data attribute
    'errorMessage' => '',

    // this is an easy way to pass a translatable heading to the notification component
    // since it is triggered from Javascript, it is hard to translate any text from within js
    'errorHeading' => 'Error',

    // how should error messages be displayed for this input
    // by default error messages are displayed in the Bladewind notification component
    // the component should exist on the page
    'showErrorInline' => config('bladewind.input.show_error_inline', false),

    // for numeric input only: should the numbers include dots
    'withDots' => false,

    // determines if the input field has a label
    'hasLabel' => false,

    'isDatepicker' => false,

    // set the prefix for the input field
    'prefix' => '',
    // set the suffix for the input field
    'suffix' => '',

    // define if prefix background is transparent
    'transparentPrefix' => config('bladewind.input.transparent_prefix', true),

    // define if suffix background is transparent
    'transparentSuffix' => config('bladewind.input.transparent_suffix', true),

    // force (or not) prefix to be an icon
    'prefixIsIcon' => false,

    // force (or not) suffix to be an icon
    'suffixIsIcon' => false,

    // define if icon prefix is outline or solid
    'prefixIconType' => 'outline',

    // force (or not) suffix to be an icon
    'suffixIconType' => 'outline',

    // should password be viewable
    'viewable' => false,
    // should field be clearable
    'clearable' => config('bladewind.input.clearable', false),

    // additional css for prefix
    'prefixIconCss' => '',

    // additional css for suffix
    'suffixIconCss' => '',

    // additional css for div containing the prefix
    'prefixIconDivCss' => '',

    // additional css for div containing the suffix
    'suffixIconDivCss' => 'rtl:!right-[unset] rtl:!left-0',

    // javascript to execute when suffix icon is clicked
    'action' => null,

    'size' => config('bladewind.input.size', 'regular'),
    'enforceLimits' => false,

    // input mask template using wildcards 9 (digit), a (letter), * (alphanumeric).
    // every other character is a literal that is inserted automatically.
    // e.g. mask="(999) 999-9999" or mask="99/99/9999"
    'mask' => '',

    // dynamic mask for formats that change as the user types. use the built-in
    // 'creditCard', or the name of a global JS function that returns a template
    // for the current value. e.g. dynamicMask="creditCard"
    'dynamicMask' => null,

    // format the field as a money input (adds thousands separators, fixes decimals)
    'money' => false,
    // character used to separate the decimal part when money=true
    'moneyDecimalSeparator' => config('bladewind.input.money_decimal_separator', '.'),
    // character used to group thousands when money=true
    'moneyThousandsSeparator' => config('bladewind.input.money_thousands_separator', ','),
    // number of decimal places to allow when money=true. 0 disables decimals
    'moneyPrecision' => config('bladewind.input.money_precision', 2),

    'nonce' => config('bladewind.script.nonce', null),
])

@php
    $name = parseBladewindName($name);
    $add_clearing = parseBladewindVariable($addClearing);
    $show_placeholder_always = parseBladewindVariable($showPlaceholderAlways);
    $show_error_inline = parseBladewindVariable($showErrorInline);
    $with_dots = parseBladewindVariable($withDots);
    $has_label = parseBladewindVariable($hasLabel);
    $is_datepicker = parseBladewindVariable($isDatepicker);
    $transparent_prefix = parseBladewindVariable($transparentPrefix);
    $transparent_suffix = parseBladewindVariable($transparentSuffix);
    $prefix_is_icon = parseBladewindVariable($prefixIsIcon);
    $suffix_is_icon = parseBladewindVariable($suffixIsIcon);
    $required = parseBladewindVariable($required);
    $numeric = parseBladewindVariable($numeric);
    $viewable = parseBladewindVariable($viewable);
    $clearable = parseBladewindVariable($clearable);
    $enforce_limits = parseBladewindVariable($enforceLimits);

    $required_symbol = ($label == '' && $placeholder != '' && $required) ? ' *' : '';
    $is_required = ($required) ? 'required' : '';
    $placeholder_color = ($show_placeholder_always || $label == '') ? '' : 'placeholder-transparent dark:placeholder-transparent';
    $placeholder_label = ($show_placeholder_always) ? $placeholder : (($label !== '') ? $label : $placeholder);
    $with_dots = ($with_dots) ? 1 : 0;
    $type = ($numeric) ? 'number' : $type;

    $money = parseBladewindVariable($money);
    $has_mask = ($mask !== '' || ! empty($dynamicMask) || $money);
    // masking needs a text field — formatted values can contain separators and letters
    if($has_mask) {
        $type = 'text';
        $numeric = false;
    }

    if($type == "password" && $viewable) {
        $suffix = 'eye';
        $suffixIconCss = 'show-pwd';
        $action = "togglePassword('$name', 'show')";
        $suffix_is_icon = true;
    }

    if($clearable) {
        $suffix = 'x-mark';
        $suffix_is_icon = true;
        $suffixIconCss = 'hidden cursor-pointer dark:!bg-dark-900/60 dark:hover:!bg-dark-900 !p-0.5 !rounded-full bg-gray-400 !stroke-2 hover:bg-gray-600 text-white';
    }

    if($attributes->has('readonly') || $attributes->has('disabled')) {
        if($attributes->get('readonly') == 'false') $attributes = $attributes->except('readonly');
        if($attributes->get('disabled') == 'false') $attributes = $attributes->except('disabled');
    }
@endphp
{{-- format-ignore-end --}}

<div class="relative w-full dv-{{$name}} @if($add_clearing) mb-4 @endif">
    <input
            {{ $attributes->exceptPropAliases(get_defined_vars())->class(["bw-input peer $is_required $name $placeholder_color $size focus:outline-primary-500 focus:border-primary-500"])->merge([
                'type' => $type,
                'id' => $name,
                'name' => $name,
                'value' => html_entity_decode($selectedValue),
                'autocomplete' => "new-password",
                'placeholder' => $placeholder_label.$required_symbol,
                'wire:model' => $attributes->get('wire:model'),
            ])->when($errorMessage != '', fn($attrs) => $attrs->merge([
            'data-error-message' => $errorMessage,
            'data-error-inline' => $show_error_inline,
            'data-error-heading' => $errorHeading
            ]))->when($type == 'number', fn($attrs) => $attrs->merge([
            'onbeforeinput' => "allowExtraCharsForNumbers(event,'$name',$with_dots);",
            ]))->when($type == 'number' && ($min||$max), fn($attrs) => $attrs->merge([
            'oninput' => "checkMinMax('$min', '$max', '$name', $enforceLimits);",
            ]))->when($mask !== '', fn($attrs) => $attrs->merge([
            'data-mask' => $mask,
            ]))->when(! empty($dynamicMask), fn($attrs) => $attrs->merge([
            'data-mask-dynamic' => $dynamicMask,
            ]))->when($money, fn($attrs) => $attrs->merge([
            'data-mask-money' => 'true',
            'data-mask-decimal' => $moneyDecimalSeparator,
            'data-mask-thousands' => $moneyThousandsSeparator,
            'data-mask-precision' => $moneyPrecision,
            ]))
            }}
    />
    @if(!empty($errorMessage))
        <div class="text-red-500 text-xs p-1 {{ $name }}-inline-error hidden">{{$errorMessage}}</div>
    @endif
    @if(!empty($label))
        <label for="{{ $name }}" class="form-label {{$size}}" onclick="domEl('.{{$name}}').focus()">{!! $label !!}
            @if($required)
                <x-bladewind::icon name="star" class="!text-red-400 !w-2 !h-2 mt-[-2px]" type="solid"/>
            @endif
        </label>
    @endif
    @if (!empty($prefix))
        <div class="{{$name}}-prefix prefix text-sm select-none pl-3.5 pr-2 z-20 {{$prefixIconDivCss}} text-blue-900/50 dark:text-dark-400 absolute left-0 inset-y-0 inline-flex items-center @if(!$transparent_prefix) bg-slate-100 border-2 border-slate-200 dark:border-dark-700 dark:bg-dark-900/50 dark:border-r-0 border-r-0 rounded-tl-md rounded-bl-md @endif"
             data-transparency="{{$transparent_prefix}}">
            @if($prefix_is_icon)
                <x-bladewind::icon name='{!! $prefix !!}' type="{{ $prefixIconType }}"
                                   class="!size-[18px] !stroke-2 !opacity-70 hover:opacity-100 mr-2 {{$prefixIconCss}}"/>
            @else
                {!! $prefix !!}
            @endif</div>
        <x-bladewind::script :nonce="$nonce">positionPrefix('{{$name}}', 'blur');</x-bladewind::script>
    @endif
    @if (!empty($suffix))
        <div class="{{$name}}-suffix suffix text-sm select-none pl-3.5 !pr-3 {{$suffixIconDivCss}} z-20 text-blue-900/50 dark:text-dark-400 absolute right-0 inset-y-0 inline-flex items-center @if(!$transparent_suffix) bg-slate-100 border-2 border-slate-200 border-l-0 dark:border-dark-700 dark:bg-dark-900/50 dark:border-l-0 rounded-tr-md rounded-br-md @endif"
             data-transparency="{{$transparent_prefix}}">
            @if($suffix_is_icon)
                <x-bladewind::icon
                        name='{!! $suffix !!}'
                        type="{{ $suffixIconType }}"
                        class="!size-4 !stroke-2 !opacity-85 hover:!opacity-100 {{$suffixIconCss}}"
                        action="{!! $action !!}"/>

                @if($type == 'password' && $viewable)
                    <x-bladewind::icon
                            name='eye-slash'
                            type="{{ $suffixIconType }}"
                            class="!size-4 !stroke-2 !opacity-85 hover:!opacity-100 hide-pwd hidden"
                            action="togglePassword('{{$name}}', 'hide')"/>
                @endif
            @else
                {!! $suffix !!}
            @endif
        </div>
        <x-bladewind::script :nonce="$nonce">positionSuffix('{{$name}}');</x-bladewind::script>
    @endif
</div>

<x-bladewind::script :nonce="$nonce">
    @if($clearable)
    domEl('input.{{$name}}').addEventListener('input', () => {
        makeClearable('{{$name}}');
    });
    @endif
    @once
    function allowExtraCharsForNumbers(event, name, withDots) {
        if (event.inputType === "deleteContentBackward" || event.inputType === "deleteContentForward") {
            return;
        }
        let pattern = /[^0-9]/g;
        if (withDots === 1) {
            pattern = /[^0-9.,e+]/g;
        }

        if (event.data && pattern.test(event.data)) {
            event.preventDefault();
        }
    }
    @endonce
</x-bladewind::script>

@if($has_mask)
    @once
        <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/mask.js') }}"></x-bladewind::script>
    @endonce
    <x-bladewind::script :nonce="$nonce">BladewindMask.attach(domEl('input.{{$name}}'));</x-bladewind::script>
@endif