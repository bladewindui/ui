{{-- format-ignore-start --}}
@aware([ 'compact' => false, ])
@props([ 'class' => '', ])
{{-- format-ignore-end --}}
<li {{ $attributes->merge([
        'class' => 'flex first:rounded-t-lg last:rounded-b-lg space-x-5 ' . (!$compact ? 'p-4' : 'py-2 px-4') . " $class",
    ]) }}>{{ $slot }}</li>