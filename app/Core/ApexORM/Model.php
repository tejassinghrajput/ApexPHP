<?php

namespace App\Core\ApexORM;

use App\Core\Database;
use PDO;
use JsonSerializable;

abstract class Model implements JsonSerializable
{
    protected static string $tableName;
    protected static string $primaryKey = 'id';
    protected array $attributes = [];
    protected array $fillable = [];
    protected static array $schema = []; // Re-declaring for clarity

    public function __construct(array $attributes = []) { $this->fill($attributes); }
    public function fill(array $attributes): void { foreach ($attributes as $key => $value) { if (in_array($key, $this->fillable)) { $this->setAttribute($key, $value); } } }
    public function setAttribute(string $key, $value): void { $this->attributes[$key] = $value; }
    public function getAttribute(string $key) { return $this->attributes[$key] ?? null; }
    public function __set(string $key, $value): void { $this->setAttribute($key, $value); }
    public function __get(string $key) { return $this->getAttribute($key); }

    public static function getTableName(): string
    {
        if (!empty(static::$tableName)) { return static::$tableName; }
        $className = (new \ReflectionClass(static::class))->getShortName();
        $baseName = str_replace('Model', '', $className);
        return strtolower($baseName);
    }

    /**
     * Get the schema for the model. (Re-added for migrator)
     */
    public static function getSchema(): array
    {
        // This is needed by the simple migrator.
        return static::$schema;
    }

    public static function all(): array
    {
        $pdo = Database::getInstance();
        $tableName = static::getTableName();
        $stmt = $pdo->query("SELECT * FROM `{$tableName}`");
        return array_map(fn($row) => new static($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function find(int $id): ?static
    {
        $pdo = Database::getInstance();
        $tableName = static::getTableName();
        $primaryKey = static::$primaryKey;
        $stmt = $pdo->prepare("SELECT * FROM `{$tableName}` WHERE `{$primaryKey}` = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) { return null; }
        $model = new static();
        $model->attributes = $result;
        return $model;
    }

    public function save(): bool
    {
        $primaryKey = static::$primaryKey;
        if (!empty($this->attributes[$primaryKey])) { return $this->performUpdate(); }
        else { return $this->performInsert(); }
    }

    public function delete(): bool
    {
        $pdo = Database::getInstance();
        $tableName = static::getTableName();
        $primaryKey = static::$primaryKey;
        $stmt = $pdo->prepare("DELETE FROM `{$tableName}` WHERE `{$primaryKey}` = ?");
        return $stmt->execute([$this->attributes[$primaryKey]]);
    }

    protected function performInsert(): bool
    {
        $pdo = Database::getInstance();
        $tableName = static::getTableName();
        $columns = array_keys($this->attributes);
        $placeholders = array_map(fn($c) => '?', $columns);
        $sql = sprintf('INSERT INTO `%s` (`%s`) VALUES (%s)', $tableName, implode('`, `', $columns), implode(', ', $placeholders));
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute(array_values($this->attributes));
        if ($success) { $this->setAttribute(static::$primaryKey, $pdo->lastInsertId()); }
        return $success;
    }

    protected function performUpdate(): bool
    {
        $pdo = Database::getInstance();
        $tableName = static::getTableName();
        $primaryKey = static::$primaryKey;
        $fields = [];
        $values = [];
        foreach ($this->attributes as $key => $value) {
            if ($key !== $primaryKey) { $fields[] = "`{$key}` = ?"; $values[] = $value; }
        }
        $values[] = $this->attributes[$primaryKey];
        $sql = sprintf('UPDATE `%s` SET %s WHERE `%s` = ?', $tableName, implode(', ', $fields), $primaryKey);
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function jsonSerialize(): mixed { return $this->attributes; }
}
