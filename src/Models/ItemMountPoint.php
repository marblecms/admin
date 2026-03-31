<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemMountPoint extends Model
{
    protected $fillable = ['item_id', 'mount_parent_id', 'sort_order'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function mountParent(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'mount_parent_id');
    }
}
