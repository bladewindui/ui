{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName(),
    'rating' => 0,
    'size' => config('bladewind.rating.size', 'small'),
    'color' => 'orange',
    'onclick' => 'javascript:void(0)',
    'type' => config('bladewind.rating.type', 'star'),
    'clickable' => config('bladewind.rating.clickable', true),
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $clickable = parseBladewindVariable($clickable);
    $name = parseBladewindName($name);
    $rating = (!is_numeric($rating) || $rating < 0) ? 0 : min((int) $rating, 5);
    $size = (!in_array($size, ['small', 'medium', 'big'])) ? 'small' : $size;

    $icon_sizes = [
        'small' => 'size-6',
        'medium' => 'size-10',
        'big' => 'size-14',
    ];
@endphp
{{-- format-ignore-end --}}

@if($clickable)
    <x-bladewind::input type="hidden" name="{{$name}}" id="{{$name}}" class="rating-value-{{$name}}"
                        selected_value="{{$rating}}"/>
@endif
<div @class(['inline-flex items-center', 'bw-rating-slider-'.$name => $clickable])
     @if($clickable)
         role="slider"
         tabindex="0"
         aria-valuemin="0"
         aria-valuemax="5"
         aria-valuenow="{{ $rating }}"
         aria-valuetext="{{ trans_choice('bladewind::bladewind.rating_value', (int) $rating, ['value' => $rating]) }}"
         aria-label="{{ __('bladewind::bladewind.rating_label') }}"
     @else
         role="img"
         aria-label="{{ trans_choice('bladewind::bladewind.rating_value', (int) $rating, ['value' => $rating]) }}"
     @endif>
    @for ($x = 1; $x < 6; $x++)
        <div data-rating="{{$x}}"
             @class([
                'relative inline-flex items-center justify-center bw-rating-'.$x.' '.$name,
                $icon_sizes[$size],
                'rated' => ($x <= $rating),
                'cursor-pointer' => $clickable,
                'cursor-default' => !$clickable,
             ])
             @if($clickable)
                 onmouseover="previewRating('{{$name}}', {{$x}})"
                 onmouseout="restoreRating('{{$name}}')"
                 onclick="setRating('{{$name}}', {{$x}});{!!$onclick!!}"
             @endif>
            <svg xmlns="http://www.w3.org/2000/svg"
                 @class([
                    'filled absolute inset-0 text-'.$color.'-600',
                    $icon_sizes[$size],
                    'hidden' => ($x > $rating),
                 ])
                 viewBox="0 0 20 20" fill="currentColor">
                @if($type == 'heart')
                    <path fill-rule="evenodd"
                          d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                          clip-rule="evenodd"/>
                @elseif($type=='thumbsup')
                    <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/>
                @else
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                @endif
            </svg>

            <svg xmlns="http://www.w3.org/2000/svg"
                 @class([
                    'empty absolute inset-0 text-'.$color.'-600',
                    $icon_sizes[$size],
                    'hidden' => ($x <= $rating),
                 ])
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                @if($type == 'heart')
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                @elseif($type == 'thumbsup')
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                @endif
            </svg>
        </div>
    @endfor
</div>
@if($clickable)
    <x-bladewind::script :nonce="$nonce">
        if (typeof window.previewRating !== 'function') {
        window.previewRating = function (name, hoverValue) {
        for (let x = 1; x <= 5; x++) {
        if (x <= hoverValue) {
        hide(`.bw-rating-${x}.${name} .empty`);
        unhide(`.bw-rating-${x}.${name} .filled`);
        } else {
        unhide(`.bw-rating-${x}.${name} .empty`);
        hide(`.bw-rating-${x}.${name} .filled`);
        }
        }
        };

        window.restoreRating = function (name) {
        const input = domEl(`.rating-value-${name}`);
        const rating = parseInt(input?.value || '0', 10) || 0;
        previewRating(name, rating);
        };

        window.setRating = function (name, rate) {
        changeCssForDomArray(`.${name}.rated`, 'rated', 'remove');
        for (let x = 1; x <= 5; x++) {
        if (x <= rate) changeCss(`.bw-rating-${x}.${name}`, 'rated');
        }
        const input = domEl(`.rating-value-${name}`);
        if (input) input.value = rate;

        // the slider reports its own value, so it has to move with the stars
        const slider = domEl(`.bw-rating-slider-${name}`);
        if (slider) {
        slider.setAttribute('aria-valuenow', rate);
        slider.setAttribute('aria-valuetext', rate === 0 ? 'Not rated' : `${rate} out of 5`);
        }

        previewRating(name, rate);
        };

        /**
         * A control with slider semantics has to be operable from the keyboard, or
         * the role is a lie: focusable, announced as a slider, and inert. Arrow keys
         * step by one, Home and End jump to the bounds.
         */
        window.enableRatingKeyboard = function (name) {
        const slider = domEl(`.bw-rating-slider-${name}`);
        if (!slider || slider.dataset.bwKeyboard === '1') return;
        slider.dataset.bwKeyboard = '1';

        slider.addEventListener('keydown', (e) => {
        const current = parseInt(domEl(`.rating-value-${name}`)?.value || '0', 10) || 0;
        let next = current;

        if (e.key === 'ArrowRight' || e.key === 'ArrowUp') next = Math.min(5, current + 1);
        else if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') next = Math.max(0, current - 1);
        else if (e.key === 'Home') next = 0;
        else if (e.key === 'End') next = 5;
        else return;

        e.preventDefault();
        setRating(name, next);
        });
        };
        }

        enableRatingKeyboard('{{$name}}');
    </x-bladewind::script>
@endif
