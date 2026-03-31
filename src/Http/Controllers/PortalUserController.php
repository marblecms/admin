<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\PortalUser;

class PortalUserController extends Controller
{
    public function index()
    {
        return view('marble::portal-user.index', [
            'portalUsers' => PortalUser::with('item')->orderBy('email')->paginate(50),
        ]);
    }

    public function create()
    {
        return view('marble::portal-user.edit', [
            'portalUser' => new PortalUser(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|unique:portal_users,email',
            'password' => 'required|min:8',
        ]);

        $portalUser = PortalUser::create([
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'item_id'  => $request->input('item_id') ?: null,
            'enabled'  => $request->boolean('enabled', true),
        ]);

        return redirect()->route('marble.portal-user.edit', $portalUser)
            ->with('success', trans('marble::admin.portal_user_created'));
    }

    public function edit(PortalUser $portalUser)
    {
        return view('marble::portal-user.edit', compact('portalUser'));
    }

    public function update(Request $request, PortalUser $portalUser)
    {
        $request->validate([
            'email'    => 'required|email|unique:portal_users,email,' . $portalUser->id,
            'password' => 'nullable|min:8',
        ]);

        $data = [
            'email'   => $request->input('email'),
            'item_id' => $request->input('item_id') ?: null,
            'enabled' => $request->boolean('enabled'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $portalUser->update($data);

        return redirect()->route('marble.portal-user.edit', $portalUser)
            ->with('success', trans('marble::admin.saved'));
    }

    public function delete(PortalUser $portalUser)
    {
        $portalUser->delete();

        return redirect()->route('marble.portal-user.index')
            ->with('success', trans('marble::admin.deleted'));
    }

    public function toggle(PortalUser $portalUser)
    {
        $portalUser->update(['enabled' => !$portalUser->enabled]);

        return back();
    }
}
