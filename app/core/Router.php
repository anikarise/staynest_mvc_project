<?php
class Router
{
    private string $defaultController = 'HomeController';
    private string $defaultMethod = 'index';

    public function dispatch(): void
    {
        $url = $this->parseUrl();

        $controllerName = !empty($url[0]) ? ucfirst($url[0]) . 'Controller' : $this->defaultController;
        $methodName = $url[1] ?? $this->defaultMethod;

        $controllerFile = APP_ROOT . '/app/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            $this->notFound();
            return;
        }

        require_once $controllerFile;
        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            $this->notFound();
            return;
        }

        $params = array_slice($url, 2);
        call_user_func_array([$controller, $methodName], $params);
    }

    private function parseUrl(): array
    {
        if (!isset($_GET['url'])) {
            return [];
        }

        $url = trim($_GET['url'], '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return explode('/', $url);
    }

    private function notFound(): void
    {
        http_response_code(404);
        require APP_ROOT . '/app/views/errors/404.php';
    }
}
