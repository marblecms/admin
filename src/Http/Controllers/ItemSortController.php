<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Models\Item;

class ItemSortController extends Controller
{
    public function sort(Request $request)
    {
        $user  = Auth::guard('marble')->user();
        $input = $request->input('items', []);
        $items = Item::whereIn('id', array_keys($input))->get()->keyBy('id');

        foreach ($input as $itemId => $sortOrder) {
            $item = $items->get((int) $itemId);
            if ($item && $user->canDoWithBlueprint($item->blueprint_id, 'update')) {
                $item->update(['sort_order' => (int) $sortOrder]);
            }
        }

        return response()->json(['success' => true]);
    }
}
