<?php

namespace App\Core\ApexORM;

use App\Core\Database;
use PDO;

/**
 * Abstract base Model class for the ApexORM.
 */
abstract class Model
{
    protected static string $tableName;
    protected static array $schema = [];
    protected static string $primaryKey = 'id';

    /**
     * Get the table name for the model.
     */
    public static function getTableName(): string
    {
        if (!empty(static::$tableName)) {
            return static::$tableName;
        }
        // "PaymentsModel" -> "Payments" -> "payments"
        $className = (new \ReflectionClass(static::class))->getShortName();
        $baseName = str_replace('Model', '', $className);
        // A real framework would use a more robust Inflector library.
        return strtolower($baseName);
    }

    /**
     * Get the schema for the model.
     */
    public static function getSchema(): array
    {
        return static::$schema;
    }

    /**
     * Retrieve all records from the database.
     */
    public static function all(): array
    {
        $pdo = Database::getInstance();
        $tableName = static::getTableName();
        $stmt = $pdo->query("SELECT * FROM `{$tableName}`");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find a record by its primary key.
     */
    public static function find(int $id): array|false
    {
        $pdo = Database::getInstance();
        $tableName = static::getTableName();
        $primaryKey = static::$primaryKey;
        $stmt = $pdo->prepare("SELECT * FROM `{$tableName}` WHERE `{$primaryKey}` = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
