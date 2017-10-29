<?php

namespace Marble\Admin\App\Http\Controllers\NodeClass;

use Marble\Admin\App\Models\NodeClass;
use Marble\Admin\App\Models\NodeClassGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupsController extends Controller
{

    public function edit($id)
    {
        $nodeClassGroup = NodeClassGroup::find($id);

        $data = array();
        $data['nodeClassGroup'] = $nodeClassGroup;

        return view('admin::nodeclass.editgroup', $data);
    }

    public function save(Request $request, $id)
    {
        $nodeClassGroup = NodeClassGroup::find($id);
        $nodeClassGroup->name = $request->input('name');
        $nodeClassGroup->save();

        return redirect('/admin/nodeclass/all');
    }

    public function delete($id)
    {
        NodeClassGroup::destroy($id);

        return redirect('/admin/nodeclass/all');
    }

    public function add()
    {
        $nodeClassGroup = new NodeClassGroup();
        $nodeClassGroup->name = 'Neue Gruppe';
        $nodeClassGroup->save();

        return redirect('/admin/nodeclass/groups/edit/'.$nodeClassGroup->id);
    }
}
