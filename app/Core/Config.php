<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Configuration centralisée — lecture du fichier .env
 */
class Config
{
    private static array $data = [];
    private static bool  $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) return;
        $envFile = PNDM_ROOT . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#')) continue;
                if (!str_contains($line, '=')) continue;
                [$key, $val] = explode('=', $line, 2);
                self::$data[trim($key)] = trim($val);
                $_ENV[trim($key)] = trim($val);
            }
        }
        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();
        // Support notation pointée: 'db.host' → DB_HOST
        $envKey = strtoupper(str_replace('.', '_', $key));
        return self::$data[$envKey] ?? $_ENV[$envKey] ?? $default;
    }

    public static function isDebug(): bool
    {
        return self::get('app.debug', false) === 'true' || self::get('app.debug', false) === true;
    }
}
