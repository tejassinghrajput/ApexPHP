<?php

namespace App\Core;

use Closure;
use ReflectionClass;
use Exception;

/**
 * A simple but powerful Inversion of Control (IoC) / Dependency Injection (DI) container.
 */
class Container
{
    /**
     * @var array The container's bindings.
     */
    protected array $bindings = [];

    /**
     * @var array The container's shared instances.
     */
    protected array $instances = [];

    /**
     * Register a binding with the container.
     *
     * @param string $key The abstract key (e.g., 'database').
     * @param Closure $resolver A function that returns an instance of the service.
     * @param bool $singleton Whether the binding should be a singleton.
     */
    public function bind(string $key, Closure $resolver, bool $singleton = false): void
    {
        $this->bindings[$key] = compact('resolver', 'singleton');
    }

    /**
     * Register a shared binding in the container.
     *
     * @param string $key
     * @param Closure $resolver
     */
    public function singleton(string $key, Closure $resolver): void
    {
        $this->bind($key, $resolver, true);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string $key The abstract key to resolve.
     * @return mixed The resolved instance.
     * @throws Exception
     */
    public function resolve(string $key)
    {
        // If an instance of the type is already resolved as a singleton, return it.
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        // If the type is not bound, we will try to auto-resolve it.
        if (!isset($this->bindings[$key])) {
            if (class_exists($key)) {
                return $this->autoResolve($key);
            }
            throw new Exception("No binding found for '{$key}' in the container.");
        }

        $binding = $this->bindings[$key];
        $instance = $binding['resolver']($this);

        // If the binding is a singleton, we will cache the instance.
        if ($binding['singleton']) {
            $this->instances[$key] = $instance;
        }

        return $instance;
    }

    /**
     * Auto-resolve a class by inspecting its constructor's dependencies.
     *
     * @param string $className
     * @return object
     * @throws Exception
     */
    protected function autoResolve(string $className): object
    {
        $reflector = new ReflectionClass($className);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$className} is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // If there is no constructor, we can just instantiate the class.
        if (is_null($constructor)) {
            return new $className;
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            // If the dependency is not a class, we cannot resolve it.
            if (!$parameter->getType() || $parameter->getType()->isBuiltin()) {
                throw new Exception("Cannot auto-resolve un-typed or built-in type for parameter '{$parameter->getName()}' in class {$className}.");
            }
            $dependencyClass = $parameter->getType()->getName();
            $dependencies[] = $this->resolve($dependencyClass);
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
