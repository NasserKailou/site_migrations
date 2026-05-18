<?php
declare(strict_types=1);
namespace App\Core;

/** Objet réponse HTTP */
class Response
{
    public static function redirect(string $url, int $code = 302): never
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    public static function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: X-Api-Key, Content-Type');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function download(string $content, string $filename, string $mime = 'text/csv'): never
    {
        header("Content-Type: {$mime}; charset=utf-8");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        echo $content;
        exit;
    }

    public static function notFound(string $msg = 'Not found'): never
    {
        http_response_code(404);
        View::render('errors/404', ['message' => $msg]);
        exit;
    }

    public static function abort(int $code, string $msg = ''): never
    {
        http_response_code($code);
        View::render("errors/{$code}", ['message' => $msg]);
        exit;
    }
}
