{{-- format-ignore-start --}}
@props([
    // attached (default) runs the controls flush against each other and removes
    // the corners and doubled borders where they meet. false keeps them apart.
    'attached' => config('bladewind.input_group.attached', true),

    // additional css for the group itself
    'class' => '',
])
@php
    $attached = parseBladewindVariable($attached);
    $classes = trim('bw-input-group '.($attached ? 'attached' : 'gapped').' '.$class);
@endphp
{{-- format-ignore-end --}}

<div {{ $attributes->exceptPropAliases(get_defined_vars())->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
