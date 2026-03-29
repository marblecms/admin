<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlueprintFieldGroup extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'blueprint_id', 'sort_order'];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(BlueprintField::class)->orderBy('sort_order');
    }
}
