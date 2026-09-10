{{-- format-ignore-start --}}
@props([
    // horizontal | vertical
    'orientation' => config('bladewind.divider.orientation', 'horizontal'),
    // optional centered label. horizontal only — ignored on a vertical divider
    'label' => '',
    // none | small | medium | large
    'spacing' => config('bladewind.divider.spacing', 'medium'),
    // true: purely visual, hidden from assistive tech. false: role="separator"
    'decorative' => config('bladewind.divider.decorative', true),
    // any accepted BladewindUI colour, or null for the neutral slate default
    'color' => config('bladewind.divider.color', null),
    'class' => '',
    'name' => defaultBladewindName(),
])
@php
    $orientation = in_array($orientation, ['horizontal', 'vertical']) ? $orientation : 'horizontal';
    $decorative = parseBladewindVariable($decorative);
    $spacing = in_array($spacing, ['none', 'small', 'medium', 'large']) ? $spacing : 'medium';
    $color = $color ? defaultBladewindColour($color) : null;
    $name = parseBladewindName($name);
    $hasLabel = $orientation === 'horizontal' && trim((string) $label) !== '';

    $spacingClasses = [
        'horizontal' => ['none' => 'my-0', 'small' => 'my-2', 'medium' => 'my-4', 'large' => 'my-8'],
        'vertical' => ['none' => 'mx-0', 'small' => 'mx-2', 'medium' => 'mx-4', 'large' => 'mx-8'],
    ][$orientation][$spacing];

    $lineColor = $color ? "border-{$color}-200 dark:border-{$color}-800/60" : 'border-gray-200 dark:border-dark-600';
    $labelColor = $color ? "text-{$color}-600 dark:text-{$color}-400" : 'text-gray-500 dark:text-dark-300';

    $a11y = $decorative
        ? ['role' => 'none', 'aria-hidden' => 'true']
        : ['role' => 'separator', 'aria-orientation' => $orientation];
@endphp
{{-- format-ignore-end --}}
@if($orientation === 'vertical')
    <div
        {{ $attributes->exceptPropAliases(get_defined_vars())->merge(array_merge($a11y, [
            'class' => "bw-divider bw-divider-vertical inline-block self-stretch border-l {$lineColor} {$spacingClasses} {$name} {$class}",
        ])) }}
    ></div>
@elseif($hasLabel)
    <div
        {{ $attributes->exceptPropAliases(get_defined_vars())->merge(array_merge($a11y, [
            'class' => "bw-divider bw-divider-horizontal flex items-center {$spacingClasses} {$name} {$class}",
        ])) }}
    >
        <span class="flex-1 border-t {{ $lineColor }}"></span>
        <span class="px-3 text-sm font-medium shrink-0 {{ $labelColor }}">{{ $label }}</span>
        <span class="flex-1 border-t {{ $lineColor }}"></span>
    </div>
@else
    <div
        {{ $attributes->exceptPropAliases(get_defined_vars())->merge(array_merge($a11y, [
            'class' => "bw-divider bw-divider-horizontal border-t {$lineColor} {$spacingClasses} {$name} {$class}",
        ])) }}
    ></div>
@endif
