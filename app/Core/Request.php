<?php
declare(strict_types=1);
namespace App\Core;

/** Objet requête HTTP */
class Request
{
    public static function uri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public static function method(): string
    {
        // Support _method override pour PUT/DELETE via formulaires HTML
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        }
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public static function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public static function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public static function isPost(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }

    public static function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public static function isJson(): bool
    {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        return str_contains($ct, 'application/json');
    }

    public static function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    /** Sanitise et retourne une valeur GET/POST */
    public static function input(string $key, mixed $default = null): mixed
    {
        $val = $_POST[$key] ?? $_GET[$key] ?? $default;
        if (is_string($val)) {
            return trim($val);
        }
        return $val;
    }

    public static function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }
}
