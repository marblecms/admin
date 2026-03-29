<?php

namespace Marble\Admin;

use Marble\Admin\Contracts\FieldTypeInterface;

class FieldTypeRegistry
{
    /**
     * Registered field type instances, keyed by identifier.
     */
    protected array $types = [];

    /**
     * Register a field type.
     */
    public function register(FieldTypeInterface $fieldType): void
    {
        $this->types[$fieldType->identifier()] = $fieldType;
    }

    /**
     * Get a field type by identifier.
     */
    public function get(string $identifier): ?FieldTypeInterface
    {
        return $this->types[$identifier] ?? null;
    }

    /**
     * Get all registered field types.
     */
    public function all(): array
    {
        return $this->types;
    }

    /**
     * Check if a field type is registered.
     */
    public function has(string $identifier): bool
    {
        return isset($this->types[$identifier]);
    }

    /**
     * Register field type routes (called during boot).
     */
    public function registerRoutes(): void
    {
        foreach ($this->types as $fieldType) {
            $fieldType->registerRoutes();
        }
    }
}
