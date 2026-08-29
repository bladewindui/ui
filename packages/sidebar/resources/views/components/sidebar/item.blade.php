{{-- format-ignore-start --}}
@props([
    'name',
    'label' => '',
    'href' => null,
    'icon' => null,
    'iconType' => 'outline',
    'iconDir' => '',
    'description' => null,
    'badge' => null,
    'badgeLabel' => null,
    'disabled' => false,
    'external' => false,
    'target' => null,
])
@php
    $itemExplicitActive = parseBladewindVariable($active ?? $attributes->get('active', false));
@endphp
@aware([
    'sidebarActive' => null,
    'multipleActive' => false,
])
@php
    $itemName = (string) $name;
    $explicitActive = $itemExplicitActive;
    $disabled = parseBladewindVariable($disabled);
    $external = parseBladewindVariable($external);
    $activeValue = is_scalar($sidebarActive) ? (string) $sidebarActive : '';
    $hasCanonicalActive = $activeValue !== '';
    $isActive = ! $disabled && ($hasCanonicalActive ? $activeValue === $itemName : $explicitActive);
    $hasCustomContent = trim((string) $slot) !== '';
    $tag = $disabled ? 'span' : ($href ? 'a' : 'button');
    $badgeMeaning = $badgeLabel ?: ($badge !== null ? $badge.' for '.$label : null);
    $itemAttributes = $attributes->exceptPropAliases(get_defined_vars())->except(['name', 'active'])->class(['bw-sidebar-item-action']);
@endphp
{{-- format-ignore-end --}}

<li class="bw-sidebar-item" data-bw-sidebar-item data-item-name="{{ $itemName }}"
    data-initial-active="{{ $explicitActive ? 'true' : 'false' }}" data-active="{{ $isActive ? 'true' : 'false' }}"
    data-disabled="{{ $disabled ? 'true' : 'false' }}">
    <{{ $tag }} {{ $itemAttributes }}
        @if($tag === 'a') href="{{ $href }}" @if($target || $external) target="{{ $target ?: '_blank' }}" @endif @if($external) rel="noopener noreferrer" @endif @endif
        @if($tag === 'button') type="button" @endif
        @if(!$disabled) data-bw-sidebar-focusable data-bw-sidebar-item-action @endif
        @if($isActive) aria-current="page" @endif
        @if($disabled) aria-disabled="true" tabindex="-1" @endif
        aria-label="{{ $label }}" title="{{ $label }}">
        <span class="bw-sidebar-icon" aria-hidden="true">
            @if($icon)<x-bladewind::icon :name="$icon" :type="$iconType" :dir="$iconDir" class="!size-5" />
            @else<span class="bw-sidebar-icon-dot"></span>@endif
        </span>
        <span class="bw-sidebar-item-copy">
            @if($hasCustomContent){{ $slot }}
            @else
                <span class="bw-sidebar-item-label">{{ $label }}</span>
                @if($description)<span class="bw-sidebar-item-description">{{ $description }}</span>@endif
            @endif
        </span>
        @if($badge !== null)
            <span class="bw-sidebar-badge"><span aria-hidden="true">{{ $badge }}</span><span class="sr-only">{{ $badgeMeaning }}</span></span>
        @endif
        @if($external)<x-bladewind::icon name="arrow-top-right-on-square" class="bw-sidebar-external !size-4" /><span class="sr-only">Opens in a new window</span>@endif
    </{{ $tag }}>
</li>
