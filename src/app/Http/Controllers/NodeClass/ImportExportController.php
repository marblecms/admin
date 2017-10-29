<?php

namespace Marble\Admin\App\Http\Controllers\NodeClass;

use DB;
use File;
use Marble\Admin\App\Models\ClassAttributeGroup;
use Marble\Admin\App\Models\Attribute;
use Marble\Admin\App\Models\NodeClass;
use Marble\Admin\App\Models\ClassAttribute;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class ImportExportController extends Controller
{

    public function export($id)
    {
        $response = array();

        $nodeClass = NodeClass::find($id);

        $json['node_class'] = array(
            'name' => $nodeClass->name,
            'allowChildren' => $nodeClass->allowChildren,
            'namedIdentifier' => $nodeClass->namedIdentifier,
            'icon' => $nodeClass->icon,
            'listChildren' => $nodeClass->listChildren,
            'locked' => $nodeClass->locked,
            'showInTree' => $nodeClass->showInTree,
            'tabs' => $nodeClass->tabs,
            'allowedChildClasses' => array(),
        );

        foreach ($nodeClass->allowedChildClasses as $classId) {
            if ($classId === 'all') {
                $json['nodeClass']['allowedChildClasses'][] = 'all';
            } else {
                $json['nodeClass']['allowedChildClasses'][] = NodeClass::find($classId)->namedIdentifier;
            }
        }

        $json['class_attributes'] = array();

        foreach ($nodeClass->attributes as $classAttribute) {
            $json['classAttributes'][] = array(
                'name' => $classAttribute->name,
                'attributeNamedIdentifier' => $classAttribute->type->namedIdentifier,
                'sortOrder' => $classAttribute->sortOrder,
                'configuration' => $classAttribute->configuration,
                'namedIdentifier' => $classAttribute->namedIdentifier,
                'translate' => $classAttribute->translate,
                'locked' => $classAttribute->locked,
                'showName' => $classAttribute->showName,
                'groupId' => $classAttribute->groupId,
            );
        }

        $json['classAttributeGroups'] = array();

        foreach (ClassAttributeGroup::where(array('classId' => $id))->get() as $classAttributeGroup) {
            $json['classAttributeGroups'][$classAttributeGroup->id] = array(
                'name' => $classAttributeGroup->name,
                'sortOrder' => $classAttributeGroup->sortOrder,
                'template' => $classAttributeGroup->template,
            );
        }

        $json = json_encode($json);

        $response = new Response();
        $response->setContent($json);
        $response->header('Content-type', 'application/json');
        $response->header('Content-disposition', 'attachment; filename="nodeclass-'.$nodeClass->namedIdentifier.'.json"');
        $response->header('Content-Length', sizeof($json));

        return $response;
    }

    public function import(Request $request)
    {
        DB::beginTransaction();

        $data = json_decode(File::get($request->file('file')));

        $nodeClass = new NodeClass();
        $nodeClass->name = $data->nodeClass->name;
        $nodeClass->allowChildren = $data->nodeClass->allowChildren;
        $nodeClass->namedIdentifier = $data->nodeClass->namedIdentifier;
        $nodeClass->icon = $data->nodeClass->icon;
        $nodeClass->listChildren = $data->nodeClass->listChildren;
        $nodeClass->locked = $data->nodeClass->locked;
        $nodeClass->showInTree = $data->nodeClass->showInTree;
        $nodeClass->tabs = $data->nodeClass->tabs;

        $allowedChildClasses = array();

        foreach ($data->node_class->allowedChildClasses as $class) {
            if ($class === 'all') {
                $allowedChildClasses[] = 'all';
                break;
            }

            $allowedNodeClass = NodeClass::where('namedIdentifier', $class)->get()->first();

            if ($allowedNodeClass) {
                $allowedChildClasses[] = ''.$allowedNodeClass->id;
            }
        }

        $nodeClass->allowedChildClasses = $allowedChildClasses;
        $nodeClass->save();

        $groupIdMapping = array(
            0 => 0,
        );

        foreach ($data->classAttributeGroups as $oldId => $groupData) {
            $group = new ClassAttributeGroup();
            $group->name = $groupData->name;
            $group->sortOrder = $groupData->sortOrder;
            $group->template = $groupData->template;
            $group->classId = $nodeClass->id;
            $group->save();

            $groupIdMapping[$oldId] = $group->id;
        }

        $missingAttributes = array();

        foreach ($data->class_attributes as $classAttributeData) {
            $classAttribute = new ClassAttribute();
            $classAttribute->classId = $nodeClass->id;
            $classAttribute->name = $classAttributeData->name;
            $classAttribute->sortOrder = $classAttributeData->sortOrder;
            $classAttribute->configuration = $classAttributeData->configuration;
            $classAttribute->namedIdentifier = $classAttributeData->namedIdentifier;
            $classAttribute->translate = $classAttributeData->translate;
            $classAttribute->locked = $classAttributeData->locked;
            $classAttribute->showName = $classAttributeData->showName;
            $classAttribute->groupId = $groupIdMapping[$classAttributeData->groupId];

            $attribute = Attribute::where('namedIdentifier', $classAttributeData->attributeNamedIdentifier)->get()->first();

            if (!$attribute) {
                $missingAttributes[] = $classAttributeData->attributeNamedIdentifier;
                continue;
            }

            $classAttribute->attributeId = $attribute->id;

            $classAttribute->save();
        }

        if (count($missingAttributes)) {
            DB::rollBack();
            $request->session()->flash('import_error', 'Import failed! The following attribute types are missing: '.implode($missingAttributes));

            return redirect('/admin/nodeclass/all');
        } else {
            DB::commit();

            return redirect('/admin/nodeclass/edit/'.$nodeClass->id);
        }
    }
}
