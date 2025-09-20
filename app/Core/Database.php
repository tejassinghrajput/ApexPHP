<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Manages the database connection using the Singleton pattern.
 */
class Database
{
    /**
     * @var PDO|null The single instance of the PDO connection.
     */
    private static ?PDO $instance = null;

    /**
     * The path to the SQLite database file.
     * The root is considered the project's base directory.
     * @var string
     */
    private static string $dbFile = __DIR__ . '/../../database/main.db';

    /**
     * The constructor is private to prevent direct instantiation.
     */
    private function __construct() {}

    /**
     * Gets the single instance of the database connection.
     *
     * @return PDO The PDO database handle.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dbDir = dirname(self::$dbFile);

            // Ensure the database directory exists.
            if (!is_dir($dbDir)) {
                if (!mkdir($dbDir, 0775, true)) {
                    // In a real framework, this would throw a specific framework exception.
                    die("Failed to create database directory: {$dbDir}");
                }
            }

            try {
                $dsn = 'sqlite:' . self::$dbFile;
                self::$instance = new PDO($dsn);

                // Set PDO to throw exceptions on error for better error handling.
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                // This would be handled by a proper exception handler in the full framework.
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * The clone method is private to prevent cloning of the instance.
     */
    private function __clone() {}

    /**
     * The wakeup method is private to prevent unserializing of the instance.
     */
    public function __wakeup() {}
}
