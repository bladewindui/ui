{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName('bw-description-list-'),
    // horizontal rule between rows
    'divided' => config('bladewind.description_list.divided', true),
    // alternate row backgrounds. passed down to items via @aware
    'striped' => config('bladewind.description_list.striped', false),
    'class' => '',
])
@php
    $name = parseBladewindName($name);
    $divided = parseBladewindVariable($divided);
    $striped = parseBladewindVariable($striped);
@endphp
{{-- format-ignore-end --}}
<dl @class([
        "bw-description-list $name",
        'divide-y divide-gray-100 dark:divide-dark-700' => $divided,
        "$class",
    ])>
    {{ $slot }}
</dl>
