<?php

namespace App\Core\ApexORM;

/**
 * Abstract base Model class for the ApexORM.
 * All application models should extend this class.
 */
abstract class Model
{
    /**
     * The database table name associated with the model.
     * If not set, it will be inferred from the class name.
     * @var string
     */
    protected static string $tableName;

    /**
     * The schema definition for the database table.
     * Should be an associative array where keys are column names
     * and values are their SQL type definitions.
     * e.g., ['id' => 'INTEGER PRIMARY KEY AUTOINCREMENT', 'name' => 'TEXT NOT NULL']
     * @var array
     */
    protected static array $schema = [];

    /**
     * Get the table name for the model.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        if (!empty(static::$tableName)) {
            return static::$tableName;
        }
        // Automatically determine table name from model class name (e.g., "User" -> "users")
        $className = (new \ReflectionClass(static::class))->getShortName();
        return strtolower($className) . 's';
    }

    /**
     * Get the schema for the model.
     *
     * @return array
     */
    public static function getSchema(): array
    {
        return static::$schema;
    }

    // Future ORM methods like find(), save(), delete() will be added here.
}
