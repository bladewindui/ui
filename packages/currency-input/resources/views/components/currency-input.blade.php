{{-- format-ignore-start --}}
@props([
    'name' => defaultBladewindName('input-'),
    'label' => '',
    // ISO 4217 currency code, e.g. USD, EUR, GHS
    'currency' => config('bladewind.currency_input.currency', 'USD'),
    // BCP 47 locale. Only used when PHP's intl extension is installed, to
    // derive the symbol's position and the decimal/thousands separators the
    // way that locale actually writes them. Without ext-intl, every currency
    // still gets a sensible symbol and precision, always shown as a prefix
    // with '.' and ',' separators.
    'locale' => config('bladewind.currency_input.locale', 'en-US'),
    // overrides for whatever locale/currency would otherwise resolve to
    'symbol' => null,
    'symbolPosition' => null, // prefix | suffix
    'decimalSeparator' => null,
    'thousandsSeparator' => null,
    'precision' => null,
    'required' => false,
    'selectedValue' => '',
    'placeholder' => '',
    'size' => config('bladewind.input.size', 'regular'),
    'class' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $required = parseBladewindVariable($required);
    $currency = strtoupper(trim((string) $currency)) ?: 'USD';

    // currencies with no minor unit — their amounts are never written with
    // decimal places, regardless of locale
    $zeroDecimalCurrencies = [
        'BIF', 'CLP', 'DJF', 'GNF', 'ISK', 'JPY', 'KMF', 'KRW',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    // used only when ext-intl is unavailable, or as the symbol fallback
    // when a currency has no glyph in the requesting locale
    $fallbackSymbols = [
        'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'CNY' => '¥',
        'GHS' => 'GH₵', 'NGN' => '₦', 'ZAR' => 'R', 'KES' => 'KSh',
        'INR' => '₹', 'CAD' => 'CA$', 'AUD' => 'A$', 'CHF' => 'CHF', 'BRL' => 'R$',
    ];

    $resolvedSymbol = $symbol;
    $resolvedPosition = $symbolPosition;
    $resolvedDecimal = $decimalSeparator;
    $resolvedThousands = $thousandsSeparator;
    $resolvedPrecision = $precision;

    if (class_exists(\NumberFormatter::class)) {
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $sample = $formatter->formatCurrency(0, $currency);

        if ($sample !== false) {
            $decimalSymbol = $formatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
            $thousandsSymbol = $formatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
            $stripPattern = '/[0-9\s'.preg_quote($decimalSymbol, '/').preg_quote($thousandsSymbol, '/').']/u';
            $currencySymbol = trim(preg_replace($stripPattern, '', $sample));

            $decimalPos = mb_strrpos($sample, $decimalSymbol);
            $fractionDigits = $decimalPos !== false
                ? strlen(preg_replace('/\D/', '', mb_substr($sample, $decimalPos + mb_strlen($decimalSymbol))))
                : 0;

            $resolvedSymbol ??= ($currencySymbol !== '' ? $currencySymbol : null);
            $resolvedPosition ??= (! empty($resolvedSymbol) && mb_strpos($sample, $resolvedSymbol) === 0) ? 'prefix' : 'suffix';
            $resolvedDecimal ??= $decimalSymbol;
            $resolvedThousands ??= $thousandsSymbol;
            $resolvedPrecision ??= $fractionDigits;
        }
    }

    $resolvedSymbol ??= $fallbackSymbols[$currency] ?? $currency;
    $resolvedPosition = in_array($resolvedPosition, ['prefix', 'suffix']) ? $resolvedPosition : 'prefix';
    $resolvedDecimal ??= '.';
    $resolvedThousands ??= ',';
    $resolvedPrecision ??= (in_array($currency, $zeroDecimalCurrencies) ? 0 : 2);

    $isPrefix = $resolvedPosition === 'prefix';
@endphp
{{-- format-ignore-end --}}
<x-bladewind::input
    :name="$name"
    :label="$label"
    :prefix="$isPrefix ? $resolvedSymbol : ''"
    :suffix="!$isPrefix ? $resolvedSymbol : ''"
    money="true"
    :money_decimal_separator="$resolvedDecimal"
    :money_thousands_separator="$resolvedThousands"
    :money_precision="$resolvedPrecision"
    :required="$required"
    :selected_value="$selectedValue"
    :placeholder="$placeholder"
    :size="$size"
    class="{{ $class }}"
    :nonce="$nonce"
    inputmode="decimal"
/>
