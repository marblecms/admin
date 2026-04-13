<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\User;
use Marble\Admin\Services\NotificationService;
use Marble\Admin\Services\WorkflowService;

class ItemWorkflowController extends Controller
{
    public function __construct(
        private WorkflowService $workflow,
        private NotificationService $notifications,
    ) {}

    public function advance(Item $item)
    {
        $actor = Auth::guard('marble')->user();

        if (!$this->workflow->canAdvance($item, $actor)) {
            return redirect()->route('marble.item.edit', $item)
                ->with('error', trans('marble::admin.workflow_permission_denied'));
        }

        $this->workflow->advance($item, $actor);
        $this->notifySubscribers($item, 'workflow.advance', $actor->id);

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
        $this->notifySubscribers($item, 'workflow.retreat', $actor->id);

        return redirect()->route('marble.item.edit', $item);
    }

    public function reject(Request $request, Item $item)
    {
        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        $actor = Auth::guard('marble')->user();
        $this->workflow->reject($item, $actor, $request->input('comment', ''));
        $this->notifySubscribers($item, 'workflow.reject', $actor->id);

        return redirect()->route('marble.item.edit', $item);
    }

    private function notifySubscribers(Item $item, string $action, int $actorUserId): void
    {
        $subscribers = User::whereIn('id', $item->subscriberIds())
            ->where('id', '!=', $actorUserId)
            ->get();

        $title = match ($action) {
            'workflow.advance' => trans('marble::admin.subscription_notify_workflow_advance', ['name' => $item->name()]),
            'workflow.retreat' => trans('marble::admin.subscription_notify_workflow_retreat', ['name' => $item->name()]),
            'workflow.reject'  => trans('marble::admin.subscription_notify_workflow_reject',  ['name' => $item->name()]),
            default            => trans('marble::admin.subscription_notify_saved',             ['name' => $item->name()]),
        };

        foreach ($subscribers as $user) {
            $this->notifications->create($user, $action, $title, '', $item);
        }
    }
}
