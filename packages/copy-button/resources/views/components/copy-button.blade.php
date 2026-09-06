{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName('bw-copy-button-'),
    // text to copy. defaults to the trimmed text of the default slot, so a
    // wrapped code snippet or value can be copied without repeating it here
    'value' => null,
    // optional text label rendered on the button itself. ignored when the
    // default slot has content, since the slot already renders as the
    // visible label/content and the button becomes an icon-only trigger
    // beside it
    'label' => null,
    'copyLabel' => __('bladewind::bladewind.copy_button_copy'),
    'copiedMessage' => __('bladewind::bladewind.copy_button_copied'),
    'failedMessage' => __('bladewind::bladewind.copy_button_failed'),
    // ms before the success icon reverts to the default clipboard icon
    'timeout' => config('bladewind.copy_button.timeout', 1500),
    'size' => 'small',
    'class' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $timeout = is_numeric($timeout) ? (int) $timeout : 1500;
    $hasContent = trim((string) $slot) !== '';

    $sizes = [
        'tiny' => 'size-3',
        'small' => 'size-4',
        'regular' => 'size-5',
    ];
    $iconSize = $sizes[$size] ?? $sizes['small'];
@endphp
{{-- format-ignore-end --}}
<span class="bw-copy-button {{ $name }} inline-flex items-center gap-1.5 {{ $class }}"
      data-name="{{ $name }}"
      @if(! is_null($value)) data-value="{{ $value }}" @endif
      data-timeout="{{ $timeout }}"
      data-copied-message="{{ $copiedMessage }}"
      data-failed-message="{{ $failedMessage }}">
    @if($hasContent)
        <span data-content>{{ $slot }}</span>
    @endif
    <button type="button" data-trigger
            aria-label="{{ $copyLabel }}"
            class="inline-flex items-center gap-1.5 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-dark-200 @unless($hasContent) p-1 hover:bg-gray-50 dark:hover:bg-dark-800 @endunless">
        <span data-icon-default class="inline-flex"><x-bladewind::icon name="clipboard" class="{{ $iconSize }}"/></span>
        <span data-icon-success class="hidden inline-flex"><x-bladewind::icon name="check" class="{{ $iconSize }} text-green-600"/></span>
        @if(! $hasContent && ! empty($label))
            <span data-label>{{ $label }}</span>
        @endif
    </button>
    <span data-status class="sr-only" aria-live="polite"></span>
</span>

@once
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/copy-button.js') }}"></x-bladewind::script>
@endonce
<x-bladewind::script :nonce="$nonce">
    (() => {
        const root = document.querySelector('.{{ $name }}');
        // Guard against a duplicate instance (and duplicate click listeners)
        // when a framework like Livewire re-renders this markup without a
        // full page reload.
        if (root && root.dataset.bwInitialised === 'true') return;
        if (root) root.dataset.bwInitialised = 'true';

        new BladewindCopyButton('{{ $name }}');
    })();
</x-bladewind::script>
