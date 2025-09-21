<?php

/**
 * ApexPHP Framework
 *
 * This file acts as the application's "kernel" for all web requests.
 */

// 1. Bootstrap The Application
// This loads the autoloader, environment variables, and configuration.
require_once __DIR__.'/../bootstrap/app.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\JsonResponse;

// 2. Load all route files
// We create a single router instance and then load all the route
// definition files from the modules, which will register their routes
// with this instance.
$router = new Router();
foreach (glob(BASE_PATH . '/app/Modules/*/*.routes.php') as $routesFile) {
    // The $router variable is made available to the included file.
    require $routesFile;
}

// 3. Dispatch the request and handle the response
try {
    $request = new Request();
    $response = $router->dispatch($request);

    // If the controller returns a JsonResponse object, send it.
    if ($response instanceof JsonResponse) {
        $response->send();
    } else {
        // Handle other potential response types in the future (e.g., HTML views).
        // For now, we just echo whatever was returned.
        echo $response;
    }
} catch (\Exception $e) {
    // A simple, unified exception handler for the application.
    // In a real app, this would be much more sophisticated, with logging,
    // different error pages for different environments, etc.
    $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;

    (new JsonResponse(
        [
            'error' => true,
            'message' => $e->getMessage(),
            'file' => env('APP_DEBUG', false) ? $e->getFile() : null,
            'line' => env('APP_DEBUG', false) ? $e->getLine() : null,
        ],
        $statusCode
    ))->send();
}
