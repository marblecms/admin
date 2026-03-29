<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\BlueprintGroup;

class BlueprintGroupController extends Controller
{
    public function add()
    {
        $group = BlueprintGroup::create(['name' => 'New Group']);

        return redirect()->route('marble.blueprint.group.edit', $group);
    }

    public function edit(BlueprintGroup $group)
    {
        return view('marble::blueprint.editgroup', ['group' => $group]);
    }

    public function save(Request $request, BlueprintGroup $group)
    {
        $group->update($request->only('name'));

        return redirect()->route('marble.blueprint.index');
    }

    public function delete(BlueprintGroup $group)
    {
        $group->delete();

        return redirect()->route('marble.blueprint.index');
    }
}
