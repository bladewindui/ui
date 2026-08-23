{{-- format-ignore-start --}}
@props([
    // determines types of icon to display. Available options: info, success, error, warning
    // only the blank type (type='') has no icon. useful if you want your modal to contain a form 
    // or other custom content
    'type' => '',

    // title text to display. example: Confirm your delete action
    'title' => '',

    // name of the modal. used to uniquely identify the modal in css and js
    'name' => defaultBladewindName('bw-modal-'),

    // text to display on the primary button. default is Okay
    'okButtonLabel' => config('bladewind.modal.ok_button_label', __('bladewind::bladewind.okay')),

    // text to display on secondary button. default is Cancel
    'cancelButtonLabel' => config('bladewind.modal.cancel_button_label', __('bladewind::bladewind.cancel')),

    // action to perform when secondary button is clicked. default is close. 
    // provide a custom js function as string to execute that function. example "saveUser"
    'okButtonAction' => 'close',

    // action to perform when primary button is clicked. default is close. 
    // provide a custom js function as a string to execute that function. example "confirmAction"
    'cancelButtonAction' => 'close',

    // close modal when either primary or close secondary buttons are clicked
    // the modal will be closed after your custom js function has been executed
    'closeAfterAction' => config('bladewind.modal.close_after_action', true),

    // determines if clicking on the backdrop can close the modal. default is true
    // when set to false, only the action buttons can close the modal.
    // in this case ensure you have set "close" as an action for one of your action buttons
    'backdropCanClose' => config('bladewind.modal.backdrop_can_close', true),

    // should the action buttons be displayed? default is true. false will hide the buttons
    'showActionButtons' => true,

    // should the action buttons be centered? default is false. right aligned
    'centerActionButtons' => config('bladewind.modal.center_action_buttons', false),

    // should the action buttons stretch the entire width of the modal
    'stretchActionButtons' => config('bladewind.modal.stretch_action_buttons', false),

    // should the backdrop of the modal be blurred
    'blurBackdrop' => config('bladewind.modal.blur_backdrop', true),

    // specify intensity of the backdrop blur
    'blurSize' => config('bladewind.modal.blur_size', 'medium'),

    // determines the size of the modal. available options are small, medium, large and xl
    // on mobile it is small by default but fills up the width of the screen
    'size' => config('bladewind.modal.size', 'medium'),

    // add extra css to the modal body
    'bodyCss' => '',
    // add extra css to the modal footer
    'footerCss' => '',
    // show close icon. By default, the close or cancel button closes the modal
    'showCloseIcon' => config('bladewind.modal.show_close_icon', false),

    // display any Heroicon icon in the modal
    'icon' => '',
    'iconCss' => '',

    // change positions of the action buttons .. left, center, right
    'alignButtons' => config('bladewind.modal.align_buttons', 'right'),

    'nonce' => config('bladewind.script.nonce', null),
    'radius' => config('bladewind.modal.radius', 'small'),
])
@php
    $closeAfterAction = parseBladewindVariable($closeAfterAction);
    $backdropCanClose = parseBladewindVariable($backdropCanClose);
    $showActionButtons = parseBladewindVariable($showActionButtons);
    $centerActionButtons = parseBladewindVariable($centerActionButtons);
    $stretchActionButtons = parseBladewindVariable($stretchActionButtons);
    $blurBackdrop = parseBladewindVariable($blurBackdrop);
    $showCloseIcon = parseBladewindVariable($showCloseIcon);

    $sizes = [
        'tiny' => 'sm:w-72',
        'small' => 'sm:w-96',
        'medium' => 'sm:w-[32rem]',
        'big' => 'sm:w-[48rem]',
        'large' => 'sm:w-[60rem]',
        'xl' => 'sm:w-[86rem]',
        'omg' => 'w-full',
    ];

    if (!$blurBackdrop) $blurSize = 'none';
    if(!in_array($alignButtons, ['right', 'center', 'left'])) $alignButtons = 'right';
    if(!in_array($size, ACCEPTED_BLADEWIND_SIZES)) $size = 'medium';

//    $name = str_replace(' ', '-', $name);
    $cancelCss = ($cancelButtonLabel == '') ? 'hidden' : '';
    $okCss = ($okButtonLabel == '') ? 'hidden' : '';
    // the default close is the library's own, so it is delegated off a data
    // attribute rather than an inline onclick — a strict CSP blocks those, and a
    // modal you cannot dismiss is the worst version of that. a consumer-supplied
    // action is their own javascript and stays inline. see #608
    $okAction = $cancelAction = "hideModal('{$name}')";
    $okIsDefault = ($okButtonAction === 'close');
    $cancelIsDefault = ($cancelButtonAction === 'close');
    if(! $okIsDefault) $okAction = $okButtonAction . (($closeAfterAction) ? ';'.$okAction : '');
    if(! $cancelIsDefault) $cancelAction = $cancelButtonAction . (($closeAfterAction) ? ';'.$cancelAction : '');

    $button_size = ($stretchActionButtons) ? 'medium' : (($size == 'tiny') ? 'tiny' : 'small');

    // get colours that match the various types
   $type_colour = function() use ($type) {
      switch ($type){
          case 'warning': return "yellow"; break;
          case 'error': return "red"; break;
          case 'success': return "green"; break;
          case 'info': return "blue"; break;
      }
    };
    $type_colour = $type_colour();

   $blur_intensity = function() use ($blurSize) {
       return match ($blurSize) {
           'none' => "backdrop-blur-none",
           'small' => "backdrop-blur-sm",
           'large' => "backdrop-blur-lg",
           'xl' => "backdrop-blur-xl",
           'omg' => "backdrop-blur-3xl",
           default => "backdrop-blur-md",
       };
    };
