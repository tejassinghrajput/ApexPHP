<?php

namespace App\Core;

/**
 * Manages the application's configuration.
 */
class Config
{
    /**
     * @var array Holds all the loaded configuration items.
     */
    protected array $items = [];

    /**
     * @var string The base path to the application's config directory.
     */
    protected string $configPath;

    public function __construct(string $basePath)
    {
        $this->configPath = $basePath . '/config';
    }

    /**
     * Load a configuration file from the /config directory.
     */
    public function load(string $filename): void
    {
        $key = basename($filename, '.php');
        $path = $this->configPath . '/' . $filename;

        if (file_exists($path)) {
            $this->items[$key] = require $path;
        }
    }

    /**
     * Get a configuration value using "dot" notation.
     */
    public function get(string $key, $default = null)
    {
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        $keys = explode('.', $key);
        $data = $this->items;

        foreach ($keys as $segment) {
            if (is_array($data) && isset($data[$segment])) {
                $data = $data[$segment];
            } else {
                return $default;
            }
        }

        return $data;
    }
}
