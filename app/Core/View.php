<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Moteur de vues PHP — rendu de fichiers .php dans app/Views/
 */
class View
{
    private static ?string $currentLang = null;

    public static function render(string $view, array $data = [], bool $return = false): string
    {
        $file = APP_ROOT . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("Vue introuvable : {$view}");
        }

        // Variables disponibles dans la vue
        extract($data, EXTR_SKIP);
        $csrf  = Session::csrfToken();
        $flash = [
            'success' => Session::flash('success'),
            'error'   => Session::flash('error'),
            'info'    => Session::flash('info'),
        ];
        $lang  = self::getLang();
        $user  = Auth::user();

        ob_start();
        require $file;
        $content = ob_get_clean();

        if ($return) return $content;
        echo $content;
        return '';
    }

    /** Rend une vue dans un layout */
    public static function renderWithLayout(
        string $view,
        array  $data = [],
        string $layout = 'layouts/public'
    ): void {
        $data['content'] = self::render($view, $data, true);
        self::render($layout, $data);
    }

    /** Composant partiel (header, footer, etc.) */
    public static function component(string $name, array $data = []): void
    {
        self::render('components/' . $name, $data);
    }

    public static function getLang(): string
    {
        if (self::$currentLang) return self::$currentLang;
        $lang = Session::get('lang', 'fr');
        self::$currentLang = in_array($lang, ['fr','en']) ? $lang : 'fr';
        return self::$currentLang;
    }

    /** Helper : escape HTML */
    public static function e(mixed $val): string
    {
        return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Détecte dynamiquement l'URL de base depuis la requête HTTP courante.
     * Stratégie multi-couches pour fonctionner sur localhost, sandbox et production :
     *  1. HTTP_X_FORWARDED_PROTO  (proxy standard)
     *  2. HTTP_X_FORWARDED_SSL    (certains load balancers)
     *  3. HTTP_FRONT_END_HTTPS    (IIS ARR)
     *  4. HTTPS server var        (Apache/Nginx natif)
     *  5. Heuristique port        : si HTTP_HOST ne contient pas ':port'
     *                              ET que le port serveur n'est pas 80/8080,
     *                              on suppose HTTPS (proxy transparent sandbox)
     *  6. Fallback APP_URL .env   (CLI, cron)
     */
    public static function baseUrl(): string
    {
        static $base = null;
        if ($base !== null) return $base;

        // CLI / cron → fallback .env
        if (php_sapi_name() === 'cli' || empty($_SERVER['HTTP_HOST'])) {
            $base = rtrim(Config::get('app.url', 'http://localhost:8080'), '/');
            return $base;
        }

        // Détection du scheme
        $scheme = 'http';
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $scheme = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]));
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
            $scheme = 'https';
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] === 'on') {
            $scheme = 'https';
        } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $scheme = 'https';
        } elseif (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
            $scheme = 'https';
        } else {
            // Heuristique sandbox / reverse proxy transparent :
            // Si le HTTP_HOST ne contient pas de port explicite ET ce n'est pas localhost → HTTPS proxy
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];
            $hasExplicitPort = str_contains($host, ':');
            $isLocalhost = (
                str_contains($host, 'localhost') ||
                str_starts_with($host, '127.') ||
                str_starts_with($host, '::1')
            );
            if (!$hasExplicitPort && !$isLocalhost) {
                $scheme = 'https';
            }
        }

        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];
        $host = trim(explode(',', $host)[0]);
        $host = preg_replace('/:(80|443)$/', '', $host);

        // ── Détection du sous-dossier XAMPP ──────────────────────────
        // Si APP_URL est défini et contient un path (ex: http://localhost:8085/site_migrations/public)
        // on l'utilise directement — il a priorité sur la détection automatique
        $configUrl = Config::get('app.url', '');
        if ($configUrl && rtrim($configUrl, '/') !== $scheme . '://' . $host) {
            // APP_URL contient un sous-dossier → utiliser tel quel
            $base = rtrim($configUrl, '/');
            return $base;
        }

        $base = $scheme . '://' . $host;
        return $base;
    }


    /** Helper : URL asset avec cache-busting */
    public static function asset(string $path): string
    {
        $clean    = ltrim($path, '/');
        $fullPath = PUB_ROOT . '/' . $clean;
        $ver      = file_exists($fullPath) ? '?v=' . filemtime($fullPath) : '';
        return self::baseUrl() . '/assets/' . $clean . $ver;
    }

    /** Helper : URL site (chemin relatif à la racine) */
    public static function url(string $path = ''): string
    {
        return self::baseUrl() . '/' . ltrim($path, '/');
    }
}
