<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Models\Notification;
use Marble\Admin\Services\NotificationService;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function count()
    {
        $user = Auth::guard('marble')->user();

        return response()->json([
            'count' => $this->notifications->unreadCountFor($user),
        ]);
    }

    public function recent()
    {
        $user = Auth::guard('marble')->user();
        $items = $this->notifications->recentFor($user);

        return response()->json(
            $items->map(fn ($n) => [
                'id'         => $n->id,
                'title'      => $n->title,
                'body'       => $n->body,
                'url'        => $n->url,
                'read'       => $n->read_at !== null,
                'created_at' => $n->created_at->diffForHumans(),
            ])
        );
    }

    public function markAllRead()
    {
        $this->notifications->markAllRead(Auth::guard('marble')->user());

        return response()->json(['ok' => true]);
    }

    public function markRead(Notification $notification)
    {
        $this->notifications->markRead($notification);

        return response()->json(['ok' => true]);
    }
}
