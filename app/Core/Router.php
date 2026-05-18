<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Routeur HTTP simple — support GET, POST, params dynamiques {slug}
 */
class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $uri, string $method): void
    {
        // Normalisation de l'URI
        $uri = '/' . ltrim(parse_url($uri, PHP_URL_PATH) ?? '', '/');
        $uri = rtrim($uri, '/') ?: '/';

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $pattern => $handler) {
            $params = $this->match($pattern, $uri);
            if ($params !== null) {
                $this->call($handler, $params);
                return;
            }
        }

        throw new NotFoundException("Page introuvable : {$uri}");
    }

    private function match(string $pattern, string $uri): ?array
    {
        // Convertir {slug} → groupe de capture nommé
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $uri, $matches)) return null;

        $params = [];
        foreach ($matches as $key => $val) {
            if (is_string($key)) $params[$key] = $val;
        }
        return $params;
    }

    private function call(string $handler, array $params): void
    {
        [$class, $method] = explode('@', $handler, 2);
        $fullClass = "App\\Controllers\\{$class}";
        if (!class_exists($fullClass)) {
            throw new \RuntimeException("Contrôleur introuvable : {$fullClass}");
        }
        $controller = new $fullClass();
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Méthode introuvable : {$fullClass}::{$method}");
        }
        $controller->$method($params);
    }
}

class NotFoundException  extends \RuntimeException {}
class ForbiddenException extends \RuntimeException {}
