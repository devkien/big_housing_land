<?php

class Router
{
    private $routes = [];

    // $middleware can be null or a string like 'auth' or 'role:admin,super_admin'
    public function get($uri, $action, $middleware = null)
    {
        $this->routes['GET'][$this->normalizeUri($uri)] = ['action' => $action, 'middleware' => $middleware];
    }

    public function post($uri, $action, $middleware = null)
    {
        $this->routes['POST'][$this->normalizeUri($uri)] = ['action' => $action, 'middleware' => $middleware];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // Lấy URI sạch (không query string)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // ===== AUTO DETECT BASE PATH =====
        // Ví dụ:
        // SCRIPT_NAME = /Big_Housing_Land/public/index.php
        // basePath    = /Big_Housing_Land
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        $basePath = dirname(dirname($scriptName));
        $basePath = $basePath === '/' ? '' : $basePath;

        // Cắt basePath khỏi URI
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        // Bỏ index.php nếu có
        if ($uri === '/index.php') {
            $uri = '/';
        }

        // Chuẩn hóa URI
        $uri = $this->normalizeUri($uri);

        // ===== MATCH ROUTE =====
        if (!isset($this->routes[$method][$uri])) {
            http_response_code(404);
            echo '404 Not Found (Router)';
            return;
        }

        $route = $this->routes[$method][$uri];
        $actionStr = $route['action'];
        $middleware = $route['middleware'] ?? null;

        [$controller, $action] = explode('@', $actionStr);

        $controllerFile = __DIR__ . '/../app/Controllers/' . $controller . '.php';

        if (!file_exists($controllerFile)) {
            http_response_code(500);
            echo "Controller {$controller} not found";
            return;
        }

        require_once $controllerFile;

        // Run middleware if specified
        if ($middleware) {
            require_once __DIR__ . '/Middleware.php';
            if (!\Middleware::handle($middleware)) {
                // Middleware should handle redirects/exits; if it returns false, stop dispatch
                return;
            }
        }

        $controllerObject = new $controller();

        if (!method_exists($controllerObject, $action)) {
            http_response_code(500);
            echo "Method {$action} not found in {$controller}";
            return;
        }

        call_user_func([$controllerObject, $action]);
    }

    // ===== Chuẩn hóa URI =====
    private function normalizeUri($uri)
    {
        $uri = rtrim($uri, '/');
        return $uri === '' ? '/' : $uri;
    }
}
