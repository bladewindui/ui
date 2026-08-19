{{-- format-ignore-start --}}
@props([
    // determines which icon to display. Name must match the exact name defined on 
    // https://heroicons.com
    'name' => '',
    // available values are solid and outline. Determines the weight of the icon
    'type' => config('bladewind.icon.type', 'outline'),
    // css classes to append to the svg file
    'class' => '',
    // specify directory to load icons from
    'dir' => config('bladewind.icon.dir', ''),
    // javascript to execute on click
    // this was introduced to allow show/hide password feature in the Input component
    'action' => null,

    // size of the icon. accepts a named size or any tailwind size utility.
    // tiny=size-3 small=size-4 regular=size-5 medium=size-6 big=size-8 large=size-10
    // anything else is emitted verbatim, so size="size-[18px]" works too.
    // a size- h- or w- class in the class attribute still wins, which is how this
    // was expressed before the prop existed.
    'size' => config('bladewind.icon.size', ''),
])
@php
    $path = 'vendor/bladewind/icons';
    $icons_dir = ($dir !== '') ? $dir : ((! in_array($type, [ 'outline', 'solid' ])) ? "$path/outline" : "$path/$type");
    $svg_file = file_exists(public_path("$icons_dir/$name.svg")) ? public_path("$icons_dir/$name.svg") : null;
    $named_sizes = [
        'tiny' => 'size-3',
        'small' => 'size-4',
        'regular' => 'size-5',
        'medium' => 'size-6',
        'big' => 'size-8',
        'large' => 'size-10',
    ];
    // an explicit size class in `class` keeps winning: that was the only way to
    // set a size before this prop existed, and plenty of markup relies on it
    $has_size = preg_match('/\b(h|w|size)-\S+/', $class);
    $size_css = ($size !== '' && ! $has_size) ? ($named_sizes[$size] ?? $size) : '';
    $is_hidden = preg_match('/\bhidden\b/', $class);
    $default_size = ($has_size || $size_css !== '') ? '' : 'size-6 ';
    $svg_class = trim($default_size . ($size_css !== '' ? $size_css.' ' : '') . ($is_hidden ? '' : 'inline-block ') . $class);
@endphp
{{-- format-ignore-end --}}

@if (!empty($name))
    @if(!empty($action))
        <a onclick="{!! $action !!}" class="cursor-pointer"> @endif
            @if(substr($name, 0,4) === '<svg')
                {{-- do this for complete svg tags --}}
                {!!$name!!}
            @elseif($svg_file)
                {!! str_replace('<svg', '<svg class="'.$svg_class.'"', file_get_contents($svg_file)) !!}
            @endif
            @if(!empty($action)) </a>
    @endif
@endif