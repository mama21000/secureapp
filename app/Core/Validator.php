<?php
declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public static function string(string $value, int $min, int $max, string $fieldName = 'Field'): ?string
    {
        $len = mb_strlen($value);
        if ($len < $min) return "{$fieldName} must be at least {$min} character" . ($min === 1 ? '' : 's') . '.';
        if ($len > $max) return "{$fieldName} must not exceed {$max} characters.";
        return null;
    }

    public static function username(string $value): ?string
    {
        if (!preg_match('/^[a-zA-Z0-9_]{3,40}$/', $value))
            return 'Username must be 3–40 characters and contain only letters, numbers, or underscores.';
        return null;
    }

    public static function moneyAmount(mixed $value, int $min = 1, int $max = 100000): ?string
    {
        if (!is_numeric($value)) return 'Amount must be a number.';
        $amount = (int)$value;
        if ((float)$value !== (float)$amount) return 'Amount must be a whole number (no decimals).';
        if ($amount < $min) return "Amount must be at least {$min}.";
        if ($amount > $max) return "Amount must not exceed " . number_format($max) . '.';
        return null;
    }

    public static function phone(string $value): ?string
    {
        if (!preg_match('/^[0-9+\-()\s]{6,30}$/', $value))
            return 'Phone number must be 6–30 characters and contain only digits, spaces, +, -, or parentheses.';
        return null;
    }

    public static function email(string $value): ?string
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL) || mb_strlen($value) > 120)
            return 'Please enter a valid email address (max 120 characters).';
        return null;
    }

    public static function fullName(string $value): ?string
    {
        if (mb_strlen($value) > 120) return 'Full name must not exceed 120 characters.';
        return null;
    }

    public static function bio(string $value): ?string
    {
        if (mb_strlen($value) > 10000) return 'Biography must not exceed 10,000 characters.';
        return null;
    }

    public static function transferComment(string $value): ?string
    {
        if (mb_strlen($value) > 255) return 'Comment must not exceed 255 characters.';
        return null;
    }
}