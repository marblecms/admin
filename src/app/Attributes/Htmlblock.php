<?php

namespace Marble\Admin\App\Attributes;

use Marble\Admin\App\Models\Node;

class Htmlblock extends Attribute
{
    protected $configuration = true;
    protected $viewPrefix = 'admin';
    
    public function getValues($values)
    {
        foreach ($values as $languageId => &$value) {
            
            $value = preg_replace_callback('~\{% node-link\:([0-9]+) %\}~m', function($match) use($languageId){
                if( isset($match[1]) ){
                    $node = Node::find($match[1]);
                    
                    if( $node->slug ){
                        return $node->slug[$languageId];
                    }
                }
            }, $value);
        }
        
        return $values;
    }
}
