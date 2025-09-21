<?php

namespace App\Core;

use App\Core\Contracts\RepositoryInterface;
use App\Core\ApexORM\Model;
use Exception;

/**
 * A generic base repository with all the common data access logic.
 * Module-specific repositories will extend this class.
 */
abstract class Repository implements RepositoryInterface
{
    /**
     * The model class name for the repository.
     * Must be overridden in the child class.
     * e.g., return \App\Modules\Payments\PaymentsModel::class;
     *
     * @return string
     */
    abstract public function model(): string;

    /**
     * @inheritdoc
     */
    public function all(): array
    {
        return $this->model()::all();
    }

    /**
     * @inheritdoc
     */
    public function find(int $id): ?Model
    {
        return $this->model()::find($id);
    }

    /**
     * @inheritdoc
     */
    public function create(array $data): Model
    {
        $class = $this->model();
        $instance = new $class($data);
        $instance->save();
        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function update(int $id, array $data): ?Model
    {
        $instance = $this->find($id);
        if ($instance) {
            $instance->fill($data);
            $instance->save();
            return $instance;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id): bool
    {
        $instance = $this->find($id);
        if ($instance) {
            return $instance->delete();
        }
        return false;
    }
}
