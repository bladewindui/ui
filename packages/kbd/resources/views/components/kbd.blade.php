{{-- format-ignore-start --}}
@props([
    // array (or JSON string) of key labels for a combo, e.g. ['Ctrl', 'K'].
    // when given, the default slot is ignored
    'keys' => null,
    // tiny | small | regular
    'size' => config('bladewind.kbd.size', 'small'),
    'class' => '',
])
@php
    $keys = is_string($keys) ? (json_decode($keys, true) ?? []) : $keys;
    $size = in_array($size, ['tiny', 'small', 'regular']) ? $size : 'small';

    $sizes = [
        'tiny' => 'px-1 py-0 text-[10px]',
        'small' => 'px-1.5 py-0.5 text-xs',
        'regular' => 'px-2 py-1 text-sm',
    ];

    $kbdClass = "bw-kbd inline-flex items-center justify-center rounded-md border border-b-2 border-gray-300 dark:border-dark-600 bg-gray-50 dark:bg-dark-800 font-mono font-medium text-gray-600 dark:text-dark-300 shadow-sm {$sizes[$size]}";
@endphp
{{-- format-ignore-end --}}
@if(! empty($keys))
    <span class="bw-kbd-group inline-flex items-center gap-1 {{ $class }}">
        @foreach($keys as $key)
            <kbd class="{{ $kbdClass }}">{{ $key }}</kbd>
            @unless($loop->last)
                <span class="text-gray-400 dark:text-dark-500 text-xs" aria-hidden="true">+</span>
            @endunless
        @endforeach
    </span>
@else
    <kbd class="{{ $kbdClass }} {{ $class }}">{{ $slot }}</kbd>
@endif
