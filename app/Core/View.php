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

    /** Helper : URL asset avec cache-busting */
    public static function asset(string $path): string
    {
        $fullPath = PUB_ROOT . '/' . ltrim($path, '/');
        $ver = file_exists($fullPath) ? '?v=' . filemtime($fullPath) : '';
        $base = Config::get('app.url', '');
        return $base . '/assets/' . ltrim($path, '/') . $ver;
    }

    /** Helper : URL site */
    public static function url(string $path = ''): string
    {
        $base = rtrim(Config::get('app.url', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}
