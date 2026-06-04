<?php

namespace App\Support;

class PhoneNumber
{
    public static function digits($value): string
    {
        return preg_replace('/\D+/', '', (string) $value);
    }

    public static function canonicalUzDigits($value): ?string
    {
        $digits = self::digits($value);

        if ($digits === '') {
            return null;
        }

        if (strpos($digits, '998') === 0) {
            if (strlen($digits) > 12) {
                $tail = substr($digits, -12);

                if (strpos($tail, '998') === 0) {
                    return $tail;
                }
            }

            return $digits;
        }

        if (strlen($digits) === 10 && strpos($digits, '0') === 0) {
            return '998' . substr($digits, 1);
        }

        if (strlen($digits) >= 9) {
            return '998' . substr($digits, -9);
        }

        if (strlen($digits) >= 7) {
            return '998' . $digits;
        }

        return null;
    }

    public static function formatUz($value): ?string
    {
        $canonical = self::canonicalUzDigits($value);

        if ($canonical === null) {
            return null;
        }

        return '+' . $canonical;
    }

    public static function mysqlCanonicalUzDigitsExpression(string $column): string
    {
        $digits = self::mysqlDigitsExpression($column);

        return "CASE "
            . "WHEN $digits = '' THEN NULL "
            . "WHEN LEFT($digits, 3) = '998' THEN "
            . "CASE "
            . "WHEN CHAR_LENGTH($digits) > 12 AND LEFT(RIGHT($digits, 12), 3) = '998' THEN RIGHT($digits, 12) "
            . "ELSE $digits "
            . "END "
            . "WHEN CHAR_LENGTH($digits) = 10 AND LEFT($digits, 1) = '0' THEN CONCAT('998', RIGHT($digits, 9)) "
            . "WHEN CHAR_LENGTH($digits) >= 9 THEN CONCAT('998', RIGHT($digits, 9)) "
            . "WHEN CHAR_LENGTH($digits) >= 7 THEN CONCAT('998', $digits) "
            . "ELSE NULL "
            . "END";
    }

    private static function mysqlDigitsExpression(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($column, '+', ''), ' ', ''), '-', ''), '(', ''), ')', ''), '.', '')";
    }
}
