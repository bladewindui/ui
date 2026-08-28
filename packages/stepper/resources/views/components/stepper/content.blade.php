{{-- format-ignore-start --}}
@props([
    'name',
    'hasBorder' => true,
])
@aware(['current' => null])
@php
    $visible = ! is_null($current) && (string) $current === (string) $name;
    $hasBorder = parseBladewindVariable($hasBorder);
@endphp
{{-- format-ignore-end --}}

<section {{ $attributes->exceptPropAliases(get_defined_vars())->class([
    'bw-stepper-panel',
    'bw-stepper-panel-borderless' => ! $hasBorder,
]) }}
    data-bw-stepper-panel="{{ $name }}"
    role="region"
    aria-hidden="{{ $visible ? 'false' : 'true' }}"
    tabindex="-1"
    @if(!$visible) hidden inert @endif>
    {{ $slot }}
</section>
