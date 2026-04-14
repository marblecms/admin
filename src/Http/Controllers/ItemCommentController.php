<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemComment;

class ItemCommentController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Item $item)
    {
        $this->authorize('update', $item);

        $request->validate(['body' => 'required|string|max:5000']);

        ItemComment::create([
            'item_id' => $item->id,
            'user_id' => Auth::guard('marble')->id(),
            'body'    => $request->input('body'),
        ]);

        return redirect()->route('marble.item.edit', $item)
            ->withFragment('collaboration')
            ->with('success', trans('marble::admin.comment_added'));
    }

    public function destroy(ItemComment $comment)
    {
        $this->authorize('update', $comment->item);

        $comment->delete();

        return redirect()->route('marble.item.edit', $comment->item)
            ->withFragment('collaboration')
            ->with('success', trans('marble::admin.comment_deleted'));
    }
}
