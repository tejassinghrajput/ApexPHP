<?php

namespace App\Core;

use Exception;

/**
 * The core Router for the application.
 */
class Router
{
    protected array $routes = [
        'GET' => [], 'POST' => [], 'PUT' => [], 'PATCH' => [], 'DELETE' => [],
    ];

    public static function load(string $file): self
    {
        $router = new static;
        require $file;
        return $router;
    }

    public function get(string $uri, array $action): void { $this->addRoute('GET', $uri, $action); }
    public function post(string $uri, array $action): void { $this->addRoute('POST', $uri, $action); }
    public function put(string $uri, array $action): void { $this->addRoute('PUT', $uri, $action); }
    public function delete(string $uri, array $action): void { $this->addRoute('DELETE', $uri, $action); }

    private function addRoute(string $method, string $uri, array $action): void
    {
        $this->routes[$method][$this->normalizeUri($uri)] = $action;
    }

    /**
     * Dispatch the request to the appropriate controller action.
     */
    public function dispatch(Request $request)
    {
        $uri = $this->normalizeUri($request->uri());
        $method = $request->method();

        foreach ($this->routes[$method] as $route => $action) {
            // Convert route with params like {id} to a regex
            // This pattern matches named parameters like {id} or {name}
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[a-zA-Z0-9_]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                // Filter out numeric keys from matches to get only named params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$controller, $actionMethod] = $action;

                if (class_exists($controller) && method_exists($controller, $actionMethod)) {
                    $controllerInstance = new $controller();
                    // Pass the request and the route parameters to the controller method
                    return $controllerInstance->$actionMethod($request, ...array_values($params));
                }

                throw new Exception("Action {$actionMethod} not found on controller {$controller}.");
            }
        }

        throw new Exception("No route defined for URI: {$uri} with method: {$method}", 404);
    }

    private function normalizeUri(string $uri): string
    {
        return trim($uri, '/');
    }
}
