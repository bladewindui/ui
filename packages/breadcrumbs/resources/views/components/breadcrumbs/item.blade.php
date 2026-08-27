{{-- format-ignore-start --}}
@props([
    'href' => null,
    'current' => false,
    'icon' => null,
    'iconType' => 'outline',
    'iconDir' => '',
])
@aware([
    'separator' => 'chevron',
    'size' => 'regular',
])
@php
    $current = parseBladewindVariable($current);
    $hasLink = ! is_null($href);
    $iconSizes = [
        'tiny' => 'tiny',
        'small' => 'tiny',
        'regular' => 'small',
        'medium' => 'regular',
        'big' => 'regular',
        'large' => 'regular',
    ];
    $itemClasses = $current
        ? 'bw-breadcrumb-link max-w-full text-gray-800 dark:text-dark-100 font-medium'
        : 'bw-breadcrumb-link max-w-full text-gray-500 dark:text-dark-300';
    if ($hasLink) {
        $itemClasses .= ' rounded-sm transition-colors hover:text-primary-600 dark:hover:text-primary-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500';
    }
@endphp
{{-- format-ignore-end --}}

<li class="bw-breadcrumb-item flex min-w-0 shrink-0 items-center">
    <span class="bw-breadcrumb-separator shrink-0 items-center text-gray-400 dark:text-dark-500"
          aria-hidden="true">
        @if($separator === 'chevron')
            <x-bladewind::icon name="chevron-right" size="tiny" class="stroke-2 rtl:rotate-180" />
        @elseif($separator === 'slash')
            <span>/</span>
        @elseif($separator === 'dot')
            <span>&bull;</span>
        @else
            <span>{{ $separator }}</span>
        @endif
    </span>

    @if($hasLink)
        <a href="{{ $href }}"
           {{ $attributes->exceptPropAliases(get_defined_vars())->class([$itemClasses]) }}
           @if($current) aria-current="page" @endif>
            @if($icon)
                <x-bladewind::icon :name="$icon" :type="$iconType" :dir="$iconDir"
                    :size="$iconSizes[$size] ?? 'small'" class="bw-breadcrumb-icon shrink-0 stroke-2" />
            @endif
            <span class="bw-breadcrumb-label">{{ $slot }}</span>
        </a>
    @else
        <span {{ $attributes->exceptPropAliases(get_defined_vars())->class([$itemClasses]) }}
              @if($current) aria-current="page" @endif>
            @if($icon)
                <x-bladewind::icon :name="$icon" :type="$iconType" :dir="$iconDir"
                    :size="$iconSizes[$size] ?? 'small'" class="bw-breadcrumb-icon shrink-0 stroke-2" />
            @endif
            <span class="bw-breadcrumb-label">{{ $slot }}</span>
        </span>
    @endif
</li>
