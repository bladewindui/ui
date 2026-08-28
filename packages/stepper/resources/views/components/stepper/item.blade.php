{{-- format-ignore-start --}}
@props([
    'name',
    'label',
    'description' => null,
    'state' => 'upcoming',
    'clickable' => null,
    'disabled' => false,
    'number' => null,
    'icon' => null,
    'iconType' => null,
    'iconDir' => null,
])
@aware([
    'current' => null,
    'orientation' => 'horizontal',
    'style' => 'circles',
    'linear' => true,
    'showNumbers' => true,
    'completedIcon' => 'check',
    'errorIcon' => 'exclamation-triangle',
    'iconType' => 'outline',
    'iconDir' => '',
])
@php
    $itemName = $name;
    $disabled = parseBladewindVariable($disabled);
    $itemClickable = is_null($clickable) ? null : parseBladewindVariable($clickable);
    $validStates = ['complete', 'current', 'upcoming', 'error', 'disabled'];
    $state = in_array($state, $validStates, true) ? $state : 'upcoming';
    if (! is_null($current)) {
        if ((string) $current === (string) $itemName) $state = 'current';
        elseif ($state === 'current') $state = 'upcoming';
    }
    if ($disabled) $state = 'disabled';
    $hasCustomIndicator = trim((string) $slot) !== '';
@endphp
{{-- format-ignore-end --}}

<li class="bw-stepper-item" data-bw-stepper-item data-step="{{ $itemName }}" data-state="{{ $state }}">
    <button type="button"
        {{ $attributes->exceptPropAliases(get_defined_vars())->except(['name'])->class(['bw-stepper-trigger']) }}
        data-bw-stepper-step="{{ $itemName }}"
        @if(!is_null($itemClickable)) data-clickable="{{ $itemClickable ? 'true' : 'false' }}" @endif
        data-initial-state="{{ $state }}"
        aria-current="{{ $state === 'current' ? 'step' : 'false' }}"
        aria-disabled="{{ $state === 'disabled' ? 'true' : 'false' }}"
        tabindex="{{ $state === 'current' && $state !== 'disabled' ? '0' : '-1' }}"
        @if($state === 'disabled') disabled @endif>
        <span class="bw-stepper-indicator" aria-hidden="true">
            @if($hasCustomIndicator)
                <span class="bw-stepper-custom-indicator">{{ $slot }}</span>
            @else
                <span class="bw-stepper-default-indicator">
                    @if($icon)
                        <x-bladewind::icon :name="$icon" :type="$iconType ?? 'outline'" :dir="$iconDir ?? ''" size="small" />
                    @elseif($showNumbers)
                        <span class="bw-stepper-number">{{ $number }}</span>
                    @endif
                </span>
                @if($style !== 'line')
                    <span class="bw-stepper-complete-indicator"><x-bladewind::icon :name="$completedIcon" :type="$iconType ?? 'outline'" :dir="$iconDir ?? ''" size="small" /></span>
                    <span class="bw-stepper-error-indicator"><x-bladewind::icon :name="$errorIcon" :type="$iconType ?? 'outline'" :dir="$iconDir ?? ''" size="small" /></span>
                @endif
            @endif
        </span>
        <span class="bw-stepper-copy">
            <span class="bw-stepper-label">{{ $label }}</span>
            @if($description)<span class="bw-stepper-description">{{ $description }}</span>@endif
            <span class="sr-only bw-stepper-state-text">{{ ucfirst($state) }}</span>
        </span>
    </button>
    <span class="bw-stepper-connector" aria-hidden="true"></span>
</li>
