<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Facades\Marble;
use Marble\Admin\Models\ActivityLog;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\FormSubmission;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemSubscription;
use Marble\Admin\Models\ItemTask;
use Marble\Admin\Models\Media;
use Marble\Admin\Models\Site;
use Marble\Admin\Models\User;

class DashboardController extends Controller
{
    public function view()
    {
        $user = Auth::guard('marble')->user();

        $recentActivity = ActivityLog::with('user', 'item.blueprint')
            ->orderByDesc('created_at')
            ->paginate(10);

        $trashCount = Item::onlyTrashed()->count();

        $stats = [
            'items_total'        => Item::count(),
            'items_published'    => Item::where('status', 'published')->count(),
            'items_draft'        => Item::where('status', 'draft')->count(),
            'media_count'        => Media::count(),
            'users_count'        => User::count(),
            'trash_count'        => $trashCount,
            'unread_submissions' => FormSubmission::where('read', false)->count(),
        ];

        $upcomingItems = Item::where(function ($q) {
                $q->whereNotNull('published_at')->where('published_at', '>', now())
                  ->orWhereNotNull('expires_at')->where('expires_at', '>', now());
            })
            ->with('blueprint')
            ->orderBy('published_at')
            ->limit(5)
            ->get();

        $unreadSubmissions = FormSubmission::where('read', false)
            ->with('item.blueprint')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $deadlineItems = Item::where('status', 'draft')
            ->whereNotNull('current_workflow_step_id')
            ->whereNotNull('workflow_step_entered_at')
            ->whereHas('workflowStep', fn ($q) => $q->whereNotNull('deadline_days'))
            ->with(['workflowStep', 'blueprint'])
            ->get()
            ->map(fn ($item) => [
                'item'      => $item,
                'days_left' => (int) now()->diffInDays(
                    $item->workflow_step_entered_at->addDays($item->workflowStep->deadline_days),
                    false
                ),
            ])
            ->sortBy('days_left')
            ->values()
            ->take(15);

        $watchedItems = $user
            ? ItemSubscription::where('user_id', $user->id)
                ->with(['item.blueprint'])
                ->orderByDesc('created_at')
                ->get()
                ->pluck('item')
                ->filter()
            : collect();

        $myDraftIds = $user
            ? ActivityLog::where('user_id', $user->id)
                ->whereNotNull('item_id')
                ->orderByDesc('id')
                ->pluck('item_id')
                ->unique()
                ->take(50)
            : collect();

        $myDrafts = $myDraftIds->isNotEmpty()
            ? Item::where('status', 'draft')
                ->whereIn('id', $myDraftIds)
                ->with('blueprint')
                ->orderByDesc('updated_at')
                ->limit(10)
                ->get()
            : collect();

        $myPendingItems = $user
            ? Item::where('status', 'draft')
                ->whereNotNull('current_workflow_step_id')
                ->whereHas('workflowStep', function ($q) use ($user) {
                    $q->where(function ($q2) use ($user) {
                        // Step has no group restriction (anyone can act)
                        $q2->whereDoesntHave('allowedGroups');
                        // OR the user's group is explicitly allowed
                        if ($user->user_group_id) {
                            $q2->orWhereHas('allowedGroups', fn ($q3) => $q3->where('user_groups.id', $user->user_group_id));
                        }
                    });
                })
                ->with(['workflowStep', 'blueprint'])
                ->orderByDesc('updated_at')
                ->limit(10)
                ->get()
            : collect();

        $myAssignedTasks = $user
            ? ItemTask::where('assigned_to', $user->id)
                ->where('done', false)
                ->with(['item.blueprint', 'creator'])
                ->orderByRaw('due_date IS NULL, due_date ASC')
                ->limit(20)
                ->get()
            : collect();

        // Quick-create shortcuts: blueprints that can have children (creatable content types)
        // excluding system/folder types and forms
        $quickCreateBlueprints = Blueprint::where('allow_children', false)
            ->where('is_form', false)
            ->where('show_in_tree', true)
            ->orderBy('name')
            ->get();

        // Default parent for quick-create: site root item, or config fallback
        $site = Site::where('is_default', true)->first() ?? Site::first();
        $quickCreateParentId = $site?->root_item_id ?? config('marble.entry_item_id', 1);

        return view('marble::dashboard.view', [
            'blueprints'             => Blueprint::all(),
            'users'                  => User::all(),
            'currentUser'            => $user,
            'recentActivity'         => $recentActivity,
            'trashCount'             => $trashCount,
            'stats'                  => $stats,
            'upcomingItems'          => $upcomingItems,
            'unreadSubmissions'      => $unreadSubmissions,
            'deadlineItems'          => $deadlineItems,
            'watchedItems'           => $watchedItems,
            'myDrafts'               => $myDrafts,
            'myPendingItems'         => $myPendingItems,
            'quickCreateBlueprints'  => $quickCreateBlueprints,
            'quickCreateParentId'    => $quickCreateParentId,
            'myAssignedTasks'        => $myAssignedTasks,
        ]);
    }
}
