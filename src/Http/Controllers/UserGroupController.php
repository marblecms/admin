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

        // Build a keyed map of blueprintId => pivot row for the view
        $blueprintPerms = DB::table('user_group_allowed_blueprints')
            ->where('user_group_id', $group->id)
            ->whereNotNull('blueprint_id')
            ->get()
            ->keyBy('blueprint_id');

        return view('marble::usergroup.edit', [
            'group'          => $group,
            'blueprintPerms' => $blueprintPerms,
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

        $data = [
            'name'          => $request->input('name'),
            'entry_item_id' => $request->input('entry_item_id') ?: null,
        ];
        foreach ($booleanFields as $field) {
            $data[$field] = $request->boolean($field);
        }

        $group->update($data);

        // Sync allowed blueprints with granular CRUD permissions
        DB::table('user_group_allowed_blueprints')
            ->where('user_group_id', $group->id)
            ->delete();

        $allowAll = $request->boolean('allow_all_blueprints');

        if ($allowAll) {
            DB::table('user_group_allowed_blueprints')->insert([
                'user_group_id' => $group->id,
                'blueprint_id'  => null,
                'allow_all'     => true,
                'can_create'    => true,
                'can_read'      => true,
                'can_update'    => true,
                'can_delete'    => true,
            ]);
        } else {
            $blueprintPerms = $request->input('blueprint_perms', []);
            foreach ($blueprintPerms as $blueprintId => $perms) {
                if (!is_numeric($blueprintId)) continue;
                DB::table('user_group_allowed_blueprints')->insert([
                    'user_group_id' => $group->id,
                    'blueprint_id'  => (int) $blueprintId,
                    'allow_all'     => false,
                    'can_create'    => !empty($perms['can_create']),
                    'can_read'      => !empty($perms['can_read']),
                    'can_update'    => !empty($perms['can_update']),
                    'can_delete'    => !empty($perms['can_delete']),
                ]);
            }
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
