{{-- format-ignore-start --}}
@props([
    // name of the input field for use in forms
    'name' => defaultBladewindName('textarea-'),
    'rows' => config('bladewind.textarea.rows', 3),
    'label' => '',
    'required' => false,
    'addClearing' => config('bladewind.textarea.add_clearing', true),
    'placeholder' => '', // placeholder text
    'selectedValue' => '',
     // message to display when validation fails for this field
    // this is just attached to the input field as a data attribute
    'errorMessage' => '',
    // this is an easy way to pass a translatable heading to the notification component
    // since it is triggered from Javascript, it is hard to translate any text from within js
    'errorHeading' => config('bladewind.textarea.error_heading', __("bladewind::bladewind.error_heading")),
    // how should error messages be displayed for this input
    // by default error messages are displayed in the Bladewind notification component
    // the component should exist on the page
    'showErrorInline' => config('bladewind.textarea.show_error_inline', false),

    'toolbar' => config('bladewind.textarea.toolbar', false),
    'except' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $addClearing = parseBladewindVariable($addClearing);
    $showErrorInline = parseBladewindVariable($showErrorInline);
    $required = parseBladewindVariable($required);
    $name = parseBladewindName($name);

    $required_symbol = ($label == '' && $required) ? ' *' : '';
    $is_required = ($required) ? 'required' : '';
    $placeholder_color = ($label !== '') ? 'placeholder-transparent dark:placeholder-transparent' : '';
@endphp
{{-- format-ignore-end --}}

<div class="relative w-full @if($addClearing) mb-3 @endif">
    @if($toolbar)
        <div id="{{$name}}"></div>
        <textarea hidden name="{{ $name }}" id="{{ $name }}-hidden" class="size-0"></textarea>
    @else
        <textarea
                {{ $attributes->exceptPropAliases(get_defined_vars())->merge(['class' => "bw-input peer $is_required $name $placeholder_color focus:border-primary-500"]) }}
                id="{{ $name }}"
                name="{{ $name }}"
                rows="{{ $rows }}"
                @if($errorMessage !== '')
                    data-error-message="{{$errorMessage}}"
                data-error-inline="{{$showErrorInline}}"
                data-error-heading="{{$errorHeading}}"
                @endif
                placeholder="{{ ($label !== '') ? $label : $placeholder }}{{$required_symbol}}">{{$selectedValue}}</textarea>
    @endif
    @if($errorMessage !== '')
        <div class="text-red-500 text-xs pt-2 px-1 {{ $name }}-inline-error hidden">{{$errorMessage}}</div>
    @endif
    @if($label !== '')
        <label for="{{ $name }}" class="form-label dark:peer-focus:pt-1"
               onclick="domEl('.{{$name}}').focus()">{{ $label }}
            @if($required == 'true')
                <x-bladewind::icon name="star" class="!text-red-400 !w-2 !h-2 mt-[-2px]" type="solid"/>
            @endif
        </label>
    @endif
</div>
@if($toolbar)
    @once
        {{--        <span class="hidden ql-toolbar ql-snow ql-stroke ql-thin ql-container ql-editor ql-blank"></span>--}}
        <x-bladewind::script :nonce="$nonce"
                             src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></x-bladewind::script>
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet"/>
        <x-bladewind::script :nonce="$nonce">
            const toolbarOptions = ['bold', 'italic', 'underline',
            {'align': []},
            {'indent': '-1'}, {'indent': '+1'}, 'link',
            {'color': []}, {'background': []},
            {'list': 'ordered'}, {'list': 'bullet'},
            {'list': 'check'}, 'image', 'blockquote', 'code-block', 'clean'
            ];

            const modifyToolbarOptions = (toolbarOptions, except) => {
            except = except.replaceAll(' ', '');
            const exceptArray = except.split(',').map(item => item.trim());
            return toolbarOptions.filter(option => {
            if (typeof option === 'string') {
            return !exceptArray.includes(option);
            } else if (typeof option === 'object') {
            const key = Object.keys(option)[0];
            return !exceptArray.includes(key);
            }
            return true;
            });
            }

            const quillOptions = {
            theme: 'snow',
            placeholder: '{{ ($label !== '') ? $label : $placeholder }}',
            modules: {
            toolbar: '',
            },
            };
        </x-bladewind::script>
    @endonce
    <x-bladewind::script :nonce="$nonce">
        if (typeof Quill === 'undefined') {
        console.log('Unable to load assets from https://quilljs.com');
        } else {
        quillOptions.modules.toolbar = modifyToolbarOptions(toolbarOptions, '{{$except}}');
        var quill_{{$name}} = new Quill('#{{$name}}', quillOptions);
        // Update the hidden input field whenever the textarea content changes
        quill_{{ $name }}.on('text-change', function (delta, oldDelta, source) {
        var value = quill_{{ $name }}.root.innerHTML;
        document.getElementById('{{ $name }}-hidden').value = value;
        });

        // set the initial content for quill
        quill_{{ $name }}.root.innerHTML = @js($selectedValue);
        }
    </x-bladewind::script>
@endif