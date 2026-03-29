<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\Redirect;

class RedirectController extends Controller
{
    public function index()
    {
        $redirects = Redirect::with('targetItem')->orderByDesc('hits')->orderByDesc('id')->paginate(50);

        return view('marble::redirect.index', compact('redirects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'source_path'  => 'required|string|max:500',
            'target_path'  => 'nullable|string|max:500',
            'target_item_id' => 'nullable|exists:items,id',
            'status_code'  => 'required|in:301,302',
        ]);

        $source = '/' . ltrim($request->input('source_path'), '/');

        Redirect::updateOrCreate(
            ['source_path' => $source],
            [
                'target_path'    => $request->input('target_item_id') ? null : ('/' . ltrim($request->input('target_path', ''), '/')),
                'target_item_id' => $request->input('target_item_id') ?: null,
                'status_code'    => $request->input('status_code', 301),
                'active'         => true,
            ]
        );

        return redirect()->route('marble.redirect.index')->with('success', trans('marble::admin.redirect_saved'));
    }

    public function toggle(Redirect $redirect)
    {
        $redirect->update(['active' => !$redirect->active]);

        return redirect()->route('marble.redirect.index');
    }

    public function destroy(Redirect $redirect)
    {
        $redirect->delete();

        return redirect()->route('marble.redirect.index');
    }
}
