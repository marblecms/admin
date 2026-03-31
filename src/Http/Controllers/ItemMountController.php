<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemMountPoint;

class ItemMountController extends Controller
{
    /**
     * Add a mount point: mount $item under $mountParent.
     */
    public function store(Request $request, Item $item)
    {
        $request->validate([
            'mount_parent_id' => 'required|integer|exists:items,id',
        ]);

        $mountParentId = (int) $request->input('mount_parent_id');

        // Prevent mounting an item under itself or under its own descendant (cycle)
        if ($mountParentId === $item->id) {
            return back()->withErrors(['mount' => trans('marble::admin.mount_cycle_error')]);
        }

        $mountParent = Item::findOrFail($mountParentId);
        if (str_starts_with($mountParent->path, $item->path)) {
            return back()->withErrors(['mount' => trans('marble::admin.mount_cycle_error')]);
        }

        $maxSort = ItemMountPoint::where('mount_parent_id', $mountParentId)->max('sort_order') ?? -1;

        ItemMountPoint::firstOrCreate(
            ['item_id' => $item->id, 'mount_parent_id' => $mountParentId],
            ['sort_order' => $maxSort + 1]
        );

        return redirect()->route('marble.item.edit', $item)
            ->with('success', trans('marble::admin.mount_added'));
    }

    /**
     * Remove a specific mount point.
     */
    public function destroy(Item $item, ItemMountPoint $mount)
    {
        abort_if($mount->item_id !== $item->id, 403);
        $mount->delete();

        return redirect()->route('marble.item.edit', $item)
            ->with('success', trans('marble::admin.mount_removed'));
    }
}
