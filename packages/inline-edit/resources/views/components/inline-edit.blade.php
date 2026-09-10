{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName('bw-inline-edit-'),
    'value' => '',
    'placeholder' => __('bladewind::bladewind.inline_edit_placeholder'),
    'required' => config('bladewind.inline_edit.required', false),
    'requiredMessage' => __('bladewind::bladewind.inline_edit_required'),
    'maxlength' => null,
    // raw JS expression run on save, wrapped in a function receiving
    // (newValue, oldValue); may return a Promise. while it is pending, the
    // field and both controls disable and the save button shows a spinner. a
    // rejection shows its message (or a generic one) and leaves edit mode
    // open so the user can retry. leaving this blank saves optimistically:
    // the display updates immediately with no round trip.
    'onSave' => '',
    'saveLabel' => __('bladewind::bladewind.inline_edit_save'),
    'cancelLabel' => __('bladewind::bladewind.inline_edit_cancel'),
    'editLabel' => __('bladewind::bladewind.inline_edit_edit'),
    'class' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $required = parseBladewindVariable($required);
    $hasOnSave = trim((string) $onSave) !== '';
    $isEmpty = trim((string) $value) === '';
@endphp
{{-- format-ignore-end --}}
<div class="bw-inline-edit {{ $name }} inline-block {{ $class }}"
     data-name="{{ $name }}"
     data-value="{{ $value }}"
     data-placeholder="{{ $placeholder }}"
     data-required="{{ $required ? '1' : '0' }}"
     data-required-message="{{ $requiredMessage }}">
    <div data-display class="group inline-flex items-center gap-1.5">
        <span data-display-text
              role="button" tabindex="0"
              aria-label="{{ $editLabel }}"
              @class([
                  'cursor-pointer rounded-sm outline-offset-2',
                  'text-gray-400 dark:text-dark-500 italic' => $isEmpty,
                  'text-gray-700 dark:text-dark-200' => ! $isEmpty,
              ])>{{ $isEmpty ? $placeholder : $value }}</span>
        <button type="button" data-edit-trigger aria-label="{{ $editLabel }}"
                class="opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 focus:opacity-100 text-gray-400 hover:text-gray-600 dark:hover:text-dark-200">
            <x-bladewind::icon name="pencil-square" class="size-3.5"/>
        </button>
    </div>

    <div data-edit-form class="hidden flex items-center gap-1.5">
        <input type="text" data-input value="{{ $value }}" placeholder="{{ $placeholder }}"
               @if($maxlength) maxlength="{{ $maxlength }}" @endif
               class="bw-input text-sm rounded-md border border-gray-200 dark:border-dark-600 dark:bg-dark-900/50 px-2.5 py-1 focus:outline-primary-500 focus:border-primary-500"/>
        <button type="button" data-save aria-label="{{ $saveLabel }}"
                class="p-1 rounded-md text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20">
            <span data-icon-save><x-bladewind::icon name="check" class="size-4"/></span>
            <span data-spinner class="hidden"><x-bladewind::spinner size="small"/></span>
        </button>
        <button type="button" data-cancel aria-label="{{ $cancelLabel }}"
                class="p-1 rounded-md text-gray-400 hover:bg-gray-50 dark:hover:bg-dark-800">
            <x-bladewind::icon name="x-mark" class="size-4"/>
        </button>
    </div>
    <div data-error hidden class="text-xs text-red-500 mt-1"></div>

    <input type="hidden" name="{{ $name }}" data-hidden-value value="{{ $value }}"/>
</div>

@once
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/inline-edit.js') }}"></x-bladewind::script>
@endonce
<x-bladewind::script :nonce="$nonce">
    (() => {
        const root = document.querySelector('.{{ $name }}');
        // Guard against a duplicate instance (and duplicate listeners) when a
        // framework like Livewire re-renders this markup without a full page
        // reload.
        if (root && root.dataset.bwInitialised === 'true') return;
        if (root) root.dataset.bwInitialised = 'true';

        new BladewindInlineEdit('{{ $name }}', @if($hasOnSave) function (newValue, oldValue) { return ({!! $onSave !!}); } @else null @endif);
    })();
</x-bladewind::script>
