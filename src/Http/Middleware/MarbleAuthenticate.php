<?php

namespace Marble\Admin\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarbleAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('marble')->check()) {
            throw new AuthenticationException('Unauthenticated.', ['marble'], route('marble.login'));
        }

        return $next($request);
    }
}
