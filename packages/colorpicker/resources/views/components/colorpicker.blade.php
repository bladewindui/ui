{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName(),
    'selectedValue' => '#000000',
    'class' => '',
    'size' => config('bladewind.colorpicker.size','regular'),
    'showValue' => config('bladewind.colorpicker.show_value',false),
    'colors' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $showValue = parseBladewindVariable($showValue);
@endphp
{{-- format-ignore-end --}}

<div class="inline-flex p-1 bw-color-picker-{{$name}} align-bottom rounded-md border-2 border-slate-300/50 hover:border-slate-300 dark:border-dark-600 dark:hover:border-dark-500/50">
    <input type="hidden" name="{{$name}}" value="{{$selectedValue}}"/>
    @if(empty($colors))
        <div style="background-color: {{$selectedValue}}" @class([
                 $name => !empty($name),
                 $class => !empty($class),
                 'size-[24px]' => $size == 'small',
                 'size-[29px]' => ($size == 'regular' || !in_array($size, ['small','regular','medium','big'])),
                 'size-[36px]' => $size == 'medium',
                 'size-[52px]' => $size == 'big',
                 "rounded-md bw-cp-trigger"
                 ]) data-bw-cp-open="{{$name}}">
            <input type="color" class="invisible bw-cp-{{$name}}" data-bw-cp-input="{{$name}}"/>
        </div>
    @else
        @php $colors = explode(',', $colors); @endphp
        <x-bladewind::dropmenu position="left">
            <x-slot:trigger>
                <div style="background-color: {{$selectedValue}}" @class([
                 $name => !empty($name),
                 $class => !empty($class),
                 'size-[24px]' => $size == 'small',
                 'size-[29px]' => ($size == 'regular' || !in_array($size, ['small','regular','medium','big'])),
                 'size-[36px]' => $size == 'medium',
                 'size-[52px]' => $size == 'big',
                 "rounded-md bw-cp-trigger"
                 ])></div>
            </x-slot:trigger>
            <x-bladewind::dropmenu.item hover="false" padded="false">
                <div class="clear-both grid grid-cols-4 gap-2" style="width: 130px">
                    @foreach($colors as $colour)
                        <div class="size-7 rounded-md border-2 border-transparent hover:border-slate-400 dark:border-transparent dark:hover:border-dark-500/50"
                             style="background-color: {{trim($colour)}};"
                             data-bw-cp-swatch="{{$name}}" data-bw-cp-colour="{{trim($colour)}}"></div>
                    @endforeach
                </div>
            </x-bladewind::dropmenu.item>
        </x-bladewind::dropmenu>
    @endif
    @if($showValue)
        <div class="w-20 bw-cp-label-{{$name}} pl-2 items-center inline-flex tracking-wide text-sm"></div>
    @endif
</div>
@once
    <x-bladewind::script :nonce="$nonce">
        const setColour = (name, colour) => {
        setFieldValue(domEl(`input[name="${name}"]`), colour);
        domEl(`.bw-cp-label-${name}`).textContent = colour;
        domEl(`.bw-cp-trigger.${name}`).style.background = colour;
        }

        /* delegated rather than inline, so a strict CSP does not disable the
           picker. see #608 */
        bwOn('click', '[data-bw-cp-open]', (el) => domEl(`.bw-cp-${el.getAttribute('data-bw-cp-open')}`)?.click());
        bwOn('input', '[data-bw-cp-input]', (el) => setColour(el.getAttribute('data-bw-cp-input'), el.value));
        bwOn('click', '[data-bw-cp-swatch]', (el) => {
        setColour(el.getAttribute('data-bw-cp-swatch'), el.getAttribute('data-bw-cp-colour'));
        });
    </x-bladewind::script>
@endonce