@endphp
{{-- format-ignore-end --}}

<div data-name="{{$name}}" data-backdrop-can-close="{{$backdropCanClose}}"
     class="fixed inset-0 flex items-center justify-center bg-black/40 {{$blur_intensity()}} z-40 flex bw-modal bw-{{$name}}-modal hidden overscroll-contain">
    <div class="{{$sizes[$size]}} @if($size=='omg') sm:px-12 @else max-w-screen @endif px-5 m-auto bw-{{$name}} animate__faster">
        <div class="bg-white relative dark:bg-dark-700/90 dark:border dark:border-dark-500/10 {{getRadiusString($radius)}} drop-shadow-2xl">
            @if( $showActionButtons && $showCloseIcon)
                <a href="javascript:void(0)"
                   @if($cancelIsDefault) data-bw-modal-close="{{ $name }}"
                   @else onclick="{!! $cancelAction !!}" @endif
                >
                    <x-bladewind::icon
                            name="x-mark"
                            class="p-1.5 stroke-2 modal-close-icon right-3.5 top-3 absolute rounded-full
                            size-7 text-gray-500 hover:text-gray-200 bg-gray-200
                            hover:bg-gray-700 dark:bg-dark-800 dark:hover:bg-dark-900 dark:hover:text-dark-400"/>
                </a>
            @endif
            <div class="{{(!empty($type) || !empty($icon))?'flex':'flex-initial'}} p-5">
                @if(!empty($type) || !empty($icon))
                    <div class="modal-icon grow-0 pr-2 pt-1.5">
                        @if(!empty($type) )
                            <x-bladewind::modal-icon
                                    type="{{ $type }}"
                                    icon="{{$icon}}"
                                    class="size-14 p-2.5 rounded-full bg-{{$type_colour}}-100 dark:bg-{{$type_colour}}-600
                                    text-{{$type_colour}}-600 dark:text-{{$type_colour}}-100 stroke-1"/>
                        @endif
                        @if(!empty($icon) && empty($type))
                            <x-bladewind::icon name="{{ $icon }}" class="size-14 {{$iconCss}}"/>
                        @endif
                    </div>
                @endif
                <div class="modal-body grow px-2 pb-1 {{ $bodyCss  }} md:max-h-none md:overflow-visible max-h-[calc(100vh-120px)] overflow-y-auto">
                    <h1 class="text-lg font-light leading-5 text-gray-800 dark:text-dark-200 tracking-wide modal-title text-left pt-2">{{ $title }}</h1>
                    <div class="modal-text text-gray-500 dark:text-dark-300 pt-2 text-base text-left font-light tracking-wide leading-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
            @if( $showActionButtons )
                <div @class([
                    'modal-footer bg-gray-100 dark:bg-dark-800/50 border-t border-t-gray-200 dark:border-t-dark-600/50',
                    'py-3 px-6  '. $footerCss . getRadiusString($radius, 'b'),
                    ' space-x-2' => !$stretchActionButtons,
                    'flex flex-col-reverse' => $stretchActionButtons,
                    'text-center' => ($centerActionButtons || $size == 'tiny'),
                    'text-'.$alignButtons => !$centerActionButtons && $size != 'tiny'])>
                    <x-bladewind::button
                            type="secondary"
                            size="{{$button_size}}"
                            outline="true"
                            {{-- a null attribute is dropped, which keeps the choice
                                 out of the tag's attribute list where a blade @if
                                 cannot go --}}
                            :data-bw-modal-close="$cancelIsDefault ? $name : null"
                            :onclick="$cancelIsDefault ? null : $cancelAction"
                            @class([
                                'cancel ' . $cancelCss,
                                'block w-full mt-2' => $stretchActionButtons])>{{$cancelButtonLabel}}</x-bladewind::button>

                    <x-bladewind::button
                            size="{{$button_size}}"
                            :data-bw-modal-close="$okIsDefault ? $name : null"
                            :onclick="$okIsDefault ? null : $okAction"
                            @class([
                                'okay ' . $okCss,
                                'block w-full' => $stretchActionButtons])>{{$okButtonLabel}}</x-bladewind::button>
                </div>
            @endif
        </div>
    </div>
</div>
<span class="overflow-hidden"></span>

<x-bladewind::script :nonce="$nonce">
    domEl('.bw-{{$name}}-modal').addEventListener('click', function (e) {
    let backdropCanClose = this.getAttribute('data-backdrop-can-close');
    if (backdropCanClose) hideModal('{{$name}}');
    });

    domEl('.bw-{{$name}}').addEventListener('click', function (e) {
    e.stopImmediatePropagation();
    });

    if (domEls('.bw-{{$name}}-modal .modal-footer>button')) {
    domEls('.bw-{{$name}}-modal .modal-footer>button').forEach((el) => {
    el.addEventListener('click', function (e) {
    e.stopImmediatePropagation();
    });
    });
    }

    document.addEventListener('keyup', function (e) {
    if (e.key === "Escape") {
    if (openModals !== undefined && openModals.length > 0) {
    let modalName = openModals[(openModals.length - 1)];
    if (domEl(`.bw-${modalName}-modal`).getAttribute('data-backdrop-can-close') === '1') {
    hideModal(modalName);
    e.stopImmediatePropagation();
    }
    }
    }
    });

    document.addEventListener('keydown', trapFocusInModal);

</x-bladewind::script>