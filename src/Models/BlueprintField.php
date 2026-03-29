<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Marble\Admin\Contracts\FieldTypeInterface;

class BlueprintField extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'identifier',
        'blueprint_id',
        'field_type_id',
        'blueprint_field_group_id',
        'sort_order',
        'configuration',
        'translatable',
        'locked',
        'validation_rules',
    ];

    protected $casts = [
        'configuration' => 'array',
        'translatable' => 'boolean',
        'locked' => 'boolean',
    ];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function fieldType(): BelongsTo
    {
        return $this->belongsTo(FieldType::class);
    }

    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(BlueprintFieldGroup::class, 'blueprint_field_group_id');
    }

    /**
     * Get the FieldType PHP class instance.
     */
    public function fieldTypeInstance(): FieldTypeInterface
    {
        return $this->fieldType->instance();
    }
}
