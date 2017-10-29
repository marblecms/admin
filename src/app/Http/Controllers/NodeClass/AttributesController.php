<?php

namespace Marble\Admin\App\Http\Controllers\NodeClass;

use Marble\Admin\App\Models\NodeClass;
use Marble\Admin\App\Models\Node;
use Marble\Admin\App\Models\Attribute;
use Marble\Admin\App\Models\ClassAttribute;
use Marble\Admin\App\Models\ClassAttributeGroup;
use Marble\Admin\App\Models\NodeClassAttribute;
use Marble\Admin\App\Models\NodeTranslation;
use Marble\Admin\App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AttributesController extends Controller
{

    public function edit($id)
    {
        $nodeClass = NodeClass::find($id);
        $attributes = Attribute::all();
        $classAttributeGroups = ClassAttributeGroup::where(array('classId' => $id))->get()->sortBy(function ($group) {
            return $group->sortOrder;
        });
        
        $attributeGroupTemplates = array();
        
        foreach( glob(resource_path().'/views/admin/attributegroups/*.php') as $filename ){
            $attributeGroupTemplates[] = str_replace(".blade.php", "", basename($filename));
        }
        
        $data = array();

        $data['nodeClass'] = $nodeClass;
        $data['attributes'] = $attributes;
        $data['classAttributeGroups'] = $classAttributeGroups;
        $data['attributeGroupTemplates'] = $attributeGroupTemplates;

        $data['groupedClassAttributes'] = array();

        foreach ($nodeClass->attributes as $attribute) {
            $classAttributeGroup = ClassAttributeGroup::find($attribute->groupId);
            $sortKey = $classAttributeGroup ? $classAttributeGroup->sortOrder : -1;

            if (!isset($data['groupedClassAttributes'][$sortKey])) {
                $data['groupedClassAttributes'][$sortKey] = (object) array(
                    'group' => $classAttributeGroup,
                    'items' => array(),
                );
            }

            $data['groupedClassAttributes'][$sortKey]->items[] = $attribute;
        }

        ksort($data['groupedClassAttributes']);

        return view('admin::nodeclass.attributes', $data);
    }

    public function add(Request $request, $id)
    {
        $classAttributes = ClassAttribute::where(array('classId' => $id))->get();

        $classAttribute = new ClassAttribute();
        $classAttribute->name = 'Neues Attribute';
        $classAttribute->classId = $id;
        $classAttribute->attributeId = $request->input('type');
        $classAttribute->namedIdentifier = 'new_attribute';
        $classAttribute->sortOrder = count($classAttributes);
        $classAttribute->save();

        $nodes = Node::where(array('classId' => $id))->get();
        $languages = Language::all();

        $attribute = Attribute::find($classAttribute->attributeId);

        foreach ($nodes as $node) {
            $nodeClassAttribute = new NodeClassAttribute();
            $nodeClassAttribute->nodeId = $node->id;
            $nodeClassAttribute->classAttributeId = $classAttribute->id;
            $nodeClassAttribute->save();

            foreach ($languages as $language) {
                $nodeTranslation = new NodeTranslation();
                $nodeTranslation->nodeId = $node->id;
                $nodeTranslation->languageId = $language->id;
                $nodeTranslation->value = $attribute->defaultValue;
                $nodeTranslation->nodeClassAttributeId = $nodeClassAttribute->id;
                $nodeTranslation->save();
            }
        }

        return redirect('/admin/nodeclass/attributes/edit/'.$id);
    }

    public function delete($id, $attributeId)
    {
        ClassAttribute::destroy($attributeId);
        $languages = Language::all();

        $nodeClassAttributes = NodeClassAttribute::where(array('classAttributeId' => $attributeId))->get();

        foreach ($nodeClassAttributes as $nodeClassAttribute) {
            $nodeTranslations = NodeTranslation::where(
                array(
                    'nodeClassAttributeId' => $nodeClassAttribute->id,
                ))->get();

            foreach ($nodeTranslations as $nodeTranslation) {
                $nodeTranslation->delete();
            }

            $nodeClassAttribute->delete();
        }

        return redirect('/admin/nodeclass/attributes/edit/'.$id);
    }

    public function save(Request $request, $id)
    {
        $attributes = $request->input('name');
        $namedIdentifier = $request->input('namedIdentifier');
        $translate = $request->input('translate');
        $locked = $request->input('locked');
        $sortOrder = $request->input('sortOrder');
        $configuration = $request->input('configuration');
        $groupId = $request->input('groupId');
        $showName = $request->input('showName');

        foreach ($attributes as $attributeId => $name) {
            $attribute = ClassAttribute::find($attributeId);
            $attribute->name = $name;
            $attribute->namedIdentifier = $namedIdentifier[$attributeId];
            $attribute->translate = isset($translate[$attributeId]) ? 1 : 0;
            $attribute->locked = isset($locked[$attributeId]) ? 1 : 0;
            $attribute->sortOrder = $sortOrder[$attributeId];
            $attribute->groupId = $groupId[$attributeId];
            $attribute->showName = isset($showName[$attributeId]) ? 1 : 0;

            if (isset($configuration[$attributeId])) {
                $attribute->configuration = $configuration[$attributeId];
            }else{
                $attribute->configuration = "";
            }

            $attribute->save();
        }

        return redirect('/admin/nodeclass/attributes/edit/'.$id);
    }
}
