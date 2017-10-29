<?php

namespace Marble\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassAttribute extends Model
{
    public $timestamps = false;

    public function getTypeAttribute()
    {
        return Attribute::find($this->attributeId);
    }

    public function getClassAttribute()
    {
        $classNameParts = explode('_', $this->type->namedIdentifier);
        $className = '\Marble\Admin\App\Attributes\\';

        foreach ($classNameParts as $classNamePart) {
            $className .= ucfirst($classNamePart);
        }

        return new $className(null, $this);
    }

    public function getConfigurationAttribute()
    {
        if ($this->attributes['configuration']) {
            return unserialize($this->attributes['configuration']);
        }
    }

    public function setConfigurationAttribute($value)
    {
        $this->attributes['configuration'] = serialize($value);
    }
}
