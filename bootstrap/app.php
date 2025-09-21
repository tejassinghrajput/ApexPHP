<?php

/**
 * --------------------------------------------------------------------------
 * Create The Application
 * --------------------------------------------------------------------------
 *
 * The first thing we will do is create a new ApexPHP application instance
 * which serves as the "glue" for all the components of Laravel, and is
 * the IoC container for the system binding all of the various parts.
 */

// 1. Define the base path of the application
define('BASE_PATH', dirname(__DIR__));

// 2. Bind the Composer autoloader
// This file makes all of our classes and vendor packages available.
require_once BASE_PATH . '/vendor/autoload.php';

// 3. Load environment variables from the .env file
// This allows us to have different settings for different environments
// without changing the codebase.
try {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // This is not a fatal error if the .env file is missing,
    // as the config files should have sensible defaults.
    // In a production environment, you would likely want to enforce
    // the presence of a .env file.
}

// 4. Load the application's configuration files
// We are loading them into our simple static Config class for now.
use App\Core\Config;

Config::load('database.php');
// In a more advanced setup, we would loop through all files in the
// config directory and load them automatically.

// 5. Return the application instance (or just true for now)
// In a full framework, this would return the Application object.
return true;
