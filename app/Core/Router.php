<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, string $handler): void
    {
        $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', trim($path, '/'));

        $this->routes[$method][] = [
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = trim((string) parse_url($uri, PHP_URL_PATH), '/');

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches);
                $this->call($route['handler'], $matches);

                return;
            }
        }

        http_response_code(404);
        require dirname(__DIR__) . '/Views/errors/404.php';
    }

    private function call(string $handler, array $params): void
    {
        [$controllerName, $method] = explode('@', $handler);
        $controllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Contrôleur introuvable : {$controllerClass}");
        }

        $controller = new $controllerClass();
        $controller->$method(...$params);
    }
}
