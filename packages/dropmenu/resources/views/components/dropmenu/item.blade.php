{{-- format-ignore-start --}}
@props([
    'class' => '',
    'icon' => '',
    'dir' => config('bladewind.dropmenu.item.dir', ''),
    'iconCss' => '',
    'divider' => false,
    'header' => false,
    'hover' => config('bladewind.dropmenu.item.hover', true),
    'divided' => config('bladewind.dropmenu.item.divided', false),
    'padded' => config('bladewind.dropmenu.item.padded', true),
])
@aware([
    'iconRight' => config('bladewind.dropmenu.item.icon_right', false),
])
@php
    $divider = parseBladewindVariable($divider);
    $divided = parseBladewindVariable($divided);
    $header = parseBladewindVariable($header);
    $hover = parseBladewindVariable($hover);
    $padded = parseBladewindVariable($padded);
    $iconRight = parseBladewindVariable($iconRight);
    $iconCss .= ($iconRight) ? ' ml-2! -mr-1!' : ' mr-2! -ml-0.5 ';
@endphp
{{-- format-ignore-end --}}

<div @class([
        'flex align-middle text-gray-600 cursor-pointer dark:text-dark-300 w-full text-sm text-left! bw-item '.$class,
        'border-0!' => ($divided && $header),
        'border-y border-t-slate-200/75 border-b-white dark:border-t-gray-800/40! dark:border-b-gray-100/10 my-1' => ($divider && !$header && !$divided),
        'hidden' => ($divider && !$header && $divided),
        'py-2 px-2.5' => (!$divider && $padded),
        'p-0' => (!$divider && !$padded),
        'flex-row-reverse justify-between' => ($iconRight && !empty($icon)),
        'hover:rounded-md hover:dark:text-dark-100 hover:bg-slate-200/75 hover:dark:bg-dark-800!' => (!$header && $hover),
        'cursor-default! border-b border-b-slate-200/75 dark:border-b-gray-100/10! mb-1' => $header,
])
     @if($header)
         role="presentation"
     @elseif(! $divider)
         role="menuitem" tabindex="-1"
     @else
         role="separator"
     @endif
     {{$attributes->exceptPropAliases(get_defined_vars())->merge(['data-item' => "true"])}}>
    @if(!empty($icon) && !$header)
        <x-bladewind::icon
                name="{!! $icon !!}"
                :dir="$dir"
                class="size-4! mt-0.5! text-gray-400! dark:text-dark-500!  {{$iconCss}}"/>
    @endif
    {!! $slot !!}
</div>