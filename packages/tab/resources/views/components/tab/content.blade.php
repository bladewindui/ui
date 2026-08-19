{{-- format-ignore-start --}}
@props([
    // available options are true or false as strings.
    // setting this to true will set this tab content as the active one
    'active' => false,
    // unique way to identify this tab content using css or javascript
    // this name is used for switching to this content when the tab header is clicked
    'name' => defaultBladewindName('bw-tab-content-'),
    // additional css to add to the tab content
    // prodbably you'd want to reduce the paddings
    'class' => config('bladewind.tab.content.class', ''),
])
@php 
    $active = parseBladewindVariable($active);
    $active_css = sprintf(((!$active) ? 'hidden %s' : '%s'), $class);
@endphp
{{-- format-ignore-end --}}

<div class="atab-content bw-tc-{{$name}} {{$active_css}}"
     role="tabpanel"
     id="bw-tabpanel-{{$name}}"
     aria-labelledby="bw-tab-{{$name}}"
     tabindex="0">
    {{ $slot }}
</div>