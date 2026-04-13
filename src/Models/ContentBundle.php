<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentBundle extends Model
{
    protected $fillable = ['name', 'description', 'status', 'created_by_user_id', 'published_at'];

    protected $casts = ['published_at' => 'datetime'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function bundleItems(): HasMany
    {
        return $this->hasMany(ContentBundleItem::class, 'bundle_id');
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'content_bundle_items', 'bundle_id', 'item_id')
            ->withPivot(['pre_publish_status', 'pre_publish_revision_id'])
            ->with('blueprint');
    }
}
