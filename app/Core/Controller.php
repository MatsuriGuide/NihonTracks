<?php

namespace App\Core;

class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = dirname(__DIR__) . "/Views/{$view}.php";

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Vue introuvable : {$view}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/main.php';
    }

    protected function redirect(string $path): void
    {
        header("Location: {$path}");
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
