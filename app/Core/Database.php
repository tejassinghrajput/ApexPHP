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
    /**
     * @var PDO|null The single instance of the PDO connection.
     */
    private static ?PDO $instance = null;

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
            // This is a temporary place to load config. In a full app,
            // this would be done in a central bootstrap file.
            Config::load('database.php');

            $defaultConnection = Config::get('database.default');
            $config = Config::get("database.connections.{$defaultConnection}");

            if (!$config) {
                // In a real framework, this would throw a specific framework exception.
                die("Database configuration for connection '{$defaultConnection}' not found.");
            }

            $dsn = self::buildDsn($config);
            $username = $config['username'] ?? null;
            $password = $config['password'] ?? null;
            $options = $config['options'] ?? [];

            try {
                self::$instance = new PDO($dsn, $username, $password, $options);

                // Common attributes for a robust connection
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            } catch (PDOException $e) {
                // This would be handled by a proper exception handler.
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Builds the DSN string from a configuration array.
     *
     * @param array $config The connection configuration.
     * @return string The DSN string.
     */
    private static function buildDsn(array $config): string
    {
        $driver = $config['driver'];

        switch ($driver) {
            case 'mysql':
                // Example: mysql:host=127.0.0.1;port=3306;dbname=apex;charset=utf8mb4
                return "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

            case 'pgsql':
                // Example: pgsql:host=127.0.0.1;port=5432;dbname=apex;
                return "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";

            case 'sqlite':
                $path = $config['database'];
                // For SQLite, ensure the directory exists before connecting.
                $dbDir = dirname($path);
                if (!is_dir($dbDir)) {
                    if (!mkdir($dbDir, 0775, true)) {
                        die("Failed to create SQLite database directory: {$dbDir}");
                    }
                }
                return "sqlite:" . $path;

            default:
                throw new InvalidArgumentException("Unsupported database driver: {$driver}");
        }
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
