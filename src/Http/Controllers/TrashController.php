<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Item;

class TrashController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $items = Item::onlyTrashed()
            ->with('blueprint')
            ->latest('deleted_at')
            ->paginate(40);

        return view('marble::item.trash', compact('items'));
    }

    public function restore(int $id)
    {
        $item = Item::onlyTrashed()->findOrFail($id);
        $this->authorize('update', $item);

        // Restore descendants first, then the item itself
        Item::onlyTrashed()
            ->where('path', 'like', $item->path . '/%')
            ->restore();

        $item->restore();

        return redirect()->route('marble.item.edit', $item->id);
    }

    public function forceDelete(int $id)
    {
        $item = Item::onlyTrashed()->findOrFail($id);
        $this->authorize('delete', $item);

        $descendants = Item::onlyTrashed()
            ->where('path', 'like', $item->path . '/%')
            ->get();

        foreach ($descendants as $d) {
            $d->itemValues()->delete();
            $d->forceDelete();
        }

        $item->itemValues()->delete();
        $item->forceDelete();

        return redirect()->route('marble.trash.index');
    }

    public function empty()
    {
        $items = Item::onlyTrashed()->get();

        foreach ($items as $item) {
            $item->itemValues()->delete();
            $item->forceDelete();
        }

        return redirect()->route('marble.trash.index');
    }
}
