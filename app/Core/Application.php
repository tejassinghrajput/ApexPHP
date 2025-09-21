<?php

namespace App\Core;

/**
 * The main Application class.
 *
 * This class extends the service container and acts as the central hub for the
 * entire application. It is instantiated in the bootstrap process.
 */
class Application extends Container
{
    /**
     * @var Application The single instance of the application.
     */
    protected static ?self $instance = null;

    /**
     * The base path of the application installation.
     * @var string
     */
    public string $basePath;

    /**
     * Create a new application instance.
     *
     * @param string|null $basePath
     */
    public function __construct(?string $basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }

        static::setInstance($this);

        // We can register some initial bindings here if needed,
        // but most will be done in the bootstrap file.
    }

    /**
     * Set the base path for the application.
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '\/');
    }

    /**
     * Set the shared instance of the application.
     */
    protected static function setInstance(self $application): void
    {
        static::$instance = $application;
    }

    /**
     * Get the shared instance of the application.
     */
    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            // This is a fallback in case the app is not instantiated properly.
            static::$instance = new static;
        }
        return static::$instance;
    }
}
