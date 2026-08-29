@php
    $name = preg_replace('/[^A-Za-z0-9_-]/', '-', trim((string) $name));
    if ($name === '') $name = defaultBladewindName('bw-command-palette-');
    $size = in_array($size, ACCEPTED_BLADEWIND_SIZES, true) ? $size : 'medium';
    $open = parseBladewindVariable($open);
    $loading = parseBladewindVariable($loading);
    $closeOnSelect = parseBladewindVariable($closeOnSelect);
    $backdropCanClose = parseBladewindVariable($backdropCanClose);
    $escapeCanClose = parseBladewindVariable($escapeCanClose);
    $shortcut = strtolower(trim((string) $shortcut));
    $listId = 'bw-'.$name.'-list';
    $inputId = 'bw-'.$name.'-input';
    $emptyId = 'bw-'.$name.'-empty';
    $rootAttributes = $attributes->exceptPropAliases(get_defined_vars())->class([
        'bw-command-palette',
        'bw-command-palette-'.$size,
    ]);
@endphp

<div {{ $rootAttributes }}
    data-bw-command-palette
    data-name="{{ $name }}"
    data-state="{{ $open ? 'open' : 'closed' }}"
    data-shortcut="{{ $shortcut }}"
    data-loading="{{ $loading ? 'true' : 'false' }}"
    data-close-on-select="{{ $closeOnSelect ? 'true' : 'false' }}"
    data-backdrop-can-close="{{ $backdropCanClose ? 'true' : 'false' }}"
    data-escape-can-close="{{ $escapeCanClose ? 'true' : 'false' }}"
    role="dialog" aria-modal="true" aria-label="{{ $label }}" aria-hidden="{{ $open ? 'false' : 'true' }}"
    @if(!$open) hidden @endif>
    <div class="bw-command-palette-backdrop" data-bw-command-palette-backdrop></div>
    <div class="bw-command-palette-panel" tabindex="-1">
        <div class="bw-command-palette-search">
            <x-bladewind::icon name="magnifying-glass" class="bw-command-palette-search-icon !size-5" />
            <input type="text" id="{{ $inputId }}" class="bw-command-palette-input" data-bw-command-palette-input
                role="combobox" aria-expanded="true" aria-haspopup="listbox" aria-controls="{{ $listId }}"
                aria-activedescendant="" aria-autocomplete="list" aria-label="{{ $searchLabel }}"
                placeholder="{{ $placeholder }}" autocomplete="off" autocorrect="off" autocapitalize="off"
                spellcheck="false" />
            <button type="button" class="bw-command-palette-close" data-bw-command-palette-close="{{ $name }}" aria-label="{{ $closeLabel }}">
                <x-bladewind::icon name="x-mark" class="!size-5" />
            </button>
        </div>
        <div id="{{ $listId }}" class="bw-command-palette-results" role="listbox" aria-label="{{ $label }}" data-bw-command-palette-list>
            {{ $slot }}
        </div>
        <p id="{{ $emptyId }}" class="bw-command-palette-empty" data-bw-command-palette-empty hidden>{{ $emptyText }}</p>
        <p class="bw-command-palette-loading" data-bw-command-palette-loading @if(!$loading) hidden @endif>{{ $loadingText }}</p>
        <footer class="bw-command-palette-footer">
            <div class="bw-command-palette-hints">
                <span class="bw-command-palette-hint"><kbd>&uarr;</kbd><kbd>&darr;</kbd> Navigate</span>
                <span class="bw-command-palette-hint"><kbd>&crarr;</kbd> Select</span>
                <span class="bw-command-palette-hint"><kbd>Esc</kbd> Close</span>
            </div>
            @isset($footer)<div class="bw-command-palette-footer-content">{{ $footer }}</div>@endisset
        </footer>
    </div>
</div>
