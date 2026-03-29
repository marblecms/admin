<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Marble\Admin\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user')
            ->orderByDesc('id')
            ->paginate(50);

        return view('marble::activity-log.index', compact('logs'));
    }
}
