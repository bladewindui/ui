{{-- format-ignore-start --}}
@props([
    'icon' => '',
    'dir' => config('bladewind.context_menu.item.dir', ''),
    'iconCss' => '',
    // renders a non-interactive separator line instead of an item
    'divider' => false,
    'disabled' => false,
    // normal | danger — danger tints the label/icon red, for destructive actions
    'tone' => 'normal',
    'padded' => config('bladewind.context_menu.item.padded', true),
    // nested context-menu.item children, turning this item into a submenu trigger
    'submenu' => null,
])
@php
    $divider = parseBladewindVariable($divider);
    $disabled = parseBladewindVariable($disabled);
    $padded = parseBladewindVariable($padded);
    $tone = in_array($tone, ['normal', 'danger']) ? $tone : 'normal';
    $hasSubmenu = ! $divider && trim((string) $submenu) !== '';
@endphp
{{-- format-ignore-end --}}
<div @class([
        'flex items-center relative w-full text-sm text-left! bw-context-menu-item text-gray-600 dark:text-dark-300',
        'border-y border-t-slate-200/75 border-b-white dark:border-t-gray-800/40! dark:border-b-gray-100/10 my-1' => $divider,
        'py-2 px-2.5' => (!$divider && $padded),
        'p-0' => (!$divider && !$padded),
        'cursor-pointer hover:rounded-md hover:bg-slate-200/75 hover:dark:bg-dark-800!' => (!$divider && !$disabled),
        'cursor-not-allowed pointer-events-none opacity-40' => $disabled,
        'text-red-600! dark:text-red-400!' => ($tone === 'danger' && !$disabled),
    ])
    @if($divider)
        role="separator"
    @else
        role="menuitem" tabindex="-1"
    @endif
    @if($disabled) aria-disabled="true" @endif
    @if($hasSubmenu) aria-haspopup="menu" aria-expanded="false" @endif
    {{ $attributes->exceptPropAliases(get_defined_vars())->merge([
        'data-item' => $divider ? null : 'true',
        'data-disabled' => $disabled ? '1' : '0',
    ]) }}>
    @if(!$divider)
        @if(!empty($icon))
            <x-bladewind::icon
                    name="{{ $icon }}"
                    :dir="$dir"
                    class="size-4! mt-0.5! shrink-0 text-gray-400! dark:text-dark-500! mr-2! -ml-0.5 {{ $iconCss }}"/>
        @endif
        <span class="grow">{{ $slot }}</span>
        @if($hasSubmenu)
            <x-bladewind::icon name="chevron-right" class="size-3.5! ml-2! -mr-1! shrink-0 text-gray-400!"/>
            <div class="bw-context-menu-submenu hidden fixed z-[9999] min-w-40 rounded-md bg-white dark:bg-dark-700
                        border border-transparent dark:border-dark-800/20 ring-1 ring-slate-800/5
                        shadow-md shadow-slate-200/80 dark:shadow-dark-800/70 whitespace-nowrap p-2"
                 role="menu" aria-hidden="true">
                {{ $submenu }}
            </div>
        @endif
    @endif
</div>
