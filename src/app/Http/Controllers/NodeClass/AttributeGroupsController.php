<?php

namespace Marble\Admin\App\Http\Controllers\NodeClass;

use Marble\Admin\App\Models\NodeClass;
use Marble\Admin\App\Models\ClassAttribute;
use Marble\Admin\App\Models\ClassAttributeGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AttributeGroupsController extends Controller
{

    public function add(Request $request, $id)
    {
        $groups = ClassAttributeGroup::where(array('classId' => $id))->get();

        $group = new ClassAttributeGroup();
        $group->classId = $id;
        $group->name = $request->input('name');
        $group->sortOrder = count($groups);
        $group->save();

        return redirect('/admin/nodeclass/attributes/edit/'.$id);
    }

    public function save(Request $request, $id)
    {
        $group = ClassAttributeGroup::find($request->input('id'));
        $group->name = $request->input('name');
        $group->template = $request->input('template');
        $group->save();

        return redirect('/admin/nodeclass/attributes/edit/'.$id);
    }

    public function delete($id, $groupId)
    {
        $classAttributes = ClassAttribute::where(array('groupId' => $groupId))->get();
        foreach ($classAttributes as $classAttribute) {
            $classAttribute->groupId = 0;
            $classAttribute->save();
        }
        ClassAttributeGroup::destroy($groupId);

        return redirect('/admin/nodeclass/attributes/edit/'.$id);
    }

    public function sort(Request $request, $id)
    {
        $groups = $request->input('groups');

        foreach ($groups as $groupId => $sortOrder) {
            $classAttributeGroup = ClassAttributeGroup::find($groupId);
            $classAttributeGroup->sortOrder = $sortOrder;
            $classAttributeGroup->save();
        }
        die;
    }
}
