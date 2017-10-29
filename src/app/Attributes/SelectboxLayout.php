<?php

namespace Marble\Admin\App\Attributes;

class SelectboxLayout extends Attribute
{
    protected $viewPrefix = 'admin';
    
    public function getValues($values)
    {
        foreach ($values as $languageId => &$value) {
            if( $value ){
                list($name) = explode(".", $value);
                $value = "layouts.$name";
            }
        }
        
        return $values;
    }
}
