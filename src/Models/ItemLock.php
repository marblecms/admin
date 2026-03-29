<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemLock extends Model
{
    public $timestamps = false;

    protected $fillable = ['item_id', 'user_id', 'locked_at'];

    protected $casts = ['locked_at' => 'datetime'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        $ttl = config('marble.lock_ttl', 300);
        return $this->locked_at->addSeconds($ttl)->isPast();
    }
}
