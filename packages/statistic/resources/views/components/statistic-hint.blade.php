{{-- format-ignore-start --}}
{{-- rendered inline next to a statistic's label; expects $hint from the parent --}}
@if(($hint ?? '') !== '')
    <span class="hint inline-block align-middle ml-1 cursor-help normal-case tracking-normal"
          title="{{ strip_tags($hint) }}">
        <x-bladewind::icon name="information-circle" class="size-3.5 -mt-0.5 opacity-50 hover:opacity-90"/>
    </span>
@endif
{{-- format-ignore-end --}}
