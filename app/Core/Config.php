<?php

namespace App\Core;

/**
 * Manages the application's configuration.
 *
 * This class loads configuration files from the /config directory and provides
 * a simple "dot" notation to access the values.
 */
class Config
{
    /**
     * @var array Holds all the loaded configuration items.
     */
    protected static array $items = [];

    /**
     * Load a configuration file from the /config directory.
     *
     * @param string $filename The name of the config file (e.g., 'database.php').
     * @return void
     */
    public static function load(string $filename): void
    {
        $key = basename($filename, '.php');
        $path = BASE_PATH . '/config/' . $filename;

        if (file_exists($path)) {
            self::$items[$key] = require $path;
        }
    }

    /**
     * Get a configuration value using "dot" notation.
     *
     * Example: Config::get('database.connections.mysql.host')
     *
     * @param string $key The configuration key (e.g., 'app.name').
     * @param mixed|null $default The default value to return if the key is not found.
     * @return mixed The configuration value.
     */
    public static function get(string $key, $default = null)
    {
        // If the key exists at the top level, return it.
        if (isset(self::$items[$key])) {
            return self::$items[$key];
        }

        // Navigate the array using dot notation for nested keys.
        $keys = explode('.', $key);
        $data = self::$items;

        foreach ($keys as $segment) {
            if (is_array($data) && isset($data[$segment])) {
                $data = $data[$segment];
            } else {
                // Key not found, return the default value.
                return $default;
            }
        }

        return $data;
    }
}
