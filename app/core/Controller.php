<?php
class Controller
{
    protected function model(string $model): object
    {
        require_once APP_ROOT . '/app/models/' . $model . '.php';
        return new $model();
    }

    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die('View not found: ' . htmlspecialchars($view, ENT_QUOTES, 'UTF-8'));
        }

        require $viewFile;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . URL_ROOT . '/' . ltrim($path, '/'));
        exit;
    }

    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    protected function input(string $key, string $default = ''): string
    {
        return trim((string) ($_POST[$key] ?? $default));
    }

    protected function clean(string $value): string
    {
        return trim(strip_tags($value));
    }
}
