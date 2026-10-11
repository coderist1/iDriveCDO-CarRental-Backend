<?php

namespace App\Support;

/**
 * One stored form for a Philippine mobile: +639171234567.
 * 09171234567, +639171234567, and 639171234567 are the same number.
 */
final class Phone
{
    public static function canonical(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
        if (preg_match('/^63(9\d{9})$/', $digits, $match) === 1) {
            return '+63'.$match[1];
        }
        if (preg_match('/^0(9\d{9})$/', $digits, $match) === 1) {
            return '+63'.$match[1];
        }
        if (preg_match('/^(9\d{9})$/', $digits, $match) === 1) {
            return '+63'.$match[1];
        }

        return null;
    }
}
