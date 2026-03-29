<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemValue extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'blueprint_field_id',
        'language_id',
        'value',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function blueprintField(): BelongsTo
    {
        return $this->belongsTo(BlueprintField::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Get the raw deserialized value.
     */
    public function raw(): mixed
    {
        $fieldType = $this->blueprintField->fieldTypeInstance();

        if ($fieldType->isStructured() && $this->value !== null) {
            return $fieldType->deserialize($this->value);
        }

        return $this->value;
    }

    /**
     * Get the processed value (e.g. ObjectRelation resolves to Item).
     */
    public function processed(): mixed
    {
        $fieldType = $this->blueprintField->fieldTypeInstance();

        return $fieldType->process($this->raw(), $this->language_id);
    }
}
