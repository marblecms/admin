<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'name'];
}
