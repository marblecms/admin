<?php

namespace Marble\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetMarbleGuard
{
    public function handle(Request $request, Closure $next)
    {
        Auth::shouldUse('marble');

        $user = Auth::guard('marble')->user();
        if ($user && !empty($user->language)) {
            app()->setLocale($user->language);
        }

        return $next($request);
    }
}
