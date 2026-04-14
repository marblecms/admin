<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarblePageview extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'item_id', 'language_id', 'site_id', 'path', 'referrer',
        'session_id', 'ip', 'country', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
