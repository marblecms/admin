<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Models\Item;
use Marble\Admin\Services\WorkflowService;

class ItemWorkflowController extends Controller
{
    public function __construct(private WorkflowService $workflow) {}

    public function advance(Item $item)
    {
        $actor = Auth::guard('marble')->user();

        if (!$this->workflow->canAdvance($item, $actor)) {
            return redirect()->route('marble.item.edit', $item)
                ->with('error', trans('marble::admin.workflow_permission_denied'));
        }

        $this->workflow->advance($item, $actor);

        return redirect()->route('marble.item.edit', $item);
    }

    public function retreat(Item $item)
    {
        $actor = Auth::guard('marble')->user();

        if (!$this->workflow->canRetreat($item, $actor)) {
            return redirect()->route('marble.item.edit', $item)
                ->with('error', trans('marble::admin.workflow_permission_denied'));
        }

        $this->workflow->retreat($item, $actor);

        return redirect()->route('marble.item.edit', $item);
    }

    public function reject(Request $request, Item $item)
    {
        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        $this->workflow->reject($item, Auth::guard('marble')->user(), $request->input('comment', ''));

        return redirect()->route('marble.item.edit', $item);
    }
}
