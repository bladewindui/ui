{{-- format-ignore-start --}}
@props([
    'separator' => config('bladewind.breadcrumbs.separator', 'chevron'),
    'size' => config('bladewind.breadcrumbs.size', 'regular'),
    'collapse' => config('bladewind.breadcrumbs.collapse', true),
])
@php
    $collapse = parseBladewindVariable($collapse);
    $sizes = [
        'tiny' => 'text-xs',
        'small' => 'text-xs sm:text-sm',
        'regular' => 'text-sm',
        'medium' => 'text-base',
        'big' => 'text-lg',
        'large' => 'text-lg',
    ];
    $sizeCss = $sizes[$size] ?? $sizes['regular'];
@endphp
{{-- format-ignore-end --}}

<nav {{ $attributes->exceptPropAliases(get_defined_vars())->merge([
        'aria-label' => 'Breadcrumb',
        'class' => 'bw-breadcrumbs min-w-0 '.$sizeCss.($collapse ? ' collapsible' : ''),
    ]) }}>
    <ol class="bw-breadcrumb-list flex min-w-0 items-center" role="list">
        <li class="bw-breadcrumb-overflow-marker shrink-0 items-center text-gray-400 dark:text-dark-400"
            aria-hidden="true">
            <span class="bw-breadcrumb-separator shrink-0 items-center" aria-hidden="true">
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
            <span class="px-1" aria-hidden="true">&hellip;</span>
        </li>
        {{ $slot }}
    </ol>
</nav>

