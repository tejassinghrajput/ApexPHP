<?php

namespace App\Core;

use Exception;

class Router
{
    protected array $routes = [
        'GET' => [], 'POST' => [], 'PUT' => [], 'PATCH' => [], 'DELETE' => [],
    ];

    /**
     * @var Application The application instance.
     */
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function get(string $uri, array $action): void { $this->addRoute('GET', $uri, $action); }
    public function post(string $uri, array $action): void { $this->addRoute('POST', $uri, $action); }
    public function put(string $uri, array $action): void { $this->addRoute('PUT', $uri, $action); }
    public function delete(string $uri, array $action): void { $this->addRoute('DELETE', $uri, $action); }

    private function addRoute(string $method, string $uri, array $action): void
    {
        $this->routes[$method][$this->normalizeUri($uri)] = $action;
    }

    public function dispatch(Request $request)
    {
        $uri = $this->normalizeUri($request->uri());
        $method = $request->method();

        foreach ($this->routes[$method] as $route => $action) {
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[a-zA-Z0-9_]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$controller, $actionMethod] = $action;

                // Resolve the controller from the container
                $controllerInstance = $this->app->resolve($controller);

                return $controllerInstance->$actionMethod($request, ...array_values($params));
            }
        }

        throw new Exception("No route defined for URI: {$uri} with method: {$method}", 404);
    }

    private function normalizeUri(string $uri): string
    {
        return trim($uri, '/');
    }
}
