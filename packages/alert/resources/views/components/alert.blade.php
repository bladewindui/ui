{{-- format-ignore-start --}}
@props([
   // error, warning, success, info
   'type' => 'info',
   // shades of the alert faint, dark
   'shade' => config('bladewind.alert.shade', 'faint'),
   // should the alert type icon be shown
   'showIcon'  => config('bladewind.alert.show_icon', true),
   // should the close icon be shown?
   'showCloseIcon' => true,
   // additional css classes to add
   'class' => '',
   // additional colors to display
   'color' => config('bladewind.alert.color', null),
   // any Heroicons icon to use
   'icon' => '',
   // additional css to apply to $icon
   'iconAvatarCss' => '',
   // use avatar in place of an icon
   'avatar' => '',
   // size of the avatar
   // available sizes are: tiny | small | medium | regular | big | huge | omg
   'size' => config('bladewind.alert.size', 'tiny'),
   // display a ring around the avatar
   'showRing' => config('bladewind.alert.show_ring', false),
])
@php
    // reset variables for Laravel 8 support
    $showIcon = parseBladewindVariable($showIcon);
    $showCloseIcon = parseBladewindVariable($showCloseIcon);

    $close_icon_css =  ($shade == 'dark') ?
        (($color =='transparent') ? 'text-slate-400 hover:text-slate-700 dark:text-slate-200' :
        'text-slate-100 hover:text-slate-500')  : 'text-slate-500 dark:text-slate-200';
    $type = (!empty($color)) ? $color : $type;

    // get colours that match the various types
   $alternate_colour = function() use ($type, $shade) {
      switch ($type){
          case 'warning': return "yellow"; break;
          case 'error': return "red"; break;
          case 'success': return "green"; break;
          case 'info': return "blue"; break;
      }
    };
    $alternate_colour = $alternate_colour();
    // the faint shade is a light chip with dark text. it had no dark counterpart,
    // so on a dark page it stayed pale — the one component that ignored the colour
    // scheme. these dark: utilities only apply where nothing was specified before,
    // so light mode is untouched. see #598
    $presets = (in_array($type, ['error','warning', 'info', 'success'])) ? [
        'faint' => " bg-$alternate_colour-100/70 dark:bg-$alternate_colour-500/15 text-$alternate_colour-600 dark:text-$alternate_colour-300",
        'dark' => "bg-$alternate_colour-500 text-white",
        'icon' => [ 'faint' => "text-$alternate_colour-600 dark:text-$alternate_colour-400", 'dark' => "!text-$alternate_colour-50" ]
    ] : [   // not error, warning, info, success
        'faint' => "bg-$type-100/70 dark:bg-$type-500/15 text-$type-600 dark:text-$type-300",
        'dark' => "bg-$type-500 text-$type-50",
        'icon' => [ 'faint' => "text-$type-600 dark:text-$type-400", 'dark' => "!text-$type-50" ]
    ];
    $colours = [
        'faint' => ($type=='transparent') ?
            "bg-transparent border border-slate-300/80 dark:border-slate-600 text-slate-600 dark:text-dark-400" :
            $presets['faint'],
        'dark' => ($type=='transparent') ?
            "bg-transparent border border-slate-400 dark:border-slate-500 text-slate-600 dark:text-dark-400" :
            $presets['dark'],
        'icon' => [
            'faint' => ($type=='transparent') ? "text-slate-400" : $presets['icon']['faint'],
            'dark' => ($type=='transparent') ? "text-slate-400" : $presets['icon']['dark'],
        ]
    ];
@endphp
{{-- format-ignore-end --}}

<div class="w-full bw-alert animate__animated animate__fadeIn rounded-md flex p-3  {{$colours[$shade] }} {{ $class }}"
     {{-- errors and warnings interrupt; info and success are announced politely --}}
     role="{{ in_array($type, ['error', 'warning']) ? 'alert' : 'status' }}"
     aria-live="{{ in_array($type, ['error', 'warning']) ? 'assertive' : 'polite' }}">
    @if($showIcon)
        <div class="pt-[1px]">
            @if($icon !== '')
                <x-bladewind::icon :name="$icon" class="-mt-1 {{ $iconAvatarCss}}"/>
            @elseif($avatar !== '')
                <x-bladewind::avatar :image="$avatar" :show_ring="$showRing" :size="$size"
                                     class="{{ $iconAvatarCss}}"/>
            @else
                <x-bladewind::modal-icon type="{{$type}}"
                                         class="-mt-1 {{ $colours['icon'][$shade] ??'' }}"/>
            @endif
        </div>
    @endif
    <div class="grow pl-2 pr-5">{{ $slot }}</div>
    @if($showCloseIcon)
        <div class="text-right" onclick="this.parentElement.style.display='none'">
            <x-bladewind::icon
                    name="x-mark"
                    class="size-[18px] -mt-[2px] p-[3px] stroke-2 cursor-pointer {{$close_icon_css}} bg-white/20  hover:bg-white/60 rounded-full hover:text-slate-600"/>
        </div>
    @endif
</div>
