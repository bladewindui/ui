{{-- format-ignore-start --}}
@props([
    // any valid CSS height/max-height value, e.g. "400px". leave blank and
    // the thread grows with its content instead of scrolling internally
    'height' => config('bladewind.chat.height', null),
    'class' => '',
])
{{-- format-ignore-end --}}
<div @class(['bw-chat flex flex-col gap-3 p-4 overflow-y-auto', $class])
     @if($height) style="height: {{ $height }}" @endif>
    {{ $slot }}
</div>
