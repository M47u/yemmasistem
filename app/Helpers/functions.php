<?php

use App\Core\View;
use App\Core\CSRF;
use App\Core\Auth;
use App\Core\Session;

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return View::e($value);
    }
}

if (!function_exists('money')) {
    function money(mixed $amount): string
    {
        return '$' . number_format((float)($amount ?? 0), 2, ',', '.');
    }
}

if (!function_exists('dateEs')) {
    function dateEs(string $date, string $format = 'd/m/Y'): string
    {
        if (!$date) return '-';
        $d = DateTime::createFromFormat('Y-m-d', substr($date, 0, 10));
        return $d ? $d->format($format) : '-';
    }
}

if (!function_exists('periodLabel')) {
    function periodLabel(int $year, int $month): string
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        return ($months[$month] ?? $month) . ' ' . $year;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return CSRF::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return CSRF::token();
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('can')) {
    function can(string $permission): bool
    {
        return Auth::can($permission);
    }
}

if (!function_exists('flash_message')) {
    function flash_message(): array
    {
        return [
            'type'    => Session::getFlash('flash_type', ''),
            'message' => Session::getFlash('flash_message', ''),
        ];
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $base = rtrim(APP_URL, '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(APP_URL, '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);
        return match(true) {
            $diff < 60     => 'hace un momento',
            $diff < 3600   => 'hace ' . floor($diff / 60) . ' min',
            $diff < 86400  => 'hace ' . floor($diff / 3600) . ' h',
            $diff < 604800 => 'hace ' . floor($diff / 86400) . ' días',
            default        => dateEs(date('Y-m-d', strtotime($datetime))),
        };
    }
}
