<?php

use App\Core\Application;
use App\Core\Config;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;

/**
 * Create The Application Instance
 */
$app = new Application(
    dirname(__DIR__)
);

/**
 * Bind Core Services as Singletons
 */
$app->singleton(Request::class, fn() => new Request());

$app->singleton(Config::class, function(Application $app) {
    $config = new Config($app->basePath);
    $config->load('database.php');
    return $config;
});

$app->singleton(Database::class, function(Application $app) {
    $config = $app->resolve(Config::class);
    $connectionName = $config->get('database.default');
    $connectionConfig = $config->get("database.connections.{$connectionName}");
    return new Database($connectionConfig);
});

$app->singleton(Router::class, function(Application $app) {
    $router = new Router($app);
    foreach (glob($app->basePath . '/app/Modules/*/*.routes.php') as $routesFile) {
        require $routesFile;
    }
    return $router;
});

/**
 * Dynamically Bind Module Repositories
 */
foreach (glob($app->basePath . '/app/Modules/*') as $moduleDir) {
    if (is_dir($moduleDir)) {
        $moduleName = basename($moduleDir);
        $interface = "App\\Modules\\{$moduleName}\\{$moduleName}RepositoryInterface";
        $implementation = "App\\Modules\\{$moduleName}\\{$moduleName}Repository";

        if (interface_exists($interface) && class_exists($implementation)) {
            // Bind the interface to a resolver function that resolves the concrete class.
            $app->singleton($interface, fn(Application $app) => $app->resolve($implementation));
        }
    }
}


/**
 * Load Environment Variables
 */
try {
    $dotenv = Dotenv\Dotenv::createImmutable($app->basePath);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // The .env file is not mandatory.
}

/**
 * Return The Application Instance
 */
return $app;
