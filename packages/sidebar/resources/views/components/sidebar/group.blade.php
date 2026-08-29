{{-- format-ignore-start --}}
@props([
    'name',
    'label',
    'icon' => null,
    'iconType' => 'outline',
    'iconDir' => '',
    'expanded' => false,
    'disabled' => false,
])
@aware([
    'sidebarActive' => null,
    'multipleActive' => false,
])
@php
    $groupName = (string) $name;
    $safeGroup = preg_replace('/[^A-Za-z0-9_-]/', '-', $groupName);
    $disabled = parseBladewindVariable($disabled);
    $expanded = parseBladewindVariable($expanded);
    $slotHtml = (string) $slot;
    $hasActiveDescendant = str_contains($slotHtml, 'aria-current="page"');
    $expanded = ! $disabled && ($expanded || $hasActiveDescendant);
    $uid = defaultBladewindName('bw-sidebar-group-');
    $panelId = $uid.'-'.$safeGroup.'-panel';
    $buttonId = $uid.'-'.$safeGroup.'-button';
@endphp
{{-- format-ignore-end --}}

<li {{ $attributes->exceptPropAliases(get_defined_vars())->except(['name'])->class(['bw-sidebar-group']) }}
    data-bw-sidebar-group data-group-name="{{ $groupName }}" data-initial-expanded="{{ $expanded ? 'true' : 'false' }}"
    data-expanded="{{ $expanded ? 'true' : 'false' }}" data-disabled="{{ $disabled ? 'true' : 'false' }}">
    <button type="button" id="{{ $buttonId }}" class="bw-sidebar-group-trigger" data-bw-sidebar-group-trigger
        data-bw-sidebar-focusable aria-controls="{{ $panelId }}" aria-expanded="{{ $expanded ? 'true' : 'false' }}"
        aria-disabled="{{ $disabled ? 'true' : 'false' }}" @if($disabled) disabled tabindex="-1" @endif
        aria-label="{{ $label }}" title="{{ $label }}">
        <span class="bw-sidebar-icon" aria-hidden="true">
            @if($icon)<x-bladewind::icon :name="$icon" :type="$iconType" :dir="$iconDir" class="!size-5" />
            @else<span class="bw-sidebar-icon-dot"></span>@endif
        </span>
        <span class="bw-sidebar-group-label">{{ $label }}</span>
        <x-bladewind::icon name="chevron-down" class="bw-sidebar-group-chevron !size-4" />
    </button>
    <div id="{{ $panelId }}" class="bw-sidebar-group-panel" role="group" aria-labelledby="{{ $buttonId }}"
        @if(!$expanded) hidden inert @endif>
        <ul class="bw-sidebar-group-list" role="list">{!! $slotHtml !!}</ul>
    </div>
</li>
