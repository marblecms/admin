<?php

namespace Marble\Admin\FieldTypes;

class Repeater extends BaseFieldType
{
    public function identifier(): string
    {
        return 'repeater';
    }

    public function name(): string
    {
        return 'Repeater';
    }

    public function isStructured(): bool
    {
        return true;
    }

    public function defaultValue(): mixed
    {
        return [];
    }

    public function configComponent(): ?string
    {
        return 'marble::field-types.repeater-config';
    }

    /**
     * Process stored value for frontend output.
     * Returns an array of row arrays keyed by sub-field identifier.
     */
    public function process(mixed $raw, int $languageId): mixed
    {
        if (!is_array($raw)) {
            return [];
        }

        return array_values($raw);
    }
}
