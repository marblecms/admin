<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemUrlAlias extends Model
{
    protected $fillable = ['item_id', 'language_id', 'alias'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
