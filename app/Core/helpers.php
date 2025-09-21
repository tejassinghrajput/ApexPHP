<?php

/**
 * Core Helper Functions
 *
 * This file contains globally available helper functions.
 */

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable or returns a default value.
     *
     * The function checks for the variable in $_ENV, $_SERVER, and through getenv().
     * This provides a convenient and secure way to access environment-specific
     * settings stored in the .env file.
     *
     * @param  string  $key The name of the environment variable.
     * @param  mixed   $default The default value to return if the variable is not found.
     * @return mixed The value of the environment variable or the default value.
     */
    function env($key, $default = null)
    {
        // The phpdotenv library loads environment variables into $_ENV and $_SERVER
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        // Handle boolean-like strings
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        // Remove quotes if the value is quoted
        if (strlen($value) > 1 && $value[0] === '"' && $value[strlen($value) - 1] === '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
