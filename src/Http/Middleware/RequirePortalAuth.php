<?php

namespace Marble\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequirePortalAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('portal')->check()) {
            return redirect()->route('marble.portal.login')
                ->with('intended', $request->fullUrl());
        }

        $user = Auth::guard('portal')->user();

        if (!$user->enabled) {
            Auth::guard('portal')->logout();
            return redirect()->route('marble.portal.login')
                ->withErrors(['email' => trans('marble::portal.account_disabled')]);
        }

        return $next($request);
    }
}
