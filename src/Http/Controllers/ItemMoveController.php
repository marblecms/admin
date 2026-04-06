<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Item;

class ItemMoveController extends Controller
{
    use AuthorizesRequests;

    public function form(Item $item)
    {
        $this->authorize('update', $item);

        $potentialParents = Item::with('blueprint.allowedChildBlueprints')
            ->where('id', '!=', $item->id)
            ->get()
            ->filter(function (Item $candidate) use ($item) {
                if (str_starts_with($candidate->path, $item->path . '/')) {
                    return false;
                }
                return $candidate->blueprint->allowsChild($item->blueprint);
            })
            ->values();

        return view('marble::item.move', compact('item', 'potentialParents'));
    }

    public function move(Request $request, Item $item)
    {
        $this->authorize('update', $item);

        $newParent = Item::findOrFail($request->input('parent_id'));

        if (!$newParent->blueprint->allowsChild($item->blueprint)) {
            return back()->withErrors(['parent_id' => 'Blueprint not allowed as child here.']);
        }

        if (str_starts_with($newParent->path, $item->path . '/')) {
            return back()->withErrors(['parent_id' => 'Cannot move item into its own subtree.']);
        }

        $item->parent_id = $newParent->id;
        $item->save();

        return redirect()->route('marble.item.edit', $item);
    }
}
