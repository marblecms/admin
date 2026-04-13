<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemSubscription;

class ItemSubscriptionController extends Controller
{
    public function toggle(Item $item)
    {
        $userId = Auth::guard('marble')->id();

        $existing = ItemSubscription::where('user_id', $userId)
            ->where('item_id', $item->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $watching = false;
        } else {
            ItemSubscription::create(['user_id' => $userId, 'item_id' => $item->id]);
            $watching = true;
        }

        return redirect()->route('marble.item.edit', $item)
            ->with('success', $watching
                ? trans('marble::admin.subscription_watching')
                : trans('marble::admin.subscription_unwatched')
            );
    }
}
