<?php

namespace Marble\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Config;

class Node extends Model
{
    private $_attributes = false;
    private $_classAttribute = false;
    public $timestamps = false;

    public function getAttributesAttribute()
    {
        if ($this->_attributes) {
            return $this->_attributes;
        }

        $attributes = (object) array();

        $nodeClassAttributes = NodeClassAttribute::where(array(
            'nodeId' => $this->id,
        ))->get();

        foreach ($nodeClassAttributes as $nodeClassAttribute) {
            $attributes->{$nodeClassAttribute->classAttribute->namedIdentifier} = $nodeClassAttribute;
        }

        return $this->_attributes = $attributes;
    }

    public function getClassAttribute()
    {
        if (!$this->_classAttribute) {
            $this->_classAttribute = NodeClass::find($this->classId);
        }

        return $this->_classAttribute;
    }

    public function getNameAttribute()
    {
        $attributes = $this->getAttributesAttribute();

        return $attributes->name->value[Config::get('app.locale')];
    }
    
    public function getSlugAttribute()
    {
        $attributes = $this->getAttributesAttribute();
        
        if( ! isset($attributes->slug) ){
            return false;
        }
        
        $slugs = $attributes->slug->value;
        
        foreach($slugs as $locale => &$slug){
            
            $slug = "/$slug";
            
            $this->getParentSlug($this->parentId, $locale, $slug);
            
            if( Config::get("app.uri_locale_prefix") ){
                $slug = "/$locale" . $slug;
            }
            
        }
        
        
        return $slugs;
    }
    
    private function getParentSlug($parentId, $locale, &$slug)
    {
        $parentNode = Node::find($parentId);
        
        if( ! $parentNode ){
            return;
        }
        
        $attributes = $parentNode->getAttributesAttribute();
        
        if( ! isset($attributes->slug) ){
            return;
        }
        
        $slug = "/" . $attributes->slug->value[$locale] . $slug;
        
        $this->getParentSlug($parentNode->parentId, $locale, $slug);
    }
}
