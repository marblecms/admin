<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Marble\Admin\Http\Requests\UserGroupRequest;
use Marble\Admin\Models\UserGroup;

class UserGroupController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', UserGroup::class);
        return view('marble::usergroup.index', [
            'groups' => UserGroup::all(),
        ]);
    }

    public function add()
    {
        $this->authorize('create', UserGroup::class);
        $group = UserGroup::create(['name' => 'New Group']);

        return redirect()->route('marble.user-group.edit', $group);
    }

    public function edit(UserGroup $group)
    {
        $this->authorize('update', $group);
        return view('marble::usergroup.edit', [
            'group' => $group,
        ]);
    }

    public function save(UserGroupRequest $request, UserGroup $group)
    {
        $this->authorize('update', $group);

        $booleanFields = [
            'can_create_users', 'can_edit_users', 'can_delete_users', 'can_list_users',
            'can_create_blueprints', 'can_edit_blueprints', 'can_delete_blueprints', 'can_list_blueprints',
            'can_create_groups', 'can_edit_groups', 'can_delete_groups', 'can_list_groups',
        ];

        $data = ['name' => $request->input('name')];
        foreach ($booleanFields as $field) {
            $data[$field] = $request->boolean($field);
        }

        $group->update($data);

        // Sync allowed blueprints
        $allowed = $request->input('allowed_blueprints', []);

        // Remove all existing entries (including the allow_all sentinel row)
        DB::table('user_group_allowed_blueprints')
            ->where('user_group_id', $group->id)
            ->delete();

        if (in_array('all', $allowed)) {
            DB::table('user_group_allowed_blueprints')->insert([
                'user_group_id' => $group->id,
                'blueprint_id'  => null,
                'allow_all'     => true,
            ]);
        } else {
            $ids = array_filter($allowed, fn($id) => is_numeric($id));
            $group->allowedBlueprints()->sync($ids);
        }

        return redirect()->route('marble.user-group.edit', $group);
    }

    public function delete(UserGroup $group)
    {
        $this->authorize('delete', $group);
        $group->delete();

        return redirect()->route('marble.user-group.index');
    }
}
