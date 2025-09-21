<?php

/**
 * ApexPHP Framework
 */

// 1. Register The Composer Auto Loader
require_once __DIR__.'/../vendor/autoload.php';

// 2. Bootstrap The Application
$app = require_once __DIR__.'/../bootstrap/app.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\JsonResponse;

// 3. Dispatch the request and handle the response
try {
    $request = $app->resolve(Request::class);
    $router = $app->resolve(Router::class);
    $response = $router->dispatch($request);

    if ($response instanceof JsonResponse) {
        $response->send();
    } else {
        echo $response;
    }
} catch (\Exception $e) {
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
