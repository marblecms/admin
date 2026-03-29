<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Site extends Model
{
    protected $table = 'sites';

    protected $fillable = ['name', 'domain', 'root_item_id', 'default_language_id', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function rootItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'root_item_id');
    }

    public function defaultLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'default_language_id');
    }

    /**
     * Resolve the site for the current HTTP request host.
     */
    public static function current(): ?static
    {
        $host = request()->getHost();
        return static::where('domain', $host)->where('active', true)->first();
    }
}
