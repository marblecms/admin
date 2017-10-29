<?php

namespace Marble\Admin\App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class UserGroup extends Authenticatable
{
    public $timestamps = false;

    public function getAllowedClassesAttribute()
    {
        return unserialize($this->attributes['allowedClasses']);
    }

    public function setAllowedClassesAttribute($value)
    {
        $this->attributes['allowedClasses'] = serialize($value);
    }
}
