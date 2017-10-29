<?php

namespace Marble\Admin\App\Http\Controllers;

use Marble\Admin\App\Helpers\NodeHelper;
use Marble\Admin\App\Models\Node;
use Marble\Admin\App\Models\NodeClass;
use Marble\Admin\App\Models\ClassAttribute;
use Marble\Admin\App\Models\ClassAttributeGroup;
use Marble\Admin\App\Models\NodeClassGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NodeClassController extends Controller
{

    public function all(Request $request)
    {
        $groups = NodeClassGroup::all();
        $groupedNodeClasses = array();

        foreach ($groups as $group) {
            $groupedNodeClasses[$group->id] = (object) array(
                'group' => $group,
                'items' => NodeClass::where(array('groupId' => $group->id))->get(),
            );
        }

        $data = array();
        $data['groupedNodeClasses'] = $groupedNodeClasses;
        $data['error'] = false;

        if ($request->session()->has('import_error')) {
            $data['error'] = $request->session()->get('import_error');
        }

        return view('admin::nodeclass.all', $data);
    }

    public function add()
    {
        $nodeClass = new NodeClass();
        $nodeClass->name = 'Neue Klasse';
        $nodeClass->allowedChildClasses = array('all');
        $nodeClass->save();

        $nameClassAttribute = new ClassAttribute();
        $nameClassAttribute->classId = $nodeClass->id;
        $nameClassAttribute->attributeId = 1;
        $nameClassAttribute->name = 'Name';
        $nameClassAttribute->namedIdentifier = 'name';
        $nameClassAttribute->translate = 1;
        $nameClassAttribute->locked = 0;
        $nameClassAttribute->sortOrder = -1;
        $nameClassAttribute->save();

        $slugClassAttribute = new ClassAttribute();
        $slugClassAttribute->classId = $nodeClass->id;
        $slugClassAttribute->attributeId = 1;
        $slugClassAttribute->name = 'Slug';
        $slugClassAttribute->namedIdentifier = 'slug';
        $slugClassAttribute->translate = 1;
        $slugClassAttribute->locked = 0;
        $slugClassAttribute->save();

        return redirect('/admin/nodeclass/edit/'.$nodeClass->id);
    }

    public function edit($id)
    {
        $nodeClass = NodeClass::find($id);
        $nodeClassGroups = NodeClassGroup::all();

        $data = array();

        $data['nodeClass'] = $nodeClass;
        $data['nodeClassGroups'] = $nodeClassGroups;

        $data['groupedNodeClasses'] = NodeHelper::getGroupedNodeClasses();

        return view('admin::nodeclass.edit', $data);
    }

    public function save(Request $request, $id)
    {
        $nodeClass = NodeClass::find($id);
        $nodeClass->name = $request->input('name');
        $nodeClass->namedIdentifier = $request->input('namedIdentifier');
        $nodeClass->icon = $request->input('icon');
        $nodeClass->allowChildren = $request->input('allowChildren');
        $nodeClass->listChildren = $request->input('listChildren');
        $nodeClass->groupId = $request->input('groupId');
        $nodeClass->locked = $request->input('locked');
        $nodeClass->allowedChildClasses = $request->input('allowedChildClasses');
        $nodeClass->showInTree = $request->input('showInTree');
        $nodeClass->tabs = $request->input('tabs');
        $nodeClass->save();

        return redirect('/admin/nodeclass/edit/'.$id);
    }

    public function delete($id)
    {
        NodeClass::destroy($id);

        $classAttributes = ClassAttribute::where(array('classId' => $id))->get();

        foreach ($classAttributes as $classAttribute) {
            ClassAttribute::destroy($classAttribute->id);
        }

        $classAttributeGroups = ClassAttributeGroup::where(array('classId' => $id))->get();

        foreach ($classAttributeGroups as $classAttribute) {
            ClassAttributeGroup::destroy($classAttribute->id);
        }

        $nodes = Node::where(array('classId' => $id))->get();

        foreach ($nodes as $node) {
            Node::destroy($node->id);
        }

        return redirect('/admin/nodeclass/all');
    }
}
