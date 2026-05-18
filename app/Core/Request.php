<?php
declare(strict_types=1);
namespace App\Core;

/** Objet requête HTTP */
class Request
{
    /**
     * Retourne l'URI de la requête, en strippant le base path
     * si l'application est installée dans un sous-dossier XAMPP.
     *
     * Détecte le base path via 2 méthodes (ordre de priorité) :
     *  1. APP_URL dans .env : parse le path (ex: /site_migrations/public)
     *  2. Auto-détection via SCRIPT_NAME (fonctionne sans config .env)
     *     SCRIPT_NAME = /site_migrations/public/index.php
     *     → base      = /site_migrations/public
     *
     * Exemples :
     *   XAMPP : REQUEST_URI = /site_migrations/public/admin
     *           SCRIPT_NAME = /site_migrations/public/index.php
     *           → uri retournée = /admin
     *
     *   Root  : REQUEST_URI = /admin
     *           SCRIPT_NAME = /index.php
     *           → uri retournée = /admin
     */
    public static function uri(): string
    {
        $raw = $_SERVER['REQUEST_URI'] ?? '/';

        // Supprimer la query string pour le routing
        $path = parse_url($raw, PHP_URL_PATH) ?? '/';

        // ── Méthode 1 : base path depuis APP_URL dans .env ───────────
        $appUrl = \App\Core\Config::get('app.url', '');
        if ($appUrl) {
            $urlPath = rtrim(parse_url($appUrl, PHP_URL_PATH) ?? '', '/');
            if ($urlPath !== '' && str_starts_with($path, $urlPath)) {
                return self::normPath(substr($path, strlen($urlPath)));
            }
        }

        // ── Méthode 2 : auto-détection via SCRIPT_NAME ───────────────
        // SCRIPT_NAME = /site_migrations/public/index.php
        // On retire /index.php pour obtenir le base path
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath   = rtrim(dirname($scriptName), '/');
        // dirname('/index.php') = '/' → on ignore
        if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath)) {
            return self::normPath(substr($path, strlen($basePath)));
        }

        return self::normPath($path);
    }

    /** Normalise un chemin : commence par /, pas de trailing slash */
    private static function normPath(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return ($path !== '/') ? rtrim($path, '/') : '/';
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
