[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/input)](https://packagist.org/packages/bladewindui/input)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Input

BladewindUI — Input field component.

## Installation

```bash
composer require bladewindui/input
```

Or install the full library:

```bash
composer require bladewindui/ui
```

## Input masking

The input field supports masking, modelled on the [Alpine.js mask plugin](https://alpinejs.dev/plugins/mask).
Use the wildcards `9` (digit), `a` (letter) and `*` (alphanumeric) in a template — every other character is a
literal that is inserted automatically as the user types.

```blade
<x-bladewind::input name="phone" mask="(999) 999-9999" />          {{-- 9 → digit --}}
<x-bladewind::input name="postcode" mask="a9a 9a9" />              {{-- a → letter --}}
<x-bladewind::input name="key" mask="****-****-****" />            {{-- * → letter or digit --}}
```

**Dynamic masks** change as the user types. A `creditCard` mask is built in (detects Amex / Diners / Visa-style
cards) and needs no JavaScript:

```blade
<x-bladewind::input name="card" dynamicMask="creditCard" />
```

For your own dynamic masks, point `dynamicMask` at a global JS function that returns a template for the current
value. A global of the same name as a built-in takes precedence, so you can override `creditCard` too:

```blade
<x-bladewind::input name="zip" dynamicMask="zipCode" />

<script>
    function zipCode(input) {
        const digits = input.replace(/\D/g, '');
        return digits.length <= 5 ? '99999' : '99999-9999'; // ZIP or ZIP+4
    }
</script>
```

**Money inputs** add thousands separators and fix the decimal places. Customise the separators and precision:

```blade
<x-bladewind::input name="price" money="true" />
<x-bladewind::input name="price_eu" money="true"
    moneyThousandsSeparator="." moneyDecimalSeparator="," moneyPrecision="2" />
```

| Attribute                 | Default | Description                                                                 |
|---------------------------|---------|-----------------------------------------------------------------------------|
| `mask`                    | `''`    | Static mask template using `9`, `a`, `*` wildcards.                         |
| `dynamicMask`             | `null`  | Name of a JS function `(input) => template` for masks that change as you type. |
| `money`                   | `false` | Format the field as a money input.                                          |
| `moneyDecimalSeparator`   | `.`     | Decimal separator used when `money="true"`.                                 |
| `moneyThousandsSeparator` | `,`     | Thousands separator used when `money="true"`.                               |
| `moneyPrecision`          | `2`     | Number of decimal places allowed when `money="true"`. `0` disables decimals. |

> Masking forces the field to `type="text"` so formatted values (separators, letters) are preserved.

## Documentation

Full documentation, live demos, and all available attributes are at **[bladewindui.com](https://bladewindui.com)**.

## License

MIT — see the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
