<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public static function active()
    {
        return static::where('is_active', true)->get();
    }
}
