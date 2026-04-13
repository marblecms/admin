<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class CropPreset extends Model
{
    protected $fillable = ['name', 'label', 'width', 'height'];

    protected $casts = [
        'width'  => 'integer',
        'height' => 'integer',
    ];
}
