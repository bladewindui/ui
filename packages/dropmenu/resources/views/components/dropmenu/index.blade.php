{{-- format-ignore-start --}}
@props([
    // accessible name for the trigger. required in practice when the trigger is
    // only an icon, which otherwise reaches a screen reader as nothing at all.
    'triggerLabel' => '',

    'name' => defaultBladewindName('bw-dropmenu-'),
    'trigger' => config('bladewind.dropmenu.trigger', 'ellipsis-horizontal-icon'),
    'triggerCss' => '',
    'triggerOn' => config('bladewind.dropmenu.trigger_on', 'click'),
    'divided' => config('bladewind.dropmenu.divided', false),
    'scrollable' => false,
    'height' => 200,
    'hideAfterClick' => true,
    'position' => 'right',
    'class' => '',
    'modular' => false, // append type="module" to script tags
    'pickerColour' => 'pink',
    'iconRight' => config('bladewind.dropmenu.icon_right', false),
    'padded' => config('bladewind.dropmenu.padded', true),
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $height = !is_numeric($height) ? 200 : $height;
    $triggerOn = (!in_array($triggerOn, ['click', 'mouseover'])) ? 'click' : $triggerOn;
    $divided = parseBladewindVariable($divided);
    $padded = parseBladewindVariable($padded);
    $scrollable = parseBladewindVariable($scrollable);
    $hideAfterClick = parseBladewindVariable($hideAfterClick);
    $iconRight = parseBladewindVariable($iconRight);
    $position = (!in_array($position, ['left', 'right'])) ? 'right' : $position;
@endphp
{{-- format-ignore-end --}}

<div class="relative inline-block leading-none text-left bw-dropmenu {{$name}}"
     data-position="{{ $position }}"
     tabindex="0">
    <div class="bw-trigger cursor-pointer inline-block"
         role="button"
         tabindex="0"
         aria-haspopup="menu"
         aria-expanded="false"
         aria-controls="bw-dropmenu-{{ $name }}"
         @if(!empty($triggerLabel)) aria-label="{{ strip_tags($triggerLabel) }}" @endif>
        @if(str_ends_with($trigger, '-icon'))
            <x-bladewind::icon
                    name="{{ trim(str_replace('-icon','', $trigger)) }}"
                    class="h-6 w-6 text-gray-500 transition duration-150 ease-in-out z-10 {{$triggerCss}}"/>
        @else
            {!!$trigger!!}
        @endif
    </div>
    <div class="opacity-0 hidden bw-dropmenu-items animate__animated animate__fadeIn animate__faster"
         id="bw-dropmenu-{{ $name }}"
         role="menu"
         aria-hidden="true"
         data-open="0">
        <div @class([
                'bw-items-list absolute mt-1 rounded-md bg-white dark:bg-dark-700',
                'border border-transparent dark:border-dark-800/20 ring-1 ring-slate-800/5',
                'shadow-md shadow-slate-200/80 dark:shadow-dark-800/70 whitespace-nowrap',
                '!z-[9999]',
                '-right-1' => ($position === 'right'),
                '-left-1' => ($position === 'left'),
                'p-2' => $padded,
                'p-0' => !$padded,
                'divide-y divide-slate-100 dark:divide-dark-600/90' => $divided,
                "$class"
                ])
             @if($scrollable)style="height: {{$height}}px;overflow-y: scroll"@endif>
            {{ $slot }}
        </div>
    </div>
</div>

@once
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/dropmenu.js') }}"></x-bladewind::script>
@endonce
<x-bladewind::script :nonce="$nonce" :modular="$modular">
    const {{ $name }} = new BladewindDropmenu('{{ $name }}', {
    triggerOn: '{{$triggerOn}}',
    hideAfterClick: '{{$hideAfterClick}}'
    });
</x-bladewind::script>
