<?php

/**
 * ApexPHP Framework
 *
 * This file is the single entry point for all HTTP requests to the application.
 */

// 1. Bootstrap The Application
// This file loads the autoloader, environment variables, and configuration.
require_once __DIR__.'/../bootstrap/app.php';


// 2. (Future) Handle the request
// This is where the router would take the incoming request URI and method,
// find the appropriate controller and action, and execute it.


// For now, a simple response to confirm the setup is working.
echo "<h1>Welcome to ApexPHP!</h1>";
echo "<p>The front controller at <code>public/index.php</code> is running correctly.</p>";
echo "<p>Application Environment: " . env('APP_ENV', 'not set') . "</p>";
echo "<p>Database Connection: " . env('DB_CONNECTION', 'not set') . "</p>";
