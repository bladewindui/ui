@php
    $name = preg_replace('/[^A-Za-z0-9_-]/', '-', trim((string) $name));
    if ($name === '') $name = defaultBladewindName('bw-sidebar-');
    $placement = in_array($placement, ['left', 'right', 'start', 'end'], true) ? $placement : 'left';
    $mobile = in_array($mobile, ['drawer', 'none'], true) ? $mobile : 'drawer';
    $mobileSize = in_array($mobileSize, ACCEPTED_BLADEWIND_SIZES, true) ? $mobileSize : 'small';
    $height = in_array($height, ['full', 'content'], true) ? $height : 'full';
    $collapsible = parseBladewindVariable($collapsible);
    $collapsed = $collapsible && parseBladewindVariable($collapsed);
    $showCollapseControl = $collapsible && parseBladewindVariable($showCollapseControl);
    $closeOnNavigate = parseBladewindVariable($closeOnNavigate);
    $persist = parseBladewindVariable($persist);
    $persistGroups = parseBladewindVariable($persistGroups);
    $multipleActive = parseBladewindVariable($multipleActive);
    $storageKey = $storageKey ?: 'bladewind:sidebar:'.$name;
    $drawerName = $name.'-mobile';
    $drawerPosition = in_array($placement, ['right', 'end'], true) ? 'right' : 'left';
    $navLabel = $attributes->get('aria-label', $label);
    $rootAttributes = $attributes->exceptPropAliases(get_defined_vars())->except(['aria-label'])->class([
        'bw-sidebar',
        'bw-sidebar-height-'.$height,
    ]);
@endphp

<div class="bw-sidebar-desktop-host" data-bw-sidebar-desktop-host="{{ $name }}">
    <aside {{ $rootAttributes }}
        data-bw-sidebar
        data-name="{{ $name }}"
        data-active="{{ $active }}"
        data-placement="{{ $placement }}"
        data-mobile="{{ $mobile }}"
        data-drawer-name="{{ $drawerName }}"
        data-collapsible="{{ $collapsible ? 'true' : 'false' }}"
        data-state="{{ $collapsed ? 'collapsed' : 'expanded' }}"
        data-initial-state="{{ $collapsed ? 'collapsed' : 'expanded' }}"
        data-close-on-navigate="{{ $closeOnNavigate ? 'true' : 'false' }}"
        data-persist="{{ $persist ? 'true' : 'false' }}"
        data-persist-groups="{{ $persistGroups ? 'true' : 'false' }}"
        data-storage-key="{{ $storageKey }}"
        data-multiple-active="{{ $multipleActive ? 'true' : 'false' }}">
        <header class="bw-sidebar-header">
            <div class="bw-sidebar-header-content">{{ $header ?? '' }}</div>
            <button type="button" class="bw-sidebar-mobile-close" data-bw-sidebar-close aria-label="{{ $closeLabel }}">
                <x-bladewind::icon name="x-mark" class="!size-5" />
            </button>
            @if($showCollapseControl)
                <button type="button" class="bw-sidebar-collapse-control" data-bw-sidebar-collapse-control
                    data-collapse-label="{{ $collapseLabel }}" data-expand-label="{{ $expandLabel }}"
                    aria-label="{{ $collapsed ? $expandLabel : $collapseLabel }}" title="{{ $collapsed ? $expandLabel : $collapseLabel }}">
                    <x-bladewind::icon name="chevron-double-left" class="!size-4" />
                </button>
            @endif
        </header>
        <nav class="bw-sidebar-navigation" aria-label="{{ $navLabel }}">
            <ul class="bw-sidebar-list" role="list">{{ $slot }}</ul>
        </nav>
        @isset($footer)<footer class="bw-sidebar-footer">{{ $footer }}</footer>@endisset
    </aside>
</div>

@if($mobile === 'drawer')
    <x-bladewind::drawer :name="$drawerName" :position="$drawerPosition" :size="$mobileSize"
        :aria-label="$navLabel" show-close-button="false" class="bw-sidebar-drawer">
        <div class="bw-sidebar-mobile-host" data-bw-sidebar-mobile-host="{{ $name }}"></div>
    </x-bladewind::drawer>
@endif
