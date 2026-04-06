<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Marble\Admin\Models\Item;

class ItemMoveController extends Controller
{
    use AuthorizesRequests;

    public function form(Item $item)
    {
        $this->authorize('update', $item);

        // Find blueprint IDs that explicitly allow the item's blueprint as a child,
        // plus any that allow all children.
        $allowingBlueprintIds = DB::table('blueprint_allowed_children')
            ->where(function ($q) use ($item) {
                $q->where('child_blueprint_id', $item->blueprint_id)
                  ->orWhere('allow_all', true);
            })
            ->pluck('blueprint_id');

        $potentialParents = Item::with('blueprint')
            ->where('id', '!=', $item->id)
            ->whereIn('blueprint_id', $allowingBlueprintIds)
            ->whereNotLike('path', $item->path . '/%')
            ->get()
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
