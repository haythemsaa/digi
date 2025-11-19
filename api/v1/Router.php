<?php

/**
 * API Router
 * Handles routing for REST API requests
 *
 * @author Pakiparc Team
 * @version 1.0
 */
class APIRouter
{
    private $routes = [];
    private $params = [];

    /**
     * Register GET route
     */
    public function get($uri, $handler, $middleware = [])
    {
        $this->addRoute('GET', $uri, $handler, $middleware);
    }

    /**
     * Register POST route
     */
    public function post($uri, $handler, $middleware = [])
    {
        $this->addRoute('POST', $uri, $handler, $middleware);
    }

    /**
     * Register PUT route
     */
    public function put($uri, $handler, $middleware = [])
    {
        $this->addRoute('PUT', $uri, $handler, $middleware);
    }

    /**
     * Register DELETE route
     */
    public function delete($uri, $handler, $middleware = [])
    {
        $this->addRoute('DELETE', $uri, $handler, $middleware);
    }

    /**
     * Add route to registry
     */
    private function addRoute($method, $uri, $handler, $middleware)
    {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    /**
     * Dispatch request to appropriate handler
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getRequestUri();

        // Find matching route
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route, $method, $uri)) {
                // Execute middleware
                foreach ($route['middleware'] as $middlewareName) {
                    $this->executeMiddleware($middlewareName);
                }

                // Execute handler
                $this->executeHandler($route['handler']);
                return;
            }
        }

        // No route found
        http_response_code(404);
        echo json_encode([
            'error' => true,
            'message' => 'Endpoint not found'
        ]);
    }

    /**
     * Match route against request
     */
    private function matchRoute($route, $method, $uri)
    {
        if ($route['method'] !== $method) {
            return false;
        }

        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route['uri']);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // Extract parameters
            preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route['uri'], $paramNames);

            for ($i = 0; $i < count($paramNames[1]); $i++) {
                $this->params[$paramNames[1][$i]] = $matches[$i + 1];
            }

            return true;
        }

        return false;
    }

    /**
     * Execute middleware
     */
    private function executeMiddleware($name)
    {
        switch ($name) {
            case 'auth':
                AuthMiddleware::handle();
                break;
            case 'rate_limit':
                RateLimitMiddleware::handle();
                break;
        }
    }

    /**
     * Execute route handler
     */
    private function executeHandler($handler)
    {
        list($controller, $method) = explode('@', $handler);

        $controllerFile = __DIR__ . '/controllers/' . $controller . '.php';

        if (!file_exists($controllerFile)) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => 'Controller not found'
            ]);
            return;
        }

        require_once $controllerFile;

        $controllerInstance = new $controller();

        if (!method_exists($controllerInstance, $method)) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => 'Method not found'
            ]);
            return;
        }

        // Call controller method with params
        call_user_func([$controllerInstance, $method], $this->params);
    }

    /**
     * Get request URI
     */
    private function getRequestUri()
    {
        $uri = $_SERVER['REQUEST_URI'];

        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove /api/v1 prefix
        $uri = str_replace('/api/v1', '', $uri);

        // Ensure leading slash
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        return $uri;
    }
}
