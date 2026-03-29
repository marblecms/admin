<?php

namespace Marble\Admin\FieldTypes;

class KeyValueStore extends BaseFieldType
{
    public function identifier(): string
    {
        return 'keyvalue_store';
    }

    public function name(): string
    {
        return 'Key/Value Store';
    }

    public function isStructured(): bool
    {
        return true;
    }

    public function defaultValue(): mixed
    {
        return [];
    }

    public function process(mixed $raw, int $languageId): mixed
    {
        if (!is_array($raw)) {
            return (object) [];
        }

        $result = [];
        foreach ($raw as $row) {
            if (isset($row['key'], $row['value'])) {
                $result[$row['key']] = $row['value'];
            }
        }

        return (object) $result;
    }
}
