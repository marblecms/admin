<?php

namespace Marble\Admin\App\Http\Controllers;

use Marble\Admin\App\Models\UserGroup;
use Marble\Admin\App\Helpers\NodeHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserGroupController extends Controller
{

    public function all()
    {
        $data = array();

        $data['groups'] = UserGroup::all();

        return view('admin::usergroup.all', $data);
    }

    public function add()
    {
        $userGroup = new UserGroup();
        $userGroup->name = 'Neue Gruppe';
        $userGroup->allowedClasses = array('all');
        $userGroup->entryNodeId = -1;
        $userGroup->save();

        return redirect('/admin/usergroup/edit/'.$userGroup->id);
    }

    public function edit($id)
    {
        $data = array();
        $data['group'] = UserGroup::find($id);

        $data['groupedNodeClasses'] = NodeHelper::getGroupedNodeClasses();

        return view('admin::usergroup.edit', $data);
    }

    public function save(Request $request, $id)
    {
        $group = UserGroup::find($id);
        $group->name = $request->input('name');
        $group->entryNodeId = $request->input('entryNodeId');

        if (!is_numeric($group->entryNodeId)) {
            $group->entryNodeId = -1;
        }

        $group->allowedClasses = $request->input('allowed_classes');

        $group->createUser = $request->input('createUser');
        $group->createGroup = $request->input('createGroup');
        $group->createClass = $request->input('createClass');

        $group->deleteUser = $request->input('deleteUser');
        $group->deleteGroup = $request->input('deleteGroup');
        $group->deleteClass = $request->input('deleteClass');

        $group->editUser = $request->input('editUser');
        $group->editGroup = $request->input('editGroup');
        $group->editClass = $request->input('editClass');

        $group->listUser = $request->input('listUser');
        $group->listGroup = $request->input('listGroup');
        $group->listClass = $request->input('listClass');
        $group->save();

        return redirect('/admin/usergroup/edit/'.$id);
    }

    public function delete($id)
    {
        UserGroup::destroy($id);

        return redirect('/admin/usergroup/all');
    }
}
