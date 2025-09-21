<?php

namespace App\Core\ApexORM;

use App\Core\Application;
use App\Core\Database;
use PDO;
use JsonSerializable;

abstract class Model implements JsonSerializable
{
    // ... existing properties ...
    protected static string $tableName;
    protected static string $primaryKey = 'id';
    protected array $attributes = [];
    protected array $fillable = [];
    protected static array $schema = [];

    // ... existing __construct, fill, set/get, __set/__get ...
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

    public static function getSchema(): array
    {
        return static::$schema;
    }

    protected static function db(): PDO
    {
        // Resolve the database connection from the container
        return Application::getInstance()->resolve(Database::class)->pdo;
    }

    public static function all(): array
    {
        $stmt = self::db()->query("SELECT * FROM `" . static::getTableName() . "`");
        return array_map(fn($row) => new static($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function find(int $id): ?static
    {
        $stmt = self::db()->prepare("SELECT * FROM `" . static::getTableName() . "` WHERE `" . static::$primaryKey . "` = ?");
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
        if (!empty($this->attributes[$primaryKey])) {
            return $this->performUpdate();
        } else {
            return $this->performInsert();
        }
    }

    public function delete(): bool
    {
        $stmt = self::db()->prepare("DELETE FROM `" . static::getTableName() . "` WHERE `" . static::$primaryKey . "` = ?");
        return $stmt->execute([$this->attributes[static::$primaryKey]]);
    }

    protected function performInsert(): bool
    {
        $columns = array_keys($this->attributes);
        $placeholders = array_map(fn($c) => '?', $columns);
        $sql = sprintf('INSERT INTO `%s` (`%s`) VALUES (%s)', static::getTableName(), implode('`, `', $columns), implode(', ', $placeholders));
        $stmt = self::db()->prepare($sql);
        $success = $stmt->execute(array_values($this->attributes));
        if ($success) {
            $this->setAttribute(static::$primaryKey, self::db()->lastInsertId());
        }
        return $success;
    }

    protected function performUpdate(): bool
    {
        $primaryKey = static::$primaryKey;
        $fields = [];
        $values = [];
        foreach ($this->attributes as $key => $value) {
            if ($key !== $primaryKey) {
                $fields[] = "`{$key}` = ?";
                $values[] = $value;
            }
        }
        $values[] = $this->attributes[$primaryKey];
        $sql = sprintf('UPDATE `%s` SET %s WHERE `%s` = ?', static::getTableName(), implode(', ', $fields), $primaryKey);
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($values);
    }

    public function jsonSerialize(): mixed { return $this->attributes; }
}
