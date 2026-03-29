<?php

namespace Marble\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Marble\Admin\Models\Redirect;
use Symfony\Component\HttpFoundation\Response;

class HandleMarbleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 404) {
            return $response;
        }

        $path = '/' . ltrim($request->path(), '/');

        $redirect = Redirect::where('source_path', $path)
            ->where('active', true)
            ->first();

        if (!$redirect) {
            return $response;
        }

        $redirect->increment('hits');

        return \Illuminate\Support\Facades\Redirect::to(
            $redirect->resolvedTarget(),
            $redirect->status_code
        );
    }
}
