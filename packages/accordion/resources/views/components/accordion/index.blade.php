{{-- format-ignore-start --}}
@props([
   'color' => '',
   'grouped' => true,
   'canOpenMultiple' => false,
   'class' => '',
   'nonce' => config('bladewind.script.nonce', null),
   'noPadding' => false,
   'contentCanClose' => true,
])
@php
    $name = defaultBladewindName();
    $grouped = parseBladewindVariable($grouped);
    $can_open_multiple = parseBladewindVariable($canOpenMultiple);
    $noPadding = parseBladewindVariable($noPadding);
@endphp
{{-- format-ignore-end --}}

<div class="w-full bw-accordions {{$name}} {{ $class }}"
     data-name="{{$name}}"
     data-open-multiple="{{$can_open_multiple ? '1':'0'}}">
    @if($grouped)
        <x-bladewind::card no-padding="true">
            <div class="divide-y divide-gray-200/70 dark:divide-dark-600">
                {!! $slot !!}
            </div>
        </x-bladewind::card>
    @else
        <div class="@if($noPadding) space-y-0.5 @else space-y-2 @endif">{!! $slot !!}</div>
    @endif
</div>

@once
    <x-bladewind::script :nonce="$nonce">
        const toggleVisibility = (element) => {
        const accordion = domEl(`.${element}`);
        if (!accordion) return;

        const parentAccordion = accordion.closest('.bw-accordions');
        const canOpenMultiple = parentAccordion?.getAttribute('data-open-multiple') === '1';
        const accordions = domEls('.bw-accordion', `.${parentAccordion?.getAttribute('data-name') || ''}`);

        const toggleState = (targetAccordion, isOpen) => {
        const accordionName = targetAccordion.getAttribute('data-name');
        const content = domEl(`.${accordionName} .accordion-content`);
        const arrow = domEl(`.${accordionName} .accordion-arrow`);
        const title = domEl(`.${accordionName} .accordion-title`);

        targetAccordion.setAttribute('data-open', isOpen ? '1' : '0');
        // the header is the disclosure button, so it owns aria-expanded
        const header = targetAccordion.querySelector('[aria-controls]');
        if (header) header.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        if (content) content.style.maxHeight = isOpen ? `${content.scrollHeight}px` : null;
        changeCss(arrow, 'rotate-180', isOpen ? 'add' : 'remove', true);
        changeCss(title, 'text-gray-700 dark:text-slate-100', isOpen ? 'add' : 'remove', true);
        };

        if (!canOpenMultiple) {
        accordions.forEach((el) => {
        if (el !== accordion) toggleState(el, false);
        });
        }

        const isOpen = accordion.getAttribute('data-open') === '1';
        toggleState(accordion, !isOpen);
        };
    </x-bladewind::script>
@endonce