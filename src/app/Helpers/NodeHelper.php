<?php

namespace Marble\Admin\App\Helpers;

use Config;
use Marble\Admin\App\Models\Node;
use Marble\Admin\App\Models\NodeClass;
use Marble\Admin\App\Models\NodeClassGroup;
use Marble\Admin\App\Models\NodeClassAttribute;
use Marble\Admin\App\Models\NodeTranslation;
use Marble\Admin\App\Models\Language;


class NodeHelper
{
    public static function getSystemNode($identifier)
    {
        $systemNodes = Node::find(Config::get('app.system_nodes_id'));

        return $systemNodes->attributes->$identifier->processedValue[Config::get('app.locale')];
    }

    public static function createNode($parentId, $nodeClassId, $data)
    {
        $nodeClass = NodeClass::find($nodeClassId);

        $languages = Language::all();

        $node = new Node();
        $node->parentId = $parentId;
        $node->classId = $nodeClass->id;
        $node->save();

        foreach ($nodeClass->attributes as $classAttribute) {
            $nodeClassAttribute = new NodeClassAttribute();
            $nodeClassAttribute->nodeId = $node->id;
            $nodeClassAttribute->classAttributeId = $classAttribute->id;
            $nodeClassAttribute->save();

            foreach ($languages as $language) {
                $nodeTranslation = new NodeTranslation();
                $nodeTranslation->nodeId = $node->id;
                $nodeTranslation->languageId = $language->id;
                $nodeTranslation->nodeClassAttributeId = $nodeClassAttribute->id;

                $namedIdentifier = $classAttribute->namedIdentifier;
                if (isset($data[$namedIdentifier])) {
                    if (isset($data[$namedIdentifier][$language->id])) {
                        $nodeTranslation->value = $data[$namedIdentifier][$language->id];
                    } else {
                        $nodeTranslation->value = $data[$namedIdentifier];
                    }
                } else {
                    $nodeTranslation->value = $classAttribute->type->defaultValue;
                }

                $nodeTranslation->save();
            }
        }

        return $node;
    }

    public static function getGroupedNodeClasses()
    {
        $groupedNodeClasses = array();
        $nodeClasses = NodeClass::all();

        foreach ($nodeClasses as $nodeClass) {
            $nodeClassGroup = NodeClassGroup::find($nodeClass->groupId);

            if (!isset($groupedNodeClasses[$nodeClassGroup->id])) {
                $groupedNodeClasses[$nodeClassGroup->id] = (object) array(
                    'group' => $nodeClassGroup,
                    'items' => array(),
                );
            }

            $groupedNodeClasses[$nodeClassGroup->id]->items[] = $nodeClass;
        }

        ksort($groupedNodeClasses);

        return $groupedNodeClasses;
    }

    public static function __callStatic($name, $arguments)
    {
        list($parentId, $data) = $arguments;

        $name = substr($name, strlen('create'));

        $className = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $className = array_map('strtolower', $className);
        $className = implode('_', $className);

        $nodeClass = NodeClass::where(array('named_identifier' => $className))->get()->first();

        return self::createNode($parentId, $nodeClass->id, $data);
    }
}
