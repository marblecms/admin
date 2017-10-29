<?php

namespace Marble\Admin\App\Attributes;

use Marble\Admin\App\Models\Node;
use Marble\Admin\App\Models\NodeTranslation;

class ObjectRelationList extends Attribute
{
    protected $viewPrefix = 'admin';
    protected $javascripts = array('object-relation-list-edit.js');

    public function getValues($values)
    {
        foreach ($values as $languageId => &$value) {
            foreach ($value as &$node) {
                $node = Node::find($node);
            }
        }

        return $values;
    }

    public function ajaxEndpoint($request, $languageId)
    {
        $translation = NodeTranslation::where(
            array(
                'nodeClassAttributeId' => $this->attribute->id,
                'languageId' => $languageId,
            )
        )->get()->first();

        $nodes = unserialize($translation->value);

        if ($request->input('method') === 'sort') {
            $sortOrder = $request->input('sortOrder');
            $sortedNodes = array();

            foreach ($sortOrder as $index) {
                $sortedNodes[] = $nodes[$index];
            }

            $nodes = $sortedNodes;
        }

        $translation->value = serialize($nodes);
        $translation->save();

        die;
    }
}
