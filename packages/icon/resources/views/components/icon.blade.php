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
])
@php
    $path = 'vendor/bladewind/icons';
    $icons_dir = ($dir !== '') ? $dir : ((! in_array($type, [ 'outline', 'solid' ])) ? "$path/outline" : "$path/$type");
    $svg_file = file_exists(public_path("$icons_dir/$name.svg")) ? public_path("$icons_dir/$name.svg") : null;
    $has_size = preg_match('/\b(h|w|size)-\S+/', $class);
    $is_hidden = preg_match('/\bhidden\b/', $class);
    $svg_class = trim(($has_size ? '' : 'size-6 ') . ($is_hidden ? '' : 'inline-block ') . $class);
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