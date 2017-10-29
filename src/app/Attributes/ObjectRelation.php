<?php

namespace Marble\Admin\App\Attributes;

use Marble\Admin\App\Models\Node;

class ObjectRelation extends Attribute
{
    protected $viewPrefix = 'admin';
    protected $javascripts = array('object-relation-edit.js');

    public function getValues($values)
    {
        foreach ($values as $languageId => &$value) {
            $value = Node::find($value);
        }

        return $values;
    }
}
