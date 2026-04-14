<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemTask;
use Marble\Admin\Models\User;
use Marble\Admin\Notifications\TaskAssignedNotification;

class ItemTaskController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Item $item)
    {
        $this->authorize('update', $item);

        $request->validate([
            'title'       => 'required|string|max:500',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        $task = ItemTask::create([
            'item_id'     => $item->id,
            'created_by'  => Auth::guard('marble')->id(),
            'assigned_to' => $request->input('assigned_to') ?: null,
            'title'       => $request->input('title'),
            'done'        => false,
            'due_date'    => $request->input('due_date') ?: null,
        ]);

        if ($task->assigned_to && $task->assigned_to !== Auth::guard('marble')->id()) {
            $assignee = User::find($task->assigned_to);
            $assignee?->notify(new TaskAssignedNotification($task->load('item', 'creator')));
        }

        return redirect(route('marble.item.edit', $item) . '#collaboration')
            ->with('success', trans('marble::admin.task_added'));
    }

    public function toggle(ItemTask $task)
    {
        $this->authorize('update', $task->item);

        $task->done = !$task->done;
        $task->save();

        return redirect(route('marble.item.edit', $task->item) . '#collaboration');
    }

    public function destroy(ItemTask $task)
    {
        $this->authorize('update', $task->item);

        $itemId = $task->item_id;
        $task->delete();

        return redirect(route('marble.item.edit', $itemId) . '#collaboration')
            ->with('success', trans('marble::admin.task_deleted'));
    }
}
