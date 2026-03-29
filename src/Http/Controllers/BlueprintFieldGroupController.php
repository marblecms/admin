<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\BlueprintFieldGroup;

class BlueprintFieldGroupController extends Controller
{
    public function add(Request $request, Blueprint $blueprint)
    {
        BlueprintFieldGroup::create([
            'name' => $request->input('name', 'New Group'),
            'blueprint_id' => $blueprint->id,
            'sort_order' => $blueprint->fieldGroups()->max('sort_order') + 1,
        ]);

        return redirect()->route('marble.blueprint.field.edit', $blueprint);
    }

    public function save(Request $request, Blueprint $blueprint)
    {
        $group = BlueprintFieldGroup::findOrFail($request->input('id'));
        $group->update($request->only('name'));

        return redirect()->route('marble.blueprint.field.edit', $blueprint);
    }

    public function sort(Request $request, Blueprint $blueprint)
    {
        $groups = $request->input('groups', []);

        foreach ($groups as $groupId => $sortOrder) {
            BlueprintFieldGroup::where('id', $groupId)->update(['sort_order' => $sortOrder]);
        }

        return response()->json(['success' => true]);
    }

    public function delete(Blueprint $blueprint, BlueprintFieldGroup $group)
    {
        // Unassign fields from this group
        $group->fields()->update(['blueprint_field_group_id' => null]);
        $group->delete();

        return redirect()->route('marble.blueprint.field.edit', $blueprint);
    }
}
