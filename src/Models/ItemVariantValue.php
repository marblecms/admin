<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemVariantValue extends Model
{
    public $timestamps = false;

    protected $fillable = ['variant_id', 'blueprint_field_id', 'language_id', 'value'];

    public function variant(): BelongsTo { return $this->belongsTo(ItemVariant::class, 'variant_id'); }
}
