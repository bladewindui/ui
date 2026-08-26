{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName('bw-popover-'),
    // icon name suffixed with '-icon', or pass rich markup via <x-slot:trigger>
    'trigger' => config('bladewind.popover.trigger', 'information-circle-icon'),
    'triggerCss' => '',
    // click | mouseover
    'triggerOn' => config('bladewind.popover.trigger_on', 'click'),
    // where the popover appears relative to the trigger: top | bottom | left | right
    'position' => config('bladewind.popover.position', 'bottom'),
    // optional heading shown above the popover content
    'title' => '',
    'width' => config('bladewind.popover.width', 280),
    'class' => '',
    'modular' => false, // append type="module" to script tags
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $triggerOn = (! in_array($triggerOn, ['click', 'mouseover'])) ? 'click' : $triggerOn;
    $position = (! in_array($position, ['top', 'bottom', 'left', 'right'])) ? 'bottom' : $position;
    $width = ! is_numeric($width) ? 280 : $width;
    $modular = parseBladewindVariable($modular);

@endphp
{{-- format-ignore-end --}}

<div class="relative inline-block leading-none bw-popover !z-20 {{ $name }}" tabindex="0">
    <div class="bw-trigger cursor-pointer inline-block {{ $triggerCss }}">
        @if(str_ends_with($trigger, '-icon'))
            <x-bladewind::icon
                    name="{{ trim(str_replace('-icon', '', $trigger)) }}"
                    class="size-6 text-gray-500 transition duration-150 ease-in-out"/>
        @else
            {!! $trigger !!}
        @endif
    </div>

    <div @class([
            'opacity-0 hidden bw-popover-content absolute !z-20 animate__animated animate__fadeIn animate__faster',
            'top-full bottom-auto left-1/2 -translate-x-1/2 mt-2' => $position === 'bottom',
            'bottom-full top-auto left-1/2 -translate-x-1/2 mb-2' => $position === 'top',
            'left-full right-auto top-1/2 -translate-y-1/2 ml-2'  => $position === 'right',
            'right-full left-auto top-1/2 -translate-y-1/2 mr-2'  => $position === 'left',
        ])
         style="width:{{ $width }}px" data-open="0">
        <div @class([
                'rounded-md border border-transparent dark:border-dark-800/20 bg-white dark:bg-dark-700',
                'ring-1 ring-slate-800/5 shadow-md shadow-slate-200/80 dark:shadow-dark-800/70',
                'overflow-hidden',
                "$class"
                ])>
            @if(! empty($title))
                <div class="px-4 py-2.5 border-b border-slate-100 dark:border-dark-600/90 font-medium text-slate-700 dark:text-dark-200 text-sm">
                    {{ $title }}
                </div>
            @endif
            <div class="p-4 text-sm text-slate-600 dark:text-dark-300">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

<x-bladewind::script :nonce="$nonce">
    @php include_once(public_path('vendor/bladewind/js/popover.js')); @endphp
</x-bladewind::script>
<x-bladewind::script :nonce="$nonce" :modular="$modular">
    const {{ $name }} = new BladewindPopover('{{ $name }}', {
    triggerOn: '{{ $triggerOn }}',
    position: '{{ $position }}'
    });
</x-bladewind::script>
