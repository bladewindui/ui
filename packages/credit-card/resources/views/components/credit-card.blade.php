{{-- format-ignore-start --}}
@props([
    // unique name for this card. used as the css hook and js variable name
    'name' => defaultBladewindName('bw-credit-card-'),
    'cardholderName' => '',
    // raw digits, or a masked saved-card value such as "**** **** **** 4242"
    // (preserved as-is rather than reformatted, since the real digits aren't known)
    'number' => '',
    'expiryMonth' => '',
    'expiryYear' => '',
    'cvc' => '',
    // force a network logo instead of auto-detecting from number:
    // visa, mastercard, amex, discover, diners, jcb, unionpay, maestro
    'brand' => null,
    // gradient colour, from the shared bladewind palette. ignored in the outline variant
    'color' => config('bladewind.credit_card.color', 'primary'),
    // gradient (full-colour face), outline (bare silhouette), or inline
    // (compact number/expiry/cvc fields only, no flip)
    'variant' => config('bladewind.credit_card.variant', 'gradient'),
    'flipped' => config('bladewind.credit_card.flipped', false),
    'disabled' => config('bladewind.credit_card.disabled', false),
    'readonly' => config('bladewind.credit_card.readonly', false),
    // fails validate() while any field is incomplete
    'required' => config('bladewind.credit_card.required', false),
    'errorMessage' => '',
    'showErrorInline' => config('bladewind.credit_card.show_error_inline', false),
    // javascript function called with the structured value whenever it changes
    'onChange' => null,
    'class' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $variant = in_array($variant, ['gradient', 'outline', 'inline']) ? $variant : 'gradient';
    $flipped = parseBladewindVariable($flipped);
    $disabled = parseBladewindVariable($disabled);
    $readonly = parseBladewindVariable($readonly);
    $required = parseBladewindVariable($required);
    $showErrorInline = parseBladewindVariable($showErrorInline);

    $rawNumber = (string) $number;
    $isMasked = $rawNumber !== '' && ! ctype_digit(str_replace(' ', '', $rawNumber));
    $digits = $isMasked ? '' : preg_replace('/\D/', '', $rawNumber);
    $activeBrand = $brand ?: ($digits !== '' ? \Mkocansey\Bladewind\CreditCard\CreditCardBrand::detect($digits) : null);
    $formattedNumber = $isMasked ? $rawNumber : \Mkocansey\Bladewind\CreditCard\CreditCardBrand::format($digits, $activeBrand);
    $cvcLength = \Mkocansey\Bladewind\CreditCard\CreditCardBrand::cvcLength($activeBrand);

    $brandLabels = [
        'visa' => 'VISA', 'mastercard' => 'Mastercard', 'amex' => 'AMEX', 'discover' => 'DISCOVER',
        'diners' => 'Diners Club', 'jcb' => 'JCB', 'unionpay' => 'UnionPay', 'maestro' => 'Maestro',
    ];
@endphp
{{-- format-ignore-end --}}

<div data-bw-credit-card="{{ $name }}" @class(['bw-credit-card', $class])>
    @if($variant === 'inline')
        <div class="space-y-3 max-w-sm">
            <div class="relative">
                <input type="text" inputmode="numeric" autocomplete="cc-number" data-number
                       placeholder="Card number" value="{{ $formattedNumber }}"
                       @if($disabled) disabled @endif @if($readonly) readonly @endif
                       class="w-full rounded-lg border border-gray-300 dark:border-dark-600 dark:bg-dark-900/50 px-3 py-2 pr-14 text-sm focus:outline-primary-500 focus:border-primary-500"/>
                <span data-brand-label class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-semibold text-gray-400 uppercase">{{ $brandLabels[$activeBrand] ?? '' }}</span>
            </div>
            <div class="flex gap-3">
                <input type="text" inputmode="numeric" autocomplete="cc-exp" data-expiry
                       placeholder="MM / YY" value="{{ $expiryMonth && $expiryYear ? sprintf('%02d / %02d', $expiryMonth, $expiryYear) : '' }}"
                       @if($disabled) disabled @endif @if($readonly) readonly @endif
                       class="w-1/2 rounded-lg border border-gray-300 dark:border-dark-600 dark:bg-dark-900/50 px-3 py-2 text-sm focus:outline-primary-500 focus:border-primary-500"/>
                <input type="text" inputmode="numeric" autocomplete="cc-csc" data-cvc
                       placeholder="CVC" value="{{ $cvc }}"
                       @if($disabled) disabled @endif @if($readonly) readonly @endif
                       class="w-1/2 rounded-lg border border-gray-300 dark:border-dark-600 dark:bg-dark-900/50 px-3 py-2 text-sm focus:outline-primary-500 focus:border-primary-500"/>
            </div>
        </div>
    @else
        <div data-flipper class="relative w-full max-w-sm aspect-[86/54] transition-transform duration-500" style="perspective: 1000px;">
            <div data-flip-inner class="relative w-full h-full transition-transform duration-500" style="transform-style: preserve-3d; {{ $flipped ? 'transform: rotateY(180deg);' : '' }}">

                {{-- front --}}
                <div data-front @class([
                        'absolute inset-0 rounded-2xl p-5 flex flex-col justify-between text-white shadow-lg',
                        "bg-linear-to-br from-$color-500 to-$color-700" => $variant === 'gradient',
                        'border-2 border-gray-300 dark:border-dark-600 text-gray-700 dark:text-dark-200' => $variant === 'outline',
                     ]) style="backface-visibility: hidden;">
                    <div class="flex items-start justify-between">
                        <div class="h-8 w-11 rounded-md bg-linear-to-br from-yellow-200 to-yellow-500"></div>
                        <x-bladewind::icon name="signal" class="size-6 rotate-90 opacity-80"/>
                    </div>

                    <div>
                        <input type="text" inputmode="numeric" autocomplete="cc-number" data-number
                               placeholder="•••• •••• •••• ••••" value="{{ $formattedNumber }}"
                               @if($disabled) disabled @endif @if($readonly) readonly @endif
                               @class([
                                   'w-full bg-transparent border-0 p-0 text-lg tracking-widest font-medium placeholder-white/50 focus:outline-none focus:ring-0' => $variant === 'gradient',
                                   'w-full bg-transparent border-0 p-0 text-lg tracking-widest font-medium placeholder-gray-400 dark:placeholder-dark-500 focus:outline-none focus:ring-0' => $variant === 'outline',
                               ])/>
                    </div>

                    <div class="flex items-end justify-between gap-3">
                        <div class="min-w-0 grow">
                            <div class="text-[10px] uppercase opacity-70">Cardholder</div>
                            <input type="text" autocomplete="cc-name" data-name
                                   placeholder="Full name" value="{{ $cardholderName }}"
                                   @if($disabled) disabled @endif @if($readonly) readonly @endif
                                   @class([
                                       'w-full bg-transparent border-0 p-0 text-sm font-medium uppercase truncate placeholder-white/50 focus:outline-none focus:ring-0' => $variant === 'gradient',
                                       'w-full bg-transparent border-0 p-0 text-sm font-medium uppercase truncate placeholder-gray-400 dark:placeholder-dark-500 focus:outline-none focus:ring-0' => $variant === 'outline',
                                   ])/>
                        </div>
                        <div class="shrink-0 flex items-center gap-1">
                            <div>
                                <div class="text-[10px] uppercase opacity-70">Expiry</div>
                                <div class="flex items-center gap-0.5 text-sm font-medium">
                                    <input type="text" inputmode="numeric" data-expiry-month maxlength="2" placeholder="MM" value="{{ $expiryMonth }}"
                                           @if($disabled) disabled @endif @if($readonly) readonly @endif
                                           @class(['w-6 bg-transparent border-0 p-0 text-center placeholder-white/50 focus:outline-none focus:ring-0' => $variant === 'gradient', 'w-6 bg-transparent border-0 p-0 text-center placeholder-gray-400 dark:placeholder-dark-500 focus:outline-none focus:ring-0' => $variant === 'outline'])/>
                                    <span>/</span>
                                    <input type="text" inputmode="numeric" data-expiry-year maxlength="2" placeholder="YY" value="{{ $expiryYear }}"
                                           @if($disabled) disabled @endif @if($readonly) readonly @endif
                                           @class(['w-6 bg-transparent border-0 p-0 text-center placeholder-white/50 focus:outline-none focus:ring-0' => $variant === 'gradient', 'w-6 bg-transparent border-0 p-0 text-center placeholder-gray-400 dark:placeholder-dark-500 focus:outline-none focus:ring-0' => $variant === 'outline'])/>
                                </div>
                            </div>
                            <span data-brand-label class="text-sm font-bold italic ml-2">{{ $brandLabels[$activeBrand] ?? '' }}</span>
                        </div>
                    </div>

                    @unless($disabled)
                        <button type="button" data-flip-button aria-label="Show CVC"
                                class="absolute top-1/2 -right-3 -translate-y-1/2 size-8 rounded-full bg-white text-gray-700 shadow-md grid place-items-center hover:bg-gray-50">
                            <x-bladewind::icon name="arrow-path" class="size-4"/>
                        </button>
                    @endunless
                </div>

                {{-- back --}}
                <div data-back @class([
                        'absolute inset-0 rounded-2xl flex flex-col text-white shadow-lg',
                        "bg-linear-to-br from-$color-700 to-$color-900" => $variant === 'gradient',
                        'border-2 border-gray-300 dark:border-dark-600 text-gray-700 dark:text-dark-200' => $variant === 'outline',
                     ]) style="backface-visibility: hidden; transform: rotateY(180deg);">
                    <div class="mt-5 h-10 w-full bg-black/80"></div>
                    <div class="p-5 flex flex-col gap-2 grow justify-center">
                        <div class="flex items-center justify-end gap-2">
                            <div class="grow h-8 rounded bg-white/80"></div>
                            <input type="text" inputmode="numeric" autocomplete="cc-csc" data-cvc
                                   placeholder="CVC" maxlength="{{ $cvcLength }}" value="{{ $cvc }}"
                                   @if($disabled) disabled @endif @if($readonly) readonly @endif
                                   class="w-14 h-8 rounded bg-white text-center text-sm text-gray-800 border-0 focus:outline-none focus:ring-2 focus:ring-white"/>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <span data-brand-label class="text-sm font-bold italic">{{ $brandLabels[$activeBrand] ?? '' }}</span>
                        </div>
                    </div>

                    @unless($disabled)
                        <button type="button" data-flip-button aria-label="Show card number"
                                class="absolute top-1/2 -left-3 -translate-y-1/2 size-8 rounded-full bg-white text-gray-700 shadow-md grid place-items-center hover:bg-gray-50">
                            <x-bladewind::icon name="arrow-path" class="size-4"/>
                        </button>
                    @endunless
                </div>
            </div>
        </div>
    @endif

    @if($showErrorInline)
        <p data-error hidden class="text-xs text-red-500 mt-2">{{ $errorMessage }}</p>
    @endif
</div>

@once
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/credit-card.js') }}"></x-bladewind::script>
@endonce
<x-bladewind::script :nonce="$nonce">
    (() => {
        const root = domEl('[data-bw-credit-card="{{ $name }}"]');
        if (root && root.dataset.bwInitialised === 'true') return;
        if (root) root.dataset.bwInitialised = 'true';

        window.{{ $name }} = new BladewindCreditCard('{{ $name }}', {
            required: {{ $required ? 'true' : 'false' }},
            errorMessage: {!! json_encode($errorMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!},
            onChange: @if($onChange) {{ $onChange }} @else null @endif,
        });
    })();
</x-bladewind::script>
