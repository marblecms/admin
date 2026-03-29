<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Site extends Model
{
    protected $table = 'sites';

    protected $fillable = ['name', 'domain', 'root_item_id', 'default_language_id', 'active', 'is_default'];

    protected $casts = ['active' => 'boolean', 'is_default' => 'boolean'];

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
     * Falls back to the site marked is_default = true if no domain matches.
     */
    public static function current(): ?static
    {
        $host = request()->getHost();

        return static::where('active', true)
                     ->where('domain', $host)
                     ->first()
            ?? static::where('active', true)
                     ->where('is_default', true)
                     ->first();
    }

    /**
     * Ensure only one site can be is_default at a time.
     */
    public function setAsDefault(): void
    {
        static::where('id', '!=', $this->id)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }
}
