<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redirect extends Model
{
    protected $table = 'redirects';

    protected $fillable = [
        'source_path',
        'target_path',
        'target_item_id',
        'status_code',
        'active',
        'hits',
    ];

    protected $casts = [
        'active'      => 'boolean',
        'status_code' => 'integer',
        'hits'        => 'integer',
    ];

    public function targetItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'target_item_id');
    }

    /**
     * Resolve the final target URL (item URL or raw path).
     */
    public function resolvedTarget(): string
    {
        if ($this->target_item_id && $this->targetItem) {
            return $this->targetItem->slug() ?: '/';
        }

        return $this->target_path ?? '/';
    }
}
