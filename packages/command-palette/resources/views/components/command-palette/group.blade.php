{{-- format-ignore-start --}}
@props([
    'name',
    'label',
])
@php
    $groupName = (string) $name;
    $safeGroup = preg_replace('/[^A-Za-z0-9_-]/', '-', $groupName);
    $headingId = defaultBladewindName('bw-command-palette-group-').'-'.$safeGroup;
    $groupAttributes = $attributes->exceptPropAliases(get_defined_vars())->except(['name'])->class(['bw-command-palette-group']);
@endphp
{{-- format-ignore-end --}}

<div {{ $groupAttributes }} role="group" aria-labelledby="{{ $headingId }}" data-bw-command-palette-group data-group-name="{{ $groupName }}">
    <div id="{{ $headingId }}" class="bw-command-palette-group-label">{{ $label }}</div>
    <div class="bw-command-palette-group-items">{{ $slot }}</div>
</div>
