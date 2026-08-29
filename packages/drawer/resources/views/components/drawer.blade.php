{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName('bw-drawer-'),
    'title' => '',
    'description' => '',
    'position' => config('bladewind.drawer.position', 'right'),
    'size' => config('bladewind.drawer.size', 'medium'),
    'modal' => config('bladewind.drawer.modal', true),
    'open' => false,
    'showCloseButton' => config('bladewind.drawer.show_close_button', true),
    'closeLabel' => 'Close drawer',
    'backdropCanClose' => config('bladewind.drawer.backdrop_can_close', true),
    'escapeCanClose' => config('bladewind.drawer.escape_can_close', true),
    'icon' => '',
    'iconType' => 'outline',
    'iconDir' => '',
])
@php
    $name = preg_replace('/\s+/', '-', trim($name));
    $position = in_array($position, ['left', 'right', 'top', 'bottom'], true) ? $position : 'right';
    $size = in_array($size, ACCEPTED_BLADEWIND_SIZES, true) ? $size : 'medium';
    $modal = parseBladewindVariable($modal);
    $open = parseBladewindVariable($open);
    $showCloseButton = parseBladewindVariable($showCloseButton);
    $backdropCanClose = parseBladewindVariable($backdropCanClose);
    $escapeCanClose = parseBladewindVariable($escapeCanClose);
    $titleId = "bw-{$name}-title";
    $descriptionId = "bw-{$name}-description";
    $hasHeader = isset($header) || $title !== '' || $description !== '' || $icon !== '' || $showCloseButton;
    $bodyContent = isset($body) ? $body : $slot;
    $rootAttributes = $attributes->exceptPropAliases(get_defined_vars())->merge(['class' => 'bw-drawer bw-'.$name.'-drawer']);
    if ($title !== '' && ! $rootAttributes->has('aria-label') && ! $rootAttributes->has('aria-labelledby')) $rootAttributes = $rootAttributes->merge(['aria-labelledby' => $titleId]);
    if ($title === '' && ! $rootAttributes->has('aria-label') && ! $rootAttributes->has('aria-labelledby')) $rootAttributes = $rootAttributes->merge(['aria-label' => 'Drawer']);
    if ($description !== '' && ! $rootAttributes->has('aria-describedby')) $rootAttributes = $rootAttributes->merge(['aria-describedby' => $descriptionId]);
@endphp
{{-- format-ignore-end --}}
<div {{ $rootAttributes }} data-bw-drawer data-name="{{ $name }}" data-position="{{ $position }}" data-size="{{ $size }}"
     data-modal="{{ $modal ? 'true' : 'false' }}" data-backdrop-can-close="{{ $backdropCanClose ? 'true' : 'false' }}"
     data-escape-can-close="{{ $escapeCanClose ? 'true' : 'false' }}" data-state="{{ $open ? 'open' : 'closed' }}"
     role="{{ $modal ? 'dialog' : 'region' }}" @if($modal) aria-modal="true" @endif aria-hidden="{{ $open ? 'false' : 'true' }}" @if(!$open) hidden @endif>
    @if($modal)<div class="bw-drawer-backdrop" data-bw-drawer-backdrop></div>@endif
    <div class="bw-drawer-panel" tabindex="-1">
        @if($hasHeader)
            <header class="bw-drawer-header">
                @if(isset($header))
                    <div class="min-w-0 flex-1">{{ $header }}</div>
                @else
                    @if($icon !== '')<x-bladewind::icon :name="$icon" :type="$iconType" :dir="$iconDir" class="mt-0.5 !size-6 shrink-0" />@endif
                    <div class="min-w-0 flex-1">
                        @if($title !== '')<h2 id="{{ $titleId }}" class="text-lg font-semibold text-gray-900 dark:text-dark-100">{{ $title }}</h2>@endif
                        @if($description !== '')<p id="{{ $descriptionId }}" class="mt-1 text-sm text-gray-500 dark:text-dark-300">{{ $description }}</p>@endif
                    </div>
                @endif
                @if($showCloseButton)
                    <button type="button" class="bw-drawer-close" data-bw-drawer-close="{{ $name }}" aria-label="{{ $closeLabel }}">
                        <x-bladewind::icon name="x-mark" :type="$iconType" :dir="$iconDir" class="!size-5" />
                    </button>
                @endif
            </header>
        @endif
        <div class="bw-drawer-body">{{ $bodyContent }}</div>
        @isset($footer)<footer class="bw-drawer-footer">{{ $footer }}</footer>@endisset
    </div>
</div>
