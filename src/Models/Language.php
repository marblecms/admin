<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    private static ?Collection $allCache = null;

    /**
     * Return all languages, cached for the lifetime of the current request.
     */
    public static function allCached(): Collection
    {
        return static::$allCache ??= static::all();
    }

    /**
     * Flush the request-level cache (called by service provider on request termination,
     * or in tests between requests).
     */
    public static function flushCache(): void
    {
        static::$allCache = null;
    }

    public static function active(): \Illuminate\Database\Eloquent\Builder
    {
        return static::where('is_active', true);
    }
}
