<?php

namespace App\Core;

use PDO;
use PDOException;
use InvalidArgumentException;

class Database
{
    public PDO $pdo;

    public function __construct(array $config)
    {
        $dsn = $this->buildDsn($config);
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;
        $options = $config['options'] ?? [];

        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    private function buildDsn(array $config): string
    {
        $driver = $config['driver'] ?? '';
        switch ($driver) {
            case 'mysql':
                return "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            case 'pgsql':
                return "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
            case 'sqlite':
                $path = $config['database'];
                // If the path is empty/null, use the default path.
                if (empty($path)) {
                    $path = Application::getInstance()->basePath . '/database/main.db';
                }

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
}
