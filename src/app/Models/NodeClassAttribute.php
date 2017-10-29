<?php

namespace Marble\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Config;
use Cache;

class NodeClassAttribute extends Model
{
    protected $table = 'node_class_attribute';
    public $timestamps = false;

    private $_class = false;
    private $_classAttribute = false;
    private $_value = false;

    public function getClassAttributeAttribute()
    {
        if (!$this->_classAttribute) {
            $this->_classAttribute = ClassAttribute::find($this->classAttributeId);
        }

        return $this->_classAttribute;
    }

    public function getClassAttribute()
    {
        if ($this->_class) {
            return $this->_class;
        }

        $attribute = Attribute::find($this->classAttribute->attributeId);

        $classNameParts = explode('_', $attribute->namedIdentifier);
        $className = '\App\Attributes\\';

        foreach ($classNameParts as $classNamePart) {
            $className .= ucfirst($classNamePart);
        }

        return $this->_class = new $className($this, $this->classAttribute);
    }

    public function getValueAttribute()
    {
        if ($this->_value) {
            return $this->_value;
        }

        $nodeValues = array();
        
        $languages = Cache::rememberForever('languages', function () {
            return Language::all();
        });

        $nodeTranslations = NodeTranslation::where(array(
            'nodeId' => $this->nodeId,
            'nodeClassAttributeId' => $this->id,
        ))->get();

        foreach ($nodeTranslations as $nodeTranslation) {
            $nodeValues[$nodeTranslation->languageId] = $nodeTranslation ? $nodeTranslation->value : '';
        }

        if (!$this->classAttribute->translate) {
            $localeId = Config::get('app.locale');

            foreach ($languages as $language) {
                if ($language->id != $localeId) {
                    $nodeValues[$language->id] = $nodeValues[$localeId];
                }
            }
        }
        
        $attribute = Cache::rememberForever('attribute_'.$this->classAttribute->attributeId, function () {
            return $this->classAttribute->type;
        });

        if ($attribute->serializedValue) {
            foreach ($nodeValues as &$nodeValue) {
                $nodeValue = unserialize($nodeValue);
            }
        }

        return $this->_value = $nodeValues;
    }

    public function getProcessedValueAttribute()
    {
        $nodeValues = $this->value;

        if (method_exists($this->class, 'getValues')) {
            $nodeValues = $this->class->getValues($nodeValues);
        }

        return $nodeValues;
    }
}
