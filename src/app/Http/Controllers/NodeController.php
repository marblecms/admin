<?php

namespace Marble\Admin\App\Http\Controllers;

use Marble\Admin\App\Models\Node;
use Marble\Admin\App\Models\NodeClassAttribute;
use Marble\Admin\App\Models\ClassAttributeGroup;
use Marble\Admin\App\Models\ClassAttribute;
use Marble\Admin\App\Models\UserGroup;
use Marble\Admin\App\Models\Language;
use Marble\Admin\App\Models\NodeTranslation;
use App\NodeHelper;
use Auth;
use Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NodeController extends Controller
{

    public function searchJSON(Request $request)
    {
        $query = $request->input('q');

        $searchResults = array();

        $nodeTranslations = NodeTranslation::where('value', 'LIKE', "%$query%")->groupBy('nodeId')->distinct()->get();

        foreach ($nodeTranslations as $nodeTranslation) {
            $node = Node::find($nodeTranslation->nodeId);

            $searchResults[] = (object) array(
                'id' => $node->id,
                'name' => $node->name,
                'parentId' => $node->parentId,
                'sortOrder' => $node->sortOrder,
                'class' => $node->class,
            );
        }

        return response()->json($searchResults);
    }

    public function ajaxAttribute(Request $request, $nodeClassAttributeId, $languageId)
    {
        $nodeClassAttribute = NodeClassAttribute::find($nodeClassAttributeId);

        return $nodeClassAttribute->class->ajaxEndpoint($request, $languageId);
    }

    public function edit($id, $isIframe = false)
    {
        $node = Node::find($id);
        $languages = Language::all();

        $data = array();

        $data['isIframe'] = $isIframe;

        $data['node'] = $node;
        $data['groupedNodeAttributes'] = array();

        foreach ($node->attributes as $nodeAttribute) {
            $groupId = $nodeAttribute->classAttribute->groupId;
            $classAttributeGroup = ClassAttributeGroup::find($groupId);
            $sortKey = $classAttributeGroup ? $classAttributeGroup->sortOrder : -1;

            if (!isset($data['groupedNodeAttributes'][$sortKey])) {
                $data['groupedNodeAttributes'][$sortKey] = (object) array(
                    'group' => $classAttributeGroup,
                    'items' => (object) array(),
                );
            }

            $data['groupedNodeAttributes'][$sortKey]->items->{$nodeAttribute->classAttribute->namedIdentifier} = $nodeAttribute;
        }

        ksort($data['groupedNodeAttributes']);

        $data['languages'] = $languages;

        if ($node->class->listChildren) {
            $data['childNodes'] = Node::where(array('parentId' => $id))->get()->sortBy(function ($node) {
                return $node->sortOrder;
            });
        }

        return view('admin::node.edit', $data);
    }

    public function sort(Request $request)
    {
        $nodes = $request->input('nodes');

        foreach ($nodes as $nodeId => $sortOrder) {
            $node = Node::find($nodeId);
            $node->sortOrder = $sortOrder;
            $node->save();
        }
        die;
    }

    public function delete($id)
    {
        $node = Node::find($id);
        $parentId = $node->parentId;

        $this->deleteNodeAndChildNodes($id);

        return redirect('/admin/node/edit/'.$parentId);
    }

    private function deleteNodeAndChildNodes($id)
    {
        Node::destroy($id);
        Cache::forget("node_class_name_$id");

        $userGroups = UserGroup::where(array('entryNodeId' => $id))->get();
        foreach ($userGroups as $userGroup) {
            $userGroup->entryNodeId = 0;
            $userGroup->save();
        }

        $nodeClassAttributes = NodeClassAttribute::where(array('nodeId' => $id))->get();
        foreach ($nodeClassAttributes as $nodeClassAttribute) {
            NodeClassAttribute::destroy($nodeClassAttribute->id);
        }

        $nodeTranslations = NodeTranslation::where(array('nodeId' => $id))->get();
        foreach ($nodeTranslations as $nodeTranslation) {
            NodeTranslation::destroy($nodeTranslation->id);
        }

        $childNodes = Node::where(array('parentId' => $id))->get();

        foreach ($childNodes as $childNode) {
            $this->deleteNodeAndChildNodes($childNode->id);
        }
    }

    public function addIframe()
    {
        $data = array();

        $data['groupedNodeClasses'] = NodeHelper::getGroupedNodeClasses();

        return view('admin::node.add_iframe', $data);
    }

    public function add($parentId)
    {
        $data = array();

        $data['parentNode'] = Node::find($parentId);
        $data['groupedNodeClasses'] = NodeHelper::getGroupedNodeClasses();

        return view('admin::node.add', $data);
    }

    public function create(Request $request, $parentId = null)
    {
        $isIframe = false;
        
        if (!$parentId) {
            $isIframe = true;
            $parentId = $request->input('parentId');
        }

        $node = NodeHelper::createNode($parentId, (int) $request->input('classId'), array(
            'name' => $request->input('name'),
        ));

        if ($isIframe) {
            return redirect('/admin/node/edit/'.$node->id.'/iframe');
        } else {
            return redirect('/admin/node/edit/'.$node->id);
        }
    }

    public function save(Request $request, $id, $isIframe = false)
    {
        $node = Node::find($id);

        Cache::forget('routes');

        $languages = Language::all();
        $attributeValues = $request->input('attributes');
        $attributes = $node->attributes;

        $nodeTranslations = NodeTranslation::where(array(
            'nodeId' => $id,
        ))->get();

        $nodeClassAttributes = NodeClassAttribute::where(array('nodeId' => $id))->get();

        foreach ($nodeClassAttributes as $nodeClassAttribute) {
            if ($nodeClassAttribute->classAttribute->locked) {
                continue;
            }

            if (!isset($attributeValues[$nodeClassAttribute->id])) {
                $attributeValues[$nodeClassAttribute->id] = array();

                foreach ($languages as $language) {
                    $attributeValues[$nodeClassAttribute->id][$language->id] = '';
                }
            }
        }

        foreach ($attributeValues as $nodeClassAttributeId => $values) {
            $nodeClassAttribute = NodeClassAttribute::find($nodeClassAttributeId);

            foreach ($values as $languageId => $value) {
                $nodeTranslation = NodeTranslation::where(array(
                    'nodeId' => $id,
                    'languageId' => $languageId,
                    'nodeClassAttributeId' => $nodeClassAttributeId,
                ))->get()->first();

                if (!$nodeTranslation) {
                    $nodeTranslation = new NodeTranslation();
                    $nodeTranslation->nodeId = $id;
                    $nodeTranslation->languageId = $languageId;
                    $nodeTranslation->nodeClassAttributeId = $nodeClassAttributeId;
                }

                if (method_exists($nodeClassAttribute->class, 'processValue')) {
                    $oldValue = $nodeTranslation->value;

                    if ($nodeClassAttribute->classAttribute->type->serializedValue) {
                        $oldValue = unserialize($oldValue);
                    }

                    $value = $nodeClassAttribute->class->processValue(
                        $oldValue,
                        $value,
                        $nodeClassAttribute,
                        $languageId
                    );
                }

                if ($nodeClassAttribute->classAttribute->type->serializedValue) {
                    if ($value === '') {
                        $value = $nodeClassAttribute->classAttribute->type->defaultValue;
                    } else {
                        $value = serialize($value);
                    }
                }

                $nodeTranslation->value = $value;
                $nodeTranslation->save();
            }
        }

        if ($isIframe) {
            return redirect('/admin/node/edit/'.$id.'/iframe');
        } else {
            return redirect('/admin/node/edit/'.$id);
        }
    }

    public function allowedChildClassesJson($id)
    {
        $node = Node::find($id);

        $response = array();

        $response['classes'] = $node->class->allowedChildClasses;

        return response()->json($response);
    }
}
