<?php

namespace App\Support;

/**
 * Account mobiles are stored as +639XXXXXXXXX (ck_users_phone).
 */
final class Phone
{
    public static function toE164(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
        $national = '';

        if (strlen($digits) === 12 && str_starts_with($digits, '63')) {
            $national = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $national = substr($digits, 1);
        } elseif (strlen($digits) === 10) {
            $national = $digits;
        }

        return preg_match('/^9\d{9}$/', $national) ? '+63'.$national : null;
    }
}
