<?php

namespace Marble\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;

class NodeClass extends Model
{
    protected $table = 'node_classes';
    public $timestamps = false;

    public function getAttributesAttribute()
    {
        $classAttributes = ClassAttribute::where('classId', $this->id)->groupBy('namedIdentifier')->get()->sortBy(function ($nodeClass) {

            $primarySortKey = $nodeClass->groupId;

            if ($primarySortKey != 0) {
                $classAttributeGroup = ClassAttributeGroup::find($nodeClass->groupId);
                $primarySortKey = $classAttributeGroup->sortOrder;
            } else {
                $primarySortKey = '9999';
            }

            $secondarySortKey = $nodeClass->sortOrder;

            if ($secondarySortKey == -1) {
                $secondarySortKey = '00';
            }

            $sort = implode(array($primarySortKey, '_', $secondarySortKey));

            return $sort;
        });

        $attributes = array();

        foreach ($classAttributes as $classAttribute) {
            $attribute = Attribute::find($classAttribute->attributeId)->first();

            $classAttribute->type = $attribute;
            $attributes[] = $classAttribute;
        }

        return $attributes;
    }

    public function getAllowedChildClassesAttribute()
    {
        return unserialize($this->attributes['allowedChildClasses']);
    }

    public function setAllowedChildClassesAttribute($value)
    {
        $this->attributes['allowedChildClasses'] = serialize($value);
    }
}
