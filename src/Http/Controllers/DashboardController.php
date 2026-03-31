<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Models\ActivityLog;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\FormSubmission;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\Media;
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

        return view('marble::dashboard.view', [
            'blueprints'        => Blueprint::all(),
            'users'             => User::all(),
            'currentUser'       => $user,
            'recentActivity'    => $recentActivity,
            'trashCount'        => $trashCount,
            'stats'             => $stats,
            'upcomingItems'     => $upcomingItems,
            'unreadSubmissions' => $unreadSubmissions,
        ]);
    }
}
