<?php

namespace App\Core;

use PDO;
use PDOException;
use App\Core\Config;
use InvalidArgumentException;

/**
 * Manages the database connection using the application's configuration.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    /**
     * Gets the single instance of the database connection.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            Config::load('database.php');
            $defaultConnection = Config::get('database.default');
            $config = Config::get("database.connections.{$defaultConnection}");

            if (!$config) {
                throw new InvalidArgumentException("Database configuration for connection '{$defaultConnection}' not found.");
            }

            $dsn = self::buildDsn($config);
            $username = $config['username'] ?? null;
            $password = $config['password'] ?? null;
            $options = $config['options'] ?? [];

            try {
                self::$instance = new PDO($dsn, $username, $password, $options);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                // Re-throw the exception to be handled by the application's error handler.
                throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode(), $e);
            }
        }
        return self::$instance;
    }

    /**
     * Builds the DSN string from a configuration array.
     */
    private static function buildDsn(array $config): string
    {
        $driver = $config['driver'];
        switch ($driver) {
            case 'mysql':
                return "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            case 'pgsql':
                return "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
            case 'sqlite':
                $path = $config['database'];
                $dbDir = dirname($path);
                if (!is_dir($dbDir)) {
                    if (!mkdir($dbDir, 0775, true)) {
                        throw new \RuntimeException("Failed to create SQLite database directory: {$dbDir}");
                    }
                }
                return "sqlite:" . $path;
            default:
                throw new InvalidArgumentException("Unsupported database driver: {$driver}");
        }
    }

    private function __clone() {}
    public function __wakeup() {}
}
