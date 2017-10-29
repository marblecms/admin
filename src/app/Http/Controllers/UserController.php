<?php

namespace Marble\Admin\App\Http\Controllers;

use Illuminate\Hashing\BcryptHasher;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Marble\Admin\App\Models\User;
use Marble\Admin\App\Models\UserGroup;

class UserController extends Controller
{

    public function all()
    {
        $data = array();

        $data['users'] = User::all();

        return view('admin::user.all', $data);
    }

    public function add()
    {
        $user = new User();
        $user->name = 'Neuer Benutzer';
        $user->save();

        return redirect('/admin/user/edit/'.$user->id);
    }

    public function edit($id)
    {
        $data = array();
        $data['user'] = User::find($id);
        $data['userGroups'] = UserGroup::all();

        return view('admin::user.edit', $data);
    }

    public function save(Request $request, $id)
    {
        $user = User::find($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->groupId = $request->input('groupId');

        if ($request->input('password')) {
            $hasher = new BcryptHasher();
            $user->password = $hasher->make($request->input('password'));
        }

        $user->save();

        return redirect('/admin/user/edit/'.$id);
    }

    public function delete($id)
    {
        User::destroy($id);

        return redirect('/admin/user/all');
    }
}
