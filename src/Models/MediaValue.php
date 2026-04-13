<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaValue extends Model
{
    protected $fillable = ['media_id', 'blueprint_field_id', 'language_id', 'value'];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function blueprintField(): BelongsTo
    {
        return $this->belongsTo(BlueprintField::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
