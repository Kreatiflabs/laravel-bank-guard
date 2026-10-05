<?php

namespace Kreatiflabs\BankGuard\Sanitizers;

class AccountSanitizer
{
    /**
     * Clean and sanitize bank account number by stripping all non-digit characters.
     */
    public static function clean(?string $accountNumber): string
    {
        if ($accountNumber === null) {
            return '';
        }

        return (string) preg_replace('/[^0-9]/', '', $accountNumber);
    }

    /**
     * Mask an account number for security or display purposes.
     * E.g., '1234567890' -> '1234***890' or '******7890'
     */
    public static function mask(string $accountNumber, int $visibleStart = 0, int $visibleEnd = 4, string $maskChar = '*'): string
    {
        $clean = self::clean($accountNumber);
        $length = strlen($clean);

        if ($length <= ($visibleStart + $visibleEnd)) {
            return str_repeat($maskChar, $length);
        }

        $prefix = $visibleStart > 0 ? substr($clean, 0, $visibleStart) : '';
        $suffix = $visibleEnd > 0 ? substr($clean, -$visibleEnd) : '';
        $maskedLength = $length - $visibleStart - $visibleEnd;

        return $prefix . str_repeat($maskChar, $maskedLength) . $suffix;
    }
}
