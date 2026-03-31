<?php

namespace Marble\Admin\Services;

use Illuminate\Support\Collection;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\Notification;
use Marble\Admin\Models\User;

class NotificationService
{
    public function create(User $user, string $type, string $title, string $body = '', ?Item $item = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'url'     => $item ? route('marble.item.edit', $item) : null,
        ]);
    }

    public function unreadCountFor(User $user): int
    {
        return Notification::where('user_id', $user->id)->unread()->count();
    }

    public function markAllRead(User $user): void
    {
        Notification::where('user_id', $user->id)->unread()->update(['read_at' => now()]);
    }

    public function markRead(Notification $notification): void
    {
        $notification->update(['read_at' => now()]);
    }

    public function recentFor(User $user, int $limit = 20): Collection
    {
        return Notification::where('user_id', $user->id)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
