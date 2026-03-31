<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class MarbleSetting extends Model
{
    protected $table = 'settings';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::find($key);
        return $row ? $row->value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function allKeyed(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }
}
