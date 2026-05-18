<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Gestion des sessions sécurisées
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;

        $secure   = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $lifetime = 0; // session cookie (expire à fermeture navigateur)

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name('PNDM_SESSION');
        session_start();

        // Régénération périodique de l'ID session (anti-fixation)
        if (!isset($_SESSION['_last_regen'])) {
            session_regenerate_id(true);
            $_SESSION['_last_regen'] = time();
        } elseif (time() - $_SESSION['_last_regen'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_last_regen'] = time();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 42000, '/');
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_last_regen'] = time();
    }

    /** Jeton CSRF — génère ou retourne le token de session */
    public static function csrfToken(): string
    {
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    /**
     * Alias de csrfToken() — appelé dans les vues JS
     * (Session::getCsrfToken())
     */
    public static function getCsrfToken(): string
    {
        return self::csrfToken();
    }

    public static function verifyCsrf(string $token): bool
    {
        $stored = $_SESSION['_csrf'] ?? '';
        return hash_equals($stored, $token);
    }
}
