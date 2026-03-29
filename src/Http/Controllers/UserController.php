<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Marble\Admin\Http\Requests\UserRequest;
use Marble\Admin\Models\User;
use Marble\Admin\Models\UserGroup;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', User::class);
        return view('marble::user.index', [
            'groups' => UserGroup::with('users')->orderBy('name')->get(),
        ]);
    }

    public function add()
    {
        $this->authorize('create', User::class);
        return view('marble::user.edit', [
            'user'       => null,
            'userGroups' => UserGroup::all(),
        ]);
    }

    public function create(UserRequest $request)
    {
        $this->authorize('create', User::class);
        $user = User::create([
            'name'          => $request->input('name'),
            'email'         => $request->input('email'),
            'password'      => Hash::make($request->input('password')),
            'user_group_id' => $request->input('user_group_id'),
            'root_item_id'  => $request->input('root_item_id') ?: null,
        ]);

        return redirect()->route('marble.user.edit', $user);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('marble::user.edit', [
            'user' => $user,
            'userGroups' => UserGroup::all(),
        ]);
    }

    public function save(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->user_group_id = $request->input('user_group_id');
        $user->root_item_id = $request->input('root_item_id') ?: null;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        return redirect()->route('marble.user.edit', $user);
    }

    public function delete(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();

        return redirect()->route('marble.user.index');
    }

    public function setLanguage(Request $request)
    {
        $language = $request->input('language', 'en');
        $user = Auth::guard('marble')->user();
        $user->language = $language;
        $user->save();
        // Set app locale for this request
        app()->setLocale($language);
        return back();
    }
}
