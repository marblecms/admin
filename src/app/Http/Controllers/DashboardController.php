<?php

namespace Marble\Admin\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Marble\Admin\App\Models\User;
use Marble\Admin\App\Models\NodeClass;

class DashboardController extends Controller
{
    public function view()
    {
        return view('admin::dashboard.view', [
            "nodeClasses" => NodeClass::all(),
            "users" => User::all()
        ]);
    }
}
