<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentBundleItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['bundle_id', 'item_id', 'pre_publish_status', 'pre_publish_revision_id'];

    public function bundle(): BelongsTo { return $this->belongsTo(ContentBundle::class); }
    public function item(): BelongsTo   { return $this->belongsTo(Item::class); }
}
