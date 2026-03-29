<?php

namespace Marble\Admin\Services;

use Illuminate\Support\Facades\Auth;
use Marble\Admin\Models\ActivityLog;
use Marble\Admin\Models\Item;

class ActivityLogService
{
    public function log(string $action, ?Item $item = null, array $context = []): void
    {
        ActivityLog::create([
            'user_id'   => Auth::guard('marble')->id(),
            'action'    => $action,
            'item_id'   => $item?->id,
            'item_name' => $item?->name(),
            'context'   => empty($context) ? null : $context,
        ]);
    }
}
