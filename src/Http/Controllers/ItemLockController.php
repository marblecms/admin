<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Models\Item;

class ItemLockController extends Controller
{
    public function acquire(Item $item)
    {
        $item->acquireLock(Auth::guard('marble')->id());
        return response()->json(['ok' => true]);
    }

    public function release(Item $item)
    {
        $item->releaseLock(Auth::guard('marble')->id());
        return response()->json(['ok' => true]);
    }
}
