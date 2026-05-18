<?php
declare(strict_types=1);

if (!function_exists('esc')) {
    /** Échappe le HTML — alias de View::e() */
    function esc(mixed $val): string {
        return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return \App\Core\View::url($path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return \App\Core\View::asset($path);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        $token = \App\Core\Session::csrfToken();
        return '<input type="hidden" name="_csrf" value="' . esc($token) . '">';
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $value = null): mixed {
        return \App\Core\Session::flash($key, $value);
    }
}

if (!function_exists('slugify')) {
    function slugify(string $str): string {
        $str = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $str);
        if ($str === false) {
            $str = strtolower($str);
        }
        $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
        $str = preg_replace('/[\s-]+/', '-', trim($str));
        return trim($str, '-');
    }
}

if (!function_exists('format_number')) {
    function format_number(mixed $n, int $decimals = 0): string {
        if ($n === null || $n === '') return '—';
        return number_format((float)$n, $decimals, ',', ' ');
    }
}

if (!function_exists('ago')) {
    function ago(string $date): string {
        $diff = time() - strtotime($date);
        return match(true) {
            $diff < 60    => 'à l\'instant',
            $diff < 3600  => floor($diff/60)   . ' min',
            $diff < 86400 => floor($diff/3600)  . ' h',
            $diff < 2592000 => floor($diff/86400). ' j',
            default => date('d/m/Y', strtotime($date))
        };
    }
}

if (!function_exists('date_fr')) {
    function date_fr(string $date): string {
        if (!$date) return '—';
        $mois = ['','janvier','février','mars','avril','mai','juin',
                 'juillet','août','septembre','octobre','novembre','décembre'];
        $t = strtotime($date);
        if (!$t) return $date;
        return date('j', $t) . ' ' . $mois[(int)date('n', $t)] . ' ' . date('Y', $t);
    }
}

if (!function_exists('json_safe')) {
    function json_safe(mixed $data): string {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP);
    }
}
