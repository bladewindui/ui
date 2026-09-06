<?php

namespace Mkocansey\Bladewind\CreditCard;

/**
 * Detects a card network from a number's prefix and knows how each one
 * groups and limits its digits. Mirrored exactly in credit-card.js so a
 * server-rendered value and a value typed live agree on the same brand.
 */
class CreditCardBrand
{
    /**
     * @return array<string, array{pattern: string, groups: list<int>, label: string}>
     */
    public static function definitions(): array
    {
        return [
            'visa' => ['pattern' => '/^4/', 'groups' => [4, 4, 4, 4], 'label' => 'Visa'],
            'mastercard' => ['pattern' => '/^(5[1-5]|2(22[1-9]|2[3-9]\d|[3-6]\d{2}|7[01]\d|720))/', 'groups' => [4, 4, 4, 4], 'label' => 'Mastercard'],
            'amex' => ['pattern' => '/^3[47]/', 'groups' => [4, 6, 5], 'label' => 'American Express'],
            'discover' => ['pattern' => '/^(6011|65|64[4-9])/', 'groups' => [4, 4, 4, 4], 'label' => 'Discover'],
            'diners' => ['pattern' => '/^(30[0-5]|36|38)/', 'groups' => [4, 6, 4], 'label' => 'Diners Club'],
            'jcb' => ['pattern' => '/^35(2[89]|[3-8]\d)/', 'groups' => [4, 4, 4, 4], 'label' => 'JCB'],
            'unionpay' => ['pattern' => '/^62/', 'groups' => [4, 4, 4, 4], 'label' => 'UnionPay'],
            'maestro' => ['pattern' => '/^(50|5[6-8]|6304|6759|676770|676774)/', 'groups' => [4, 4, 4, 4], 'label' => 'Maestro'],
        ];
    }

    public static function detect(string $digits): ?string
    {
        foreach (static::definitions() as $brand => $definition) {
            if (preg_match($definition['pattern'], $digits)) {
                return $brand;
            }
        }

        return null;
    }

    public static function length(?string $brand): int
    {
        return match ($brand) {
            'amex' => 15,
            'diners' => 14,
            default => 16,
        };
    }

    public static function cvcLength(?string $brand): int
    {
        return $brand === 'amex' ? 4 : 3;
    }

    public static function label(?string $brand): string
    {
        return static::definitions()[$brand]['label'] ?? '';
    }

    /**
     * Groups digits per the brand's format, e.g. Amex's 4-6-5 or the
     * common 4-4-4-4, leaving any digits past the known length ungrouped
     * rather than dropping them.
     */
    public static function format(string $digits, ?string $brand): string
    {
        $groups = static::definitions()[$brand]['groups'] ?? [4, 4, 4, 4];

        $chunks = [];
        $offset = 0;
        foreach ($groups as $size) {
            $chunk = substr($digits, $offset, $size);
            if ($chunk === '') {
                break;
            }
            $chunks[] = $chunk;
            $offset += $size;
        }

        if ($offset < strlen($digits)) {
            $chunks[] = substr($digits, $offset);
        }

        return implode(' ', $chunks);
    }
}
