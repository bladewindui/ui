{{-- format-ignore-start --}}
@props([
    'name',
    'label' => '',
    'description' => null,
    'icon' => null,
    'iconType' => 'outline',
    'iconDir' => '',
    'shortcut' => null,
    'keywords' => '',
    'href' => null,
    'disabled' => false,
    'external' => false,
    'target' => null,
])
@php
    $itemName = (string) $name;
    $disabled = parseBladewindVariable($disabled);
    $external = parseBladewindVariable($external);
    $hasCustomContent = trim((string) $slot) !== '';
    $tag = $disabled ? 'div' : ($href ? 'a' : 'button');
    $optionId = defaultBladewindName('bw-command-palette-item-').'-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $itemName);
    $keywordText = mb_strtolower(trim($label.' '.$description.' '.$keywords));
    $shortcutKeys = $shortcut ? array_filter(array_map('trim', explode('+', $shortcut))) : [];
    $itemAttributes = $attributes->exceptPropAliases(get_defined_vars())->except(['name'])->class(['bw-command-palette-item']);
@endphp
{{-- format-ignore-end --}}

<{{ $tag }} {{ $itemAttributes }}
    id="{{ $optionId }}" role="option" data-bw-command-palette-item data-item-name="{{ $itemName }}"
    data-keywords="{{ $keywordText }}" aria-selected="false" tabindex="-1"
    @if($tag === 'a') href="{{ $href }}" @if($target || $external) target="{{ $target ?: '_blank' }}" @endif @if($external) rel="noopener noreferrer" @endif @endif
    @if($tag === 'button') type="button" @endif
    @if($disabled) aria-disabled="true" @endif>
    @if($hasCustomContent)
        {{ $slot }}
    @else
        <span class="bw-command-palette-item-icon" aria-hidden="true">
            @if($icon)<x-bladewind::icon :name="$icon" :type="$iconType" :dir="$iconDir" class="!size-5" />
            @else<span class="bw-command-palette-item-icon-dot"></span>@endif
        </span>
        <span class="bw-command-palette-item-copy">
            <span class="bw-command-palette-item-label">{{ $label }}</span>
            @if($description)<span class="bw-command-palette-item-description">{{ $description }}</span>@endif
        </span>
        @if(count($shortcutKeys))
            <span class="bw-command-palette-item-shortcut" aria-hidden="true">
                @foreach($shortcutKeys as $key)<kbd>{{ $key }}</kbd>@endforeach
            </span>
        @endif
        @if($external)<x-bladewind::icon name="arrow-top-right-on-square" class="bw-command-palette-item-external !size-4" /><span class="sr-only">Opens in a new window</span>@endif
    @endif
</{{ $tag }}>
