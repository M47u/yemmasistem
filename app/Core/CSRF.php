<?php

namespace App\Core;

class CSRF
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (!Session::has(self::KEY)) {
            Session::set(self::KEY, bin2hex(random_bytes(32)));
        }
        return Session::get(self::KEY);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    public static function validate(string $token): bool
    {
        $stored = Session::get(self::KEY, '');
        return hash_equals($stored, $token);
    }

    public static function regenerate(): void
    {
        Session::set(self::KEY, bin2hex(random_bytes(32)));
    }
}
