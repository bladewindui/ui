{{-- format-ignore-start --}}
@props([
    // the text to display on the tab
    'label' => 'tab',
    // available options are true or false as strings. setting this to true will set this tab 
    // as the active tab and will be highlighted
    'active' => false,
    // defines if the tab is disabled. available options are true or false as strings not booleans
    'disabled' => false,
    // unique way to identify this tab using css or javascript
    // this name is used for switching to a corresponding tab content
    // if url => 'default'
    'name' => defaultBladewindName('bw-tab-heading-'),
    // the default action of a tab is to switch to its corresponding tab content div 
    // to enable switching; the tab content div needs to have the same name as the tab
    // the alternative action is to pass a url. clicking on the tab will open the url
    'url' => 'default',
    // display optional icon prefix
    'icon' => null,
    'iconCss' => '',
    'iconType' => config('bladewind.tab.heading.icon_type', 'outline'),
    'iconDir' => config('bladewind.tab.heading.icon_dir', ''),
])
@aware(['color' => 'primary', 'style' => 'simple'])
@php
    $active = parseBladewindVariable($active);
    $disabled = parseBladewindVariable($disabled);
@endphp
{{-- format-ignore-end --}}

<li class="mr-2 cursor-pointer atab atab-{{ $name }} relative "
    role="tab"
    id="bw-tab-{{ $name }}"
    aria-controls="bw-tabpanel-{{ $name }}"
    aria-selected="{{ $active ? 'true' : 'false' }}"
    @if($disabled) aria-disabled="true" @endif
    {{-- roving tabindex: only the active tab is in the tab order, arrow keys move
         between the rest. tab.js keeps this in step as the selection changes. --}}
    tabindex="{{ $active && ! $disabled ? '0' : '-1' }}"
    @if(! $disabled)
        @if($url == 'default')
            data-bw-tab="{{$name}}" data-bw-tab-colour="{{$color}}"
        @else
            data-bw-tab-url="{{ $url }}"
        @endif
    @endif>
    <span class="@if($disabled)
                    is-disabled
                @else
                    @if(!$active)
                        is-inactive
                    @else
                        is-active {{$color}}
                    @endif
              @endif">
        @if(!empty($icon))
            <x-bladewind::icon :name="$icon" class="{{$iconCss}}" :type="$iconType" :dir="$iconDir"/>
        @endif
        {!! $label !!}</span>
</li>
