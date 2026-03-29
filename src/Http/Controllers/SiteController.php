<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\Language;
use Marble\Admin\Models\Site;

class SiteController extends Controller
{
    public function index()
    {
        return view('marble::site.index', [
            'sites' => Site::with('rootItem', 'defaultLanguage')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('marble::site.edit', [
            'site'      => null,
            'items'     => $this->itemsForSelect(),
            'languages' => Language::all(),
        ]);
    }

    public function edit(Site $site)
    {
        return view('marble::site.edit', [
            'site'      => $site,
            'items'     => $this->itemsForSelect(),
            'languages' => Language::all(),
        ]);
    }

    private function itemsForSelect(): \Illuminate\Support\Collection
    {
        return Item::orderBy('path')->get()->map(function (Item $item) {
            $depth = substr_count(rtrim($item->path, '/'), '/');
            $item->_depth = $depth;
            return $item;
        });
    }

    public function save(Request $request, ?Site $site = null)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'domain'              => 'required|string|max:255',
            'root_item_id'        => 'nullable|exists:items,id',
            'default_language_id' => 'nullable|exists:languages,id',
            'active'              => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active');

        if ($site) {
            $site->update($data);
        } else {
            Site::create($data);
        }

        return redirect()->route('marble.site.index');
    }

    public function delete(Site $site)
    {
        $site->delete();
        return redirect()->route('marble.site.index');
    }
}
