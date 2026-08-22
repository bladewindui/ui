{{-- format-ignore-start --}}
@props([
   'color' => '',
   'open' => false,
   'title' => '',
   'class' => '',
])
@aware([
    'color' => $color,
    'grouped' => true,
    'noPadding' => false,
    'contentCanClose' => true,
])
@php
    $name = defaultBladewindName();
    $grouped = parseBladewindVariable($grouped);
    $noPadding = parseBladewindVariable($noPadding);
    $open = parseBladewindVariable($open);
@endphp
{{-- format-ignore-end --}}

<div @class([
        'w-full bw-accordion '.$name . ' '.$class,
        'px-4 py-3 ' => !$noPadding,
        'px-2 py-1.5 ' => $noPadding,
        'bg-'.$color.'-100/70 border-'.$color.'-200' => (!$grouped && !empty($color)),
        "border-gray-200/70 dark:border-dark-600" => (!$grouped && empty($color))
])
     data-open="{{$open ? '1' : '0' }}"
     data-name="{{$name}}"
     onclick="toggleVisibility('{{$name}}')">
    <div class="flex group justify-between items-center cursor-pointer"
         role="button"
         tabindex="0"
         id="bw-accordion-header-{{$name}}"
         aria-controls="bw-accordion-panel-{{$name}}"
         aria-expanded="{{ $open ? 'true' : 'false' }}"
         onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
        <div @class([
        "flex-1 text-lg accordion-title font-semibold leading-normal",
        'dark:text-dark-400 hover:text-gray-700 dark:hover:text-dark-200' => ((!$grouped && empty($color)) || $grouped),
        'dark:text-dark-600 dark:hover:text-dark-700 ' => (!$grouped && !empty($color))
])>{!! $title !!}</div>
        <div class="transition duration-500 accordion-arrow">
            <x-bladewind::icon
                    name="chevron-down"
                    @class([
        '!size-6 rounded-full p-1',
        'bg-'.$color.'-300 text-white group-hover:bg-'.$color.'-500 group-hover:text-white -mr-1' => (!$grouped && !empty($color)),
        'group-hover:text-gray-700 dark:group-hover:text-slate-300 bg-gray-100 dark:bg-dark-600 dark:text-slate-100' => (!$grouped && empty($color))
])/>
        </div>
    </div>
    <div class="accordion-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out"
         id="bw-accordion-panel-{{$name}}"
         role="region"
         aria-labelledby="bw-accordion-header-{{$name}}"
         @if(!$contentCanClose) onclick="event.stopPropagation()"@endif>
        <div @class([
        "pt-2",
        'dark:!text-dark-600' => (!$grouped && !empty($color))
])>{!! $slot !!}</div>
    </div>
</div>