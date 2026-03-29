<?php

namespace Marble\Admin\FieldTypes;

use Marble\Admin\Models\Item;

class ObjectRelation extends BaseFieldType
{
    public function identifier(): string
    {
        return 'object_relation';
    }

    public function name(): string
    {
        return 'Object Relation';
    }

    public function process(mixed $raw, int $languageId): mixed
    {
        if (!$raw) {
            return null;
        }

        return Item::find((int) $raw);
    }

    public function configComponent(): ?string
    {
        return 'marble::field-types.object_relation-config';
    }

    public function scripts(): array
    {
        return ['object-relation-edit.js'];
    }

    public function onDeleteBehavior(array $configuration): string
    {
        return $configuration['on_delete'] ?? 'detach';
    }
}